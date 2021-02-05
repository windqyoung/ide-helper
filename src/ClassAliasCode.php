<?php

namespace Wqy\IdeHelper;

class ClassAliasCode extends CodeBase implements ToCodeInterface
{
    private $alias;

    public function __construct($alias, $options = [])
    {
        parent::__construct(new \ReflectionClass($alias), $options);
        $this->alias = $alias;
    }

    public function getNamespaceName()
    {
        $pos = strrpos($this->alias, '\\');
        return substr($this->alias, 0, $pos);
    }

    public function getShortName()
    {
        $pos = strrpos($this->alias, '\\');
        return substr($this->alias, $pos + 1);
    }

    public function toCode()
    {
        $code = $this->toAliasCode();
        return $this->isWrapWithNamespace() ? $this->wrapNamespace($code) : $code;
    }

    private function toAliasCode()
    {
        $pre = $this->getPrefixSpaces();
        $ref = $this->getRef();
        return $pre
            . ($ref->isInterface() ? 'interface ' : 'class ')
            . $this->getShortName()
            . ' extends \\' . $ref->getName()
            . ' {} ';
    }
}