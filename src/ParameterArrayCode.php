<?php

namespace Wqy\IdeHelper;

/**
 * @method \ReflectionParameter[] getRef()
 * @author windq
 *
 */
class ParameterArrayCode extends CodeBase implements ToCodeInterface
{
    public function toCode()
    {
        $hasAttr = false;
        foreach ($this->getRef()/* 参数数组 */ as $one) {
            if (method_exists($one, 'getAttributes') && $one->getAttributes()) {
                $hasAttr = true;
                break;
            }
        }
        $level = $hasAttr ? $this->getLevel() + 1 : $this->getLevel();
        $glue = $hasAttr ? ",\n" . $this->getPrefixSpaces($level) : ', ';

        return implode($glue, array_map(function ($one) use ($level) {
            return (new ParameterCode($one, ['level' => $level]))->toCode();
        }, $this->getRef()));
    }


}