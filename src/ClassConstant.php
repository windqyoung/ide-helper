<?php


namespace Wqy\IdeHelper;

class ClassConstant
{

    /* 属性 */
    public $name;

    /**
     * @var string
     */
    public $class;

    /* 方法 */
    public function __construct($class, $name)
    {
        $this->name = $name;
        $this->class = (new \ReflectionClass($class))->getName();
    }

    public function getDeclaringClass()
    {
        return new \ReflectionClass($this->class);
    }

    public function getDocComment()
    {
        return '';
    }

    public function getModifiers()
    {
        return 0;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->getDeclaringClass()->getConstant($this->name);
    }

    public function isPrivate()
    {
        return false;
    }

    public function isProtected()
    {
        return false;
    }

    public function isPublic()
    {
        return false;
    }
}

