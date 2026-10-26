<?php

namespace Wqy\IdeHelper;

use ReflectionClassConstant;

class ClassConstantCode extends CodeBase implements ToCodeInterface
{
    public function __construct($cls, $name, $options = [])
    {
        $ref = class_exists('ReflectionClassConstant')
            ? new \ReflectionClassConstant($cls, $name) : new ClassConstant($cls, $name);
        parent::__construct($ref, $options);
    }

    public function toCode()
    {
        if (! $this->declaringInSameClass()) {
            return '';
        }

        $value = $this->exportValue($this->getRef()->getValue());

        return $this->getDocComment()
            . $this->getAttributesString()
            . $this->getPrefixSpaces()
            . $this->getModifier()
            . 'const '
            . $this->getConstantTypeString()
            . $this->getRef()->getName()
            . ' = '
            . (is_null($value) ? 'null' : $value)
            . ";\n"
        ;
    }

    /**
     * php8.3 类常量类型: `const string FOO = 'x';`
     *
     * @return string
     */
    private function getConstantTypeString()
    {
        $ref = $this->getRef();

        if (! method_exists($ref, 'hasType') || ! $ref->hasType()) {
            return '';
        }

        $type = $ref->getType();

        return $type ? $this->getTypeString($type) : '';
    }
}