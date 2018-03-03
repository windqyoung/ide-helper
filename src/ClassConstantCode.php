<?php

namespace Wqy\IdeHelper;

use ReflectionClassConstant;

class ClassConstantCode extends CodeBase implements ToCodeInterface
{
    public function __construct($cls, $name)
    {
        $ref = class_exists(\ReflectionClassConstant::class)
            ? new \ReflectionClassConstant($cls, $name) : new ClassConstant($cls, $name);
        parent::__construct($ref);
    }

    public function toCode($options = [])
    {
        if (! $this->declaringInSameClass($options)) {
            return '';
        }

        return $this->getDocComment($options)
            . $this->getPrefixSpaces($options)
            . $this->getModifier()
            . 'const '
            . $this->getRef()->getName()
            . ' = '
            . var_export($this->getRef()->getValue(), true)
            . ";\n"
        ;
    }
}