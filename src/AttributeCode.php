<?php

namespace Wqy\IdeHelper;

class AttributeCode extends CodeBase implements ToCodeInterface
{
    private $postNewline = false;

    private $prefixNewLine = true;

    private $levelSpace = true;

    /**
     * @return boolean
     */
    public function getPostNewline()
    {
        return $this->postNewline;
    }

    /**
     * @return boolean
     */
    public function getPrefixNewLine()
    {
        return $this->prefixNewLine;
    }

    /**
     * @return boolean
     */
    public function getLevelSpace()
    {
        return $this->levelSpace;
    }

    /**
     * @param boolean $prefixNewLine
     */
    public function setPrefixNewLine($prefixNewLine)
    {
        $this->prefixNewLine = $prefixNewLine;
    }

    /**
     * @param boolean $levelSpace
     */
    public function setLevelSpace($levelSpace)
    {
        $this->levelSpace = $levelSpace;
    }

    /**
     * @param boolean $postNewline
     */
    public function setPostNewline($postNewline)
    {
        $this->postNewline = $postNewline;
    }

    /**
     * {@inheritDoc}
     * @see \Wqy\IdeHelper\ToCodeInterface::toCode()
     */
    public function toCode()
    {
        /**
         * @var \ReflectionAttribute $ref
         */
        $ref = $this->getRef();
        $name = '\\' . $ref->getName();

        $code = '';
        if ($this->prefixNewLine) {
            $code .= "\n";
        }
        if ($this->levelSpace) {
            $code .= $this->getPrefixSpaces();
        }

        $code .= '#[' . $name;

        $argsArray = [];
        foreach ($ref->getArguments() as $k => $v) {
            $export = $this->exportValue($v);
            if (is_null($export)) {
                // 无法还原的参数(比如对象)直接跳过
                continue;
            }

            $as = '';
            if (is_string($k)) {
                // php8 命名参数
                $as .= $k . ': ';
            }
            $as .= $export;
            $argsArray[] = $as;
        }

        // 无参数时省略括号
        if ($argsArray) {
            $code .= '(' . implode(', ', $argsArray) . ')';
        }

        $code .= ']';

        if ($this->postNewline) {
            $code .= "\n";
        }

        return $code;

    }

}

