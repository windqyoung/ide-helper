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

        $info = new ExtensionInfoCode($ref);

        $codes = [];

        foreach ($ref->getConstants() as $name => $value) {
            $codes[] = new ConstantCode($name, $value);
        }

        foreach ($ref->getFunctions() as $one) {
            $codes[] = new FunctionCode($one);
        }

        foreach ($ref->getClasses() as $one) {
            $codes[] = new ClassCode($one);
        }

        // 在同一扩展中, 按命名空间进行分组生成代码
        $group = new GroupByNamespaceCode($codes);

        return $info->toCode($options) . "\n" . $group->toCode($options);
    }


}