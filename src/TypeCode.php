<?php


namespace Wqy\IdeHelper;


class TypeCode extends CodeBase implements ToCodeInterface
{
    public function toCode()
    {
        $type = $this->getRef();

        // php8 联合类型
        if ($type instanceof \ReflectionUnionType) {
            return $this->getMultiTypeString($type, '|');
        }
        if ($type instanceof \ReflectionIntersectionType) {
            return $this->getMultiTypeString($type, '&');
        }

        return $this->getNamedTypeString($type);
    }

    /**
     *
     * @param \ReflectionNamedType $type
     */
    private function getNamedTypeString($type)
    {
        $ts = '';

        $typeStr = $type instanceof \ReflectionNamedType ? $type->getName() : ((string)$type);
        // fix bug
        // in parallel extension, the return type name has a prefix \
        $typeStr = ltrim($typeStr, '\\');

        // php8 mixed不允许和？同用
        if ($type && $typeStr !== 'mixed' && $typeStr !== 'null' && $type->allowsNull()) {
            $ts .= '?';
        }
        if ($typeStr == 'static' || $typeStr == 'self') {
            // 这两个, 不特殊处理
        }
        else if ($type && ! $type->isBuiltin()) {
            $ts .= '\\';
        }

        $ts .= $typeStr . ' ';

        return $ts;
    }

    /**
     * @param \ReflectionUnionType|\ReflectionIntersectionType $type
     */
    private function getMultiTypeString($type, $glue)
    {
        return implode($glue, array_map(fn($one) => trim($this->getNamedTypeString($one)), $type->getTypes())) . ' ';
    }

    public function hasType($typeName)
    {
        $rtType = $this->getRef();
        if ($rtType instanceof \ReflectionNamedType) {
            return $typeName == $rtType->getName();
        }

        if ($rtType instanceof \ReflectionUnionType) {
            foreach ($rtType->getTypes() as $t /** @var \ReflectionNamedType $t */) {
                if ($t->getName() == $typeName) {
                    return true;
                }
            }
        }

        return false;
    }

}