<?php

namespace Wqy\IdeHelper;

class DocCommentClassCode extends CodeBase implements ToCodeInterface
{
    /**
     * @var \ReflectionProperty[]|string[]
     */
    private $docCommentProperties = [];

    /**
     * @var \ReflectionMethod[]|string[]
     */
    private $docCommentMethods = [];

    /**
     * @var \ReflectionClass|string
     */
    private $parentClass;

    /**
     * @var \ReflectionClass[]|string[]
     */
    private $interfaces = [];

    private $classname;

    public function __construct($classname, $options = [])
    {
        parent::__construct($classname, $options);
        $this->classname = $classname;
    }

    /**
     * @param multitype:ReflectionProperty  $docCommentProperties
     */
    public function setDocCommentProperties($docCommentProperties)
    {
        $this->docCommentProperties = $docCommentProperties;
    }

    public function addDocCommentProperties($props)
    {
        if (! is_array($props)) {
            $props = [$props];
        }
        $this->docCommentProperties = array_unique(array_merge($this->docCommentProperties, $props));
    }

    /**
     * @param multitype:ReflectionMethod  $docCommentMethods
     */
    public function setDocCommentMethods($docCommentMethods)
    {
        $this->docCommentMethods = $docCommentMethods;
    }

    public function addDocCommentMethods($ms)
    {
        if (! is_array($ms)) {
            $ms = [$ms];
        }
        $this->docCommentMethods = array_unique(array_merge($this->docCommentMethods, $ms));
    }

    /**
     * @param \ReflectionClass|string $parentClass
     */
    public function setParentClass($parentClass)
    {
        $this->parentClass = $parentClass;
    }

    /**
     * @param multitype:ReflectionClass $interfaces
     */
    public function setInterfaces($interfaces)
    {
        $this->interfaces = $interfaces;
    }

    public function addInterfaces($interfaces)
    {
        if (! is_array($interfaces)) {
            $interfaces = [$interfaces];
        }
        $this->interfaces = array_unique(array_merge($this->interfaces, $interfaces));
    }

    public function getNamespaceName()
    {
        $pos = strrpos($this->classname, '\\');
        return substr($this->classname, 0, $pos);
    }

    public function getShortName()
    {
        $pos = strrpos($this->classname, '\\');
        return substr($this->classname, $pos + 1);
    }


    public function toCode()
    {
        $code = $this->toClassCode();
        return $this->isWrapWithNamespace() ? $this->wrapNamespace($code) : $code;
    }

    public function getDocComment()
    {
        $pre = $this->getPrefixSpaces();

        $cmt = "$pre/**\n";

        foreach ($this->docCommentProperties as $p) {
            $cmt .= $this->docCommentProperty($p, $pre);
        }

        foreach ($this->docCommentMethods as $m) {
            $cmt .= $this->docCommentMethed($m, $pre);
        }

        $cmt .= "$pre */\n";
        return $cmt;
    }

    /**
     * @param \ReflectionProperty|string $p
     * @param string $pre
     */
    private function docCommentProperty($p, $pre)
    {
        if (is_string($p)) {
            return sprintf("%s * @property %s\n", $pre, trim($p));
        }

        $to = new PropertyCode($p);
        return $to->getDocCommentSign($pre);
    }

    /**
     * @param \ReflectionMethod|string $m
     * @param string $pre
     */
    private function docCommentMethed($m, $pre)
    {
        if (is_string($m)) {
            return sprintf("%s * %s\n", $pre, trim($m));
        }

        $to = new MethodCode($m);
        return $to->getDocCommentSign($pre);
    }

    private function getExtends()
    {
        if (! $this->parentClass) {
            return '';
        }

        $pName = is_string($this->parentClass) ? $this->parentClass : $this->parentClass->getName();
        return ' extends \\' . $pName;
    }

    private function getImplements()
    {
        if (! $this->interfaces) {
            return '';
        }

        return ' implements ' . implode(', ', array_unique(array_map(function ($f) {
            $ifName = $f instanceof \ReflectionClass ? $f->getName() : $f;
            return '\\' . trim($ifName, '\ ');
        }, $this->interfaces)));
    }

    private function toClassCode()
    {
        $pre = $this->getPrefixSpaces();

        return $this->getDocComment()
            . $pre . 'class '
            . $this->getShortName()
            . $this->getExtends()
            . $this->getImplements()
            . "\n$pre{\n"
            . "$pre}\n"
        ;
    }

}