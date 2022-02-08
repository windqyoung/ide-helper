<?php


namespace Wqy\IdeHelper;

use Reflection;

class CodeBase
{
    /**
     * @var \ReflectionClass|\ReflectionMethod|\ReflectionProperty|\ReflectionFunction|\ReflectionParameter|\ReflectionExtension|\ReflectionAttribute|\ReflectionAttribute[]|\ReflectionParameter[]
     */
    private $ref;

    /**
     * 每一级的缩进
     * @var string
     */
    private $prefixSpace = '    ';

    /**
     * 缩进等级
     * @var integer
     */
    private $level = 1;

    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @var string
     */
    private $declaringClass;

    /**
     * @var boolean
     */
    private $wrapWithNamespace = true;

    public function __construct($ref, $options = [])
    {
        $this->ref = $ref;

        $this->setLevel($options);
        $this->setDefaultValue($options);
        $this->setDeclaringClass($options);
    }

    /**
     * @return boolean
     */
    public function isWrapWithNamespace()
    {
        return $this->wrapWithNamespace;
    }

    /**
     * @param boolean $wrapWithNamespace
     */
    public function setWrapWithNamespace($wrapWithNamespace)
    {
        $this->wrapWithNamespace = $wrapWithNamespace;
    }

    public function setLevel($levelOrOptions, $default = 1)
    {
        if (is_int($levelOrOptions)) {
            $level = $levelOrOptions;
        }
        else if (isset($levelOrOptions['level'])) {
            $level = $levelOrOptions['level'];
        }
        else {
            $level = $default;
        }

        return $this->level = $level;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function getPrefixSpaces($level = null)
    {
        if (is_null($level)) {
            $level = $this->getLevel();
        }
        return str_repeat($this->prefixSpace, $level);
    }

    protected function getModifier()
    {
        if (method_exists($this->ref, 'getModifiers') && ($mods = $this->ref->getModifiers())) {
            return implode(' ', Reflection::getModifierNames($mods)) . ' ';
        }

        return '';
    }

    public function getRef()
    {
        return $this->ref;
    }

    public function setDefaultValue($options, $raw = false)
    {
        if ($raw) {
            $this->defaultValue = $options;
        }
        else if (isset($options['defaultValue'])) {
            $this->defaultValue = $options['defaultValue'];
        }
    }

    protected function getDefaultAssign()
    {
        if (method_exists($ref = $this->getRef(), 'getDefaultValue')) {
            $def = $ref->getDefaultValue();

            // 如果是null, 不赋值
            if (is_null($def)) {
                return '';
            }
            return ' = ' . var_export($ref->getDefaultValue(), true);
        }

        return is_null($this->defaultValue) ? '' : (' = ' . var_export($this->defaultValue, true));
    }

    public function getDocComment()
    {
        if (! method_exists($this->ref, 'getDocComment')) {
            return '';
        }

        $cmt = $this->ref->getDocComment();

        if (empty($cmt)) {
            return '';
        }

        $prefix = $this->getPrefixSpaces();

        $cmtFmt = implode("\n", array_map(function ($one) use ($prefix) {
            $l = ltrim($one);
            if ($l[0] == '/') {
                return $prefix . $l;
            }
            else if ($l[0] == '*') {
                return $prefix . ' ' . $l;
            }

            return $one;
        }, explode("\n", $cmt)));

        return $cmtFmt . "\n";
    }

    public function wrapNamespace($code)
    {
        return 'namespace ' . $this->getNamespaceName() . " {\n\n"
            . $code
            . "\n}\n";
    }

    public function getNamespaceName()
    {
        return $this->ref && method_exists($this->ref, 'getNamespaceName') ? $this->ref->getNamespaceName() : '';
    }

    /**
     * @param string $declaringClass
     */
    public function setDeclaringClass($options, $raw= false)
    {
        if ($raw) {
            $this->declaringClass = $options;
        }
        else if (isset($options['declaringClass'])) {
            $this->declaringClass = $options['declaringClass'];
        }
    }

    public function declaringInSameClass()
    {
        return $this->declaringClass == $this->getRef()->getDeclaringClass()->getName();
    }

    public function getShortName()
    {
        $name = $this->ref->getShortName();

        if (preg_match('/^\d/', $name)) {
            $name = '___DIGIT_' . $name;
        }

        return $name;
    }

    /**
     * @param \ReflectionType $type
     * @return string
     */
    public function getTypeString($type)
    {
        return (new TypeCode($type, $this->getOptions()))->toCode();
    }

    public function getOptions()
    {
        return [
            'level' => $this->getLevel(),
        ];
    }

    public function hasAttributes()
    {
        return method_exists($this->ref, 'getAttributes') && $this->ref->getAttributes();
    }

    public function getAttributesString()
    {
        if (! method_exists($this->ref, 'getAttributes')) {
            return '';
        }

        return (new AttributeArrayCode($this->ref->getAttributes(), $this->getOptions()))->toCode();
    }
}
