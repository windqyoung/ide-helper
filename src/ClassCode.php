<?php


namespace Wqy\IdeHelper;

class ClassCode extends CodeBase implements ToCodeInterface
{
    public function toCode($options = [])
    {
        $code = $this->toClassCode($options);
        return isset($options['namespace']) && $options['namespace'] === false ? $code : $this->wrapNamespace($code);
    }

    private function toClassCode($options)
    {
        $pre = $this->getPrefixSpaces($options);

        $ref = $this->getRef();

        return $this->getDocComment($options)
            . $pre
            . $this->getClassKeyword()
            . $this->getShortName()
            . $this->getExtends()
            . $this->getImplements()
            . "\n$pre{\n"
            . $this->toClassBody($options)
            . "$pre}\n"
        ;
    }

    private function getClassKeyword()
    {
        $ref = $this->getRef();

        if ($ref->isInterface()) {
            return 'interface ';
        }

        if ($ref->isTrait()) {
            return 'trait ';
        }

        return ($ref->isAbstract() ? 'abstract ' : '') . 'class ';
    }

    private function getExtends()
    {
        $p = $this->getRef()->getParentClass();

        if ($p) {
            return ' extends \\' . $p->getName();
        }

        return '';
    }

    private function getImplements()
    {
        $ref = $this->getRef();

        $impsRaw = $ref->getInterfaceNames();

        // fix bug
        // class Traversable can't implements
        $imps = array_filter($impsRaw, function ($x) { return $x !== 'Traversable'; });

        if (! $imps) {
            return '';
        }

        return ($ref->isInterface() ? ' extends ' : ' implements ')
            . implode(', ', array_map(function ($one) {
                return '\\' . $one;
            }, $imps));
    }

    private function toClassBody($options)
    {
        $options['level'] = $this->getLevel($options) + 1;

        return $this->getTraits($options)
            . $this->getConstants($options)
            . $this->getProperties($options)
            . $this->getMethods($options)
        ;
    }

    private function getTraits($options)
    {
        $ts = $this->getRef()->getTraitNames();

        if (empty($ts)) {
            return '';
        }

        return $this->getPrefixSpaces($options) . 'use ' . implode(', ', array_map(function ($one) {
            return '\\' . $one;
        }, $ts)) . ";\n\n";

    }
    private function getConstants($options)
    {
        $cons = $this->getRef()->getConstants();

        if (! $cons) {
            return '';
        }

        $cls = $this->getRef()->getName();

        $options['declaringClass'] = $cls;

        return implode("\n", array_filter(array_map(function ($name) use ($cls, $options) {

            $c = new ClassConstantCode($cls, $name);
            return $c->toCode($options);

        }, array_keys($cons)))) . "\n\n";
    }


    private function getProperties($options)
    {
        $ref = $this->getRef();

        $props = $ref->getProperties();

        if (! $props) {
            return '';
        }

        $cls = $ref->getName();

        $options['declaringClass'] = $cls;

        $defs = $ref->getDefaultProperties();

        return implode("\n", array_filter(array_map(function (\ReflectionProperty $one) use ($options, $defs) {

            $name = $one->getName();

            if (isset($defs[$name]) && !is_null($defs[$name])) {
                $options['defaultValue'] = $defs[$name];
            }

            $c = new PropertyCode($one);
            return $c->toCode($options);

        }, $props))) . "\n\n";
    }


    private function getMethods($options)
    {
        $ref = $this->getRef();
        $mds = $ref->getMethods();

        if (! $mds) {
            return '';
        }

        $cls = $ref->getName();

        $options['declaringClass'] = $cls;

        return implode("\n", array_filter(array_map(function (\ReflectionMethod $one) use ($options) {

            $c = new MethodCode($one);
            return $c->toCode($options);

        }, $mds)));
    }
}