<?php


namespace Wqy\IdeHelper;

/**
 * @method \ReflectionProperty getRef()
 * @author windq
 *
 */
class PropertyCode extends CodeBase implements ToCodeInterface
{

    public function toCode()
    {
        if (! $this->declaringInSameClass()) {
            return '';
        }

        return $this->getDocComment()
            . $this->getAttributesString()
            . $this->getPrefixSpaces()
            . $this->getModifier()
            . $this->getPropertyTypeString()
            . '$' . $this->getRef()->getName()
            . $this->getDefaultAssign()
            . ";\n";
    }

    private function getPropertyTypeString()
    {
        $ref = $this->getRef();
        if (! method_exists($ref, 'getType')) {
            return '';
        }
        return $this->getTypeString($ref->getType());
    }

}