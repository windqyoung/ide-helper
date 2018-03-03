<?php


namespace Wqy\IdeHelper;


class FunctionCode extends AbstractFunctionCode implements ToCodeInterface
{

    public function toCode($options = [])
    {
        return $this->wrapNamespace($this->toFunctionCode($options));
    }

}
