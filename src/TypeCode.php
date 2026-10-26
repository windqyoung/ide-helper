<?php


namespace Wqy\IdeHelper;


/**
 * 把 ReflectionType 转成 php 代码中的类型字符串.
 *
 * 支持:
 * - 命名类型 (php 7.0)
 * - 可空类型 `?T` (php 7.1)
 * - 联合类型 `A|B` (php 8.0)
 * - 交叉类型 `A&B` (php 8.1)
 * - DNF 类型 `(A&B)|C` (php 8.2)
 */
class TypeCode extends CodeBase implements ToCodeInterface
{
    /**
     * 这几种类型前面不能加 `\`
     * @var string[]
     */
    private const SPECIAL_TYPE_NAMES = ['static', 'self', 'parent'];

    /**
     * 不能和 `?` 一起使用的类型
     * @var string[]
     */
    private const NON_NULLABLE_TYPE_NAMES = ['mixed', 'null', 'void', 'never'];

    /**
     * 是否强制输出可空类型
     * @var boolean
     */
    private $forceNullable = false;

    /**
     * php8.4 起, `function f(int $x = null)` 这种隐式可空写法被废弃,
     * 必须显式写成 `?int`;
     * 属性(含构造方法提升)同理.
     *
     * @param boolean $forceNullable
     * @return $this
     */
    public function setForceNullable($forceNullable = true)
    {
        $this->forceNullable = $forceNullable;
        return $this;
    }

    public function toCode()
    {
        $type = $this->getRef();

        if (is_null($type)) {
            return '';
        }

        $s = $this->toTypeString($type, false);

        if ($this->forceNullable && ! $type->allowsNull()) {
            $s = $this->toNullableString($type, $s);
        }

        // 末尾带一个空格, 方便拼接
        return $s . ' ';
    }

    /**
     * @param \ReflectionType $type
     * @param string $code
     * @return string
     */
    private function toNullableString($type, $code)
    {
        // 联合类型: A|B => A|B|null
        if ($type instanceof \ReflectionUnionType) {
            return $code . '|null';
        }

        // 交叉类型(DNF): A&B => (A&B)|null
        if ($type instanceof \ReflectionIntersectionType) {
            return '(' . $code . ')|null';
        }

        if ($type instanceof \ReflectionNamedType
            && in_array(strtolower($type->getName()), self::NON_NULLABLE_TYPE_NAMES)) {
            return $code;
        }

        return '?' . $code;
    }

    /**
     * @param \ReflectionType $type
     * @param boolean $inUnion 是否位于联合类型中(DNF 的交集类型需要括号)
     * @return string
     */
    private function toTypeString($type, $inUnion)
    {
        // php8 联合类型
        if ($type instanceof \ReflectionUnionType) {
            return implode('|', array_map(function ($one) {
                return $this->toTypeString($one, true);
            }, $type->getTypes()));
        }

        // php8.1 交叉类型, php8.2 之后可以出现在联合类型中(DNF)
        if ($type instanceof \ReflectionIntersectionType) {
            $s = implode('&', array_map(function ($one) {
                return $this->toTypeString($one, false);
            }, $type->getTypes()));

            return $inUnion ? '(' . $s . ')' : $s;
        }

        return $this->getNamedTypeString($type);
    }

    /**
     *
     * @param \ReflectionNamedType $type
     */
    private function getNamedTypeString($type)
    {
        if (! $type instanceof \ReflectionNamedType) {
            // 兜底: 极老版本 php 只有 ReflectionType
            return ltrim((string) $type, '\\');
        }

        // fix bug
        // in parallel extension, the return type name has a prefix \
        $typeStr = ltrim($type->getName(), '\\');
        $lower = strtolower($typeStr);

        $ts = '';

        // php8 mixed/null/void/never 不允许和 ? 同用
        if ($type->allowsNull() && ! in_array($lower, self::NON_NULLABLE_TYPE_NAMES)) {
            $ts .= '?';
        }

        // static/self/parent 和内置类型不特殊处理
        if (! $type->isBuiltin() && ! in_array($lower, self::SPECIAL_TYPE_NAMES)) {
            $ts .= '\\';
        }

        return $ts . $typeStr;
    }

    /**
     * 判断类型(联合/交叉类型会递归查找)中是否含有指定的类型名
     *
     * @param string $typeName
     * @return boolean
     */
    public function hasType($typeName)
    {
        return $this->typeHasName($this->getRef(), $typeName);
    }

    /**
     * @param \ReflectionType $type
     * @param string $typeName
     * @return boolean
     */
    private function typeHasName($type, $typeName)
    {
        if ($type instanceof \ReflectionNamedType) {
            return strcasecmp($type->getName(), $typeName) === 0;
        }

        if ($type && method_exists($type, 'getTypes')) {
            foreach ($type->getTypes() as $one) {
                if ($this->typeHasName($one, $typeName)) {
                    return true;
                }
            }
        }

        return false;
    }

}
