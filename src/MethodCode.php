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

}
