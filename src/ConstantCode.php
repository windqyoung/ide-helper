<?php

namespace Wqy\IdeHelper;


class ConstantCode extends CodeBase implements ToCodeInterface
{
    private $name;
    private $value;

    public function __construct($name, $value)
    {
        parent::__construct(null);
        $this->name = $name;
        $this->value = $value;
    }

    public function toCode()
    {
        $code = $this->toDefineCode();
        return $this->isWrapWithNamespace() ? $this->wrapNamespace($code) : $code;
    }

    private function toDefineCode()
    {
        $value = $this->exportValue($this->value);

        return sprintf("%sdefine('%s', %s);\n", $this->getPrefixSpaces(),
            $this->name, is_null($value) ? 'null' : $value);
    }
}
