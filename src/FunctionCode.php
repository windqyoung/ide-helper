<?php


namespace Wqy\IdeHelper;


class FunctionCode extends AbstractFunctionCode implements ToCodeInterface
{

    public function toCode()
    {
        $code = $this->toFunctionCode();
        $wrap = $this->isWrapWithNamespace();
        return $wrap ? $this->wrapNamespace($code) : $code;
    }

}
