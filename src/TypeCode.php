<?php


namespace Wqy\IdeHelper;


class TypeCode extends CodeBase implements ToCodeInterface
{
    public function toCode()
    {
        $type = $this->getRef();

        // php8 联合类型
        if ($type instanceof \ReflectionUnionType) {
            return $this->getUnionTypeString($type);
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
        if ($type && $typeStr !== 'mixed' && $type->allowsNull()) {
            $ts .= '?';
        }
        if ($type && ! $type->isBuiltin()) {
            $ts .= '\\';
        }

        $ts .= $typeStr . ' ';

        return $ts;
    }

    /**
     * @param \ReflectionUnionType $type
     */
    private function getUnionTypeString($type)
    {
        return implode('|', $type->getTypes()) . ' ';
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