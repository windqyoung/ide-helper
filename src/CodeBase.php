<?php


namespace Wqy\IdeHelper;

use Reflection;

class CodeBase
{
    /**
     * @var \ReflectionClass|\ReflectionMethod|\ReflectionProperty|\ReflectionFunction|\ReflectionParameter|\ReflectionExtension
     */
    private $ref;

    private $prefixSpace = '    ';


    public function __construct($ref)
    {
        $this->ref = $ref;
    }

    public function getLevel($levelOrOptions, $default)
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

        return $level;
    }

    public function getPrefixSpaces($levelOrOptions, $defaultLevel = 1)
    {
        return str_repeat($this->prefixSpace, $this->getLevel($levelOrOptions, $defaultLevel));
    }

    public function getModifier()
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

    public function getDefaultAssign($options)
    {
        if (isset($options['defaultValue'])) {
            return ' = ' . var_export($options['defaultValue'], true);
        }
        return '';
    }

    public function getDocComment($levelOrOptions, $defaultLevel = 1)
    {
        if (! method_exists($this->ref, 'getDocComment')) {
            return '';
        }

        $cmt = $this->ref->getDocComment();

        if (empty($cmt)) {
            return '';
        }

        $prefix = $this->getPrefixSpaces($levelOrOptions, $defaultLevel);

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
        $ns = method_exists($this->ref, 'getNamespaceName') ? $this->ref->getNamespaceName() : '';

        return 'namespace ' . $ns . " {\n\n"
            . $code
            . "\n}\n";
    }

    /**
     * @param \ReflectionType $type
     */
    public function getTypeString($type)
    {
        $ts = '';

        if ($type->allowsNull()) {
            $ts .= '?';
        }
        if (! $type->isBuiltin()) {
            $ts .= '\\';
        }

        $ts .= $type . ' ';

        return $ts;
    }

    public function declaringInSameClass($options)
    {
        return isset($options['declaringClass'])
            && $options['declaringClass'] == $this->getRef()->getDeclaringClass()->getName()
        ;

    }

    public function getShortName()
    {
        $name = $this->ref->getShortName();

        if (preg_match('/^\d/', $name)) {
            $name = '___DIGIT_' . $name;
        }

        return $name;
    }
}
