<?php

namespace Wqy\IdeHelper;


class MethodCode extends AbstractFunctionCode implements ToCodeInterface
{

    public function toCode()
    {
        if (! $this->declaringInSameClass()) {
            return '';
        }

        return $this->toFunctionCode();
    }

    protected function getFunctionReturnStatement()
    {
        $ref = $this->getRef();
        $name = strtolower($ref->getName());

        if ($name == '__tostring') {
            return "\n" . $this->getPrefixSpaces($this->getLevel() + 1) . "return '';\n";
        }
        else if ($name == '__construct') {
            return "";
        }

        return parent::getFunctionReturnStatement();
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
