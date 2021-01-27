<?php

namespace Wqy\IdeHelper;

class ExtensionCode extends CodeBase implements ToCodeInterface
{
    /**
     * {@inheritDoc}
     * @see \Wqy\IdeHelper\ToCodeInterface::toCode()
     */
    public function toCode()
    {
        $ref = $this->getRef();

        $info = new ExtensionInfoCode($ref, $this->getOptions());

        $codes = [];

        foreach ($ref->getConstants() as $name => $value) {
            $codes[] = new ConstantCode($name, $value);
        }

        foreach ($ref->getFunctions() as $one) {
            $codes[] = new FunctionCode($one, $this->getOptions());
        }

        // fix bug
        // 在swoole扩展的输出中, 类重复了.
        // 使用类名当键过滤一下重复类
        $classCodes = [];
        foreach ($ref->getClasses() as $one) {
            $classCodes[$one->getName()] = new ClassCode($one, $this->getOptions());
        }
        $codes = array_merge($codes, array_values($classCodes));

        // 在同一扩展中, 按命名空间进行分组生成代码
        $group = new GroupByNamespaceCode($codes);

        return $info->toCode() . "\n" . $group->toCode();
    }


}