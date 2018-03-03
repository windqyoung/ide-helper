<?php

namespace Wqy\IdeHelper;

class ExtensionCode extends CodeBase implements ToCodeInterface
{
    /**
     * {@inheritDoc}
     * @see \Wqy\IdeHelper\ToCodeInterface::toCode()
     */
    public function toCode($options = [])
    {
        $ref = $this->getRef();

        $codes = [new ExtensionInfoCode($ref)];

        foreach ($ref->getConstants() as $name => $value) {
            $codes[] = new ConstantCode($name, $value);
        }

        foreach ($ref->getFunctions() as $one) {
            $codes[] = new FunctionCode($one);
        }

        foreach ($ref->getClasses() as $one) {
            $codes[] = new ClassCode($one);
        }

        return implode("\n", array_map(function ($one) use ($options) {
            return $one->toCode($options);
        }, $codes));
    }


}