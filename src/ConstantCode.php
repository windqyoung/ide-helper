<?php

namespace Wqy\IdeHelper;


class ConstantCode extends CodeBase implements ToCodeInterface
{
    private $name;
    private $value;

    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function toCode($options = [])
    {
        return $this->wrapNamespace($this->toDefineCode($options));
    }

    public function toDefineCode($options = [])
    {
        return sprintf("%sdefine('%s', %s);\n", $this->getPrefixSpaces($options),
            $this->name, var_export($this->value, true));
    }
}
