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
        if (strtolower($ref->getName()) == '__tostring') {
            return "\n" . $this->getPrefixSpaces($this->getLevel($options) + 1) . "return '';\n";
        }
        return parent::getFunctionReturnStatement($options);
    }
}
