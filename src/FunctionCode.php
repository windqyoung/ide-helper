<?php


namespace Wqy\IdeHelper;


class FunctionCode extends AbstractFunctionCode implements ToCodeInterface
{

    public function toCode($options = [])
    {
        $code = $this->toFunctionCode($options);
        return ! empty($options['namespace']) ? $this->wrapNamespace($code) : $code;
    }

}
