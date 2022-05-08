<?php


namespace Wqy\IdeHelper;

class EnumCaseCode extends CodeBase implements ToCodeInterface
{

    public function toCode()
    {
        $pre = $this->getPrefixSpaces();
        /**
         * @var \ReflectionEnum
         */
        $ref = $this->getRef();

        return $this->getDocComment()
            . $this->getAttributesString()
            . $this->getPrefixSpaces()
            . 'case '
            . $ref->getName()
            . $this->getEnumBackingValue()
            . ";\n";

    }

    private function getEnumBackingValue()
    {
        $ref = $this->getRef();
        if ($ref instanceof \ReflectionEnumBackedCase) {
            return ' = ' . var_export($ref->getBackingValue(), true);
        }
    }

}








