<?php


namespace Wqy\IdeHelper;


class PropertyCode extends CodeBase implements ToCodeInterface
{

    public function toCode($options = [])
    {
        if (! $this->declaringInSameClass($options)) {
            return '';
        }

        return $this->getDocComment($options)
            . $this->getPrefixSpaces($options)
            . $this->getModifier()
            . '$' . $this->getRef()->getName()
            . $this->getDefaultAssign($options)
            . ";\n";
    }

}