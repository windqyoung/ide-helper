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

        $code .= "#[{$name}(";

        $argsArray = [];
        foreach ($ref->getArguments() as $k => $v) {
            $as = '';
            if (is_string($k)) {
                $as .= $k . ': ';
            }
            $as .= var_export($v, true);
            $argsArray[] = $as;
        }

        $code .= implode(', ', $argsArray);

        $code .=")]";

        if ($this->postNewline) {
            $code .= "\n";
        }

        return $code;

    }

}

