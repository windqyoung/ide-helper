<?php

namespace Wqy\IdeHelper;


class MethodCode extends AbstractFunctionCode implements ToCodeInterface
{

    public function toCode($options = [])
    {
        if (! $this->declaringInSameClass($options)) {
            return '';
        }

        return $this->toFunctionCode($options);
    }

    protected function getFunctionReturnStatement($options)
    {
        $ref = $this->getRef();
        $name = strtolower($ref->getName());

        if ($name == '__tostring') {
            return "\n" . $this->getPrefixSpaces($this->getLevel($options) + 1) . "return '';\n";
        }
        else if ($name == '__construct') {
            return "";
        }

        return parent::getFunctionReturnStatement($options);
    }

    protected function getModifier()
    {
        /** @var \ReflectionMethod $ref */
        $ref = $this->getRef();
        $rc = $ref->getDeclaringClass();

        // 接口不需要
        if ($rc->isInterface()) {
            return '';
        }

        return parent::getModifier();
    }

}
