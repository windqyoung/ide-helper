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

        return $this->getDocComment()
            . $this->getAttributesString()
            . $this->getPrefixSpaces()
            . $this->getModifier()
            . 'const '
            . $this->getRef()->getName()
            . ' = '
            . var_export($this->getRef()->getValue(), true)
            . ";\n"
        ;
    }
}