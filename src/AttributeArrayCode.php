<?php

namespace Wqy\IdeHelper;


class AttributeArrayCode extends CodeBase implements ToCodeInterface
{

    /**
     * {@inheritDoc}
     * @see \Wqy\IdeHelper\ToCodeInterface::toCode()
     */
    public function toCode()
    {
        return implode('', array_map(function ($ref) {
            $ac = new AttributeCode($ref, $this->getOptions());
            return $ac->toCode();
        }, $this->getRef())) . "\n";
    }
}