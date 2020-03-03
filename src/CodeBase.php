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

    protected function getLevel($levelOrOptions, $default = 1)
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

    protected function getPrefixSpaces($levelOrOptions, $defaultLevel = 1)
    {
        return str_repeat($this->prefixSpace, $this->getLevel($levelOrOptions, $defaultLevel));
    }

    protected function getModifier()
    {
        if (method_exists($this->ref, 'getModifiers') && ($mods = $this->ref->getModifiers())) {
            return implode(' ', Reflection::getModifierNames($mods)) . ' ';
        }

        return '';
    }

    protected function getRef()
    {
        return $this->ref;
    }

    protected function getDefaultAssign($options)
    {
        if (isset($options['defaultValue'])) {
            return ' = ' . var_export($options['defaultValue'], true);
        }
        return '';
    }

    protected function getDocComment($levelOrOptions, $defaultLevel = 1)
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

    protected function wrapNamespace($code)
    {

        return 'namespace ' . $this->getNamespaceName() . " {\n\n"
            . $code
            . "\n}\n";
    }

    public function getNamespaceName()
    {
        $ns = method_exists($this->ref, 'getNamespaceName') ? $this->ref->getNamespaceName() : '';
        return $ns;
    }

    /**
     * @param \ReflectionType $type
     * @return string
     */
    protected function getTypeString($type)
    {
        $ts = '';

        if ($type->allowsNull()) {
            $ts .= '?';
        }
        if (! $type->isBuiltin()) {
            $ts .= '\\';
        }

        $typeStr = $type instanceof \ReflectionNamedType ? $type->getName() : ((string)$type);
        // fix bug
        // in parallel extension, the return type name has a prefix \
        $typeStr = ltrim($typeStr, '\\');

        $ts .= $typeStr . ' ';

        return $ts;
    }

    protected function declaringInSameClass($options)
    {
        return isset($options['declaringClass'])
            && $options['declaringClass'] == $this->getRef()->getDeclaringClass()->getName()
        ;

    }

    protected function getShortName()
    {
        $name = $this->ref->getShortName();

        if (preg_match('/^\d/', $name)) {
            $name = '___DIGIT_' . $name;
        }

        return $name;
    }
}
