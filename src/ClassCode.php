<?php


namespace Wqy\IdeHelper;

class ClassCode extends CodeBase implements ToCodeInterface
{
    public function toCode()
    {
        $code = $this->toClassCode();
        return $this->isWrapWithNamespace() ? $this->wrapNamespace($code) : $code;
    }

    private function toClassCode()
    {
        $pre = $this->getPrefixSpaces();

        return $this->getDocComment()
            . $this->getAttributesString()
            . $pre
            . $this->getClassKeyword()
            . $this->getShortName()
            . $this->getExtends()
            . $this->getImplements()
            . "\n$pre{\n"
            . $this->toClassBody()
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

    private function toClassBody()
    {
        return $this->getTraits()
            . $this->getConstants()
            . $this->getProperties()
            . $this->getMethods()
        ;
    }

    private function getTraits()
    {
        $ts = $this->getRef()->getTraitNames();

        if (empty($ts)) {
            return '';
        }

        return $this->getPrefixSpaces($this->getLevel() + 1) . 'use ' . implode(', ', array_map(function ($one) {
            return '\\' . $one;
        }, $ts)) . ";\n\n";

    }
    private function getConstants()
    {
        $cons = $this->getRef()->getConstants();

        if (! $cons) {
            return '';
        }

        return implode("\n", array_filter(array_map(function ($name) {

            $c = new ClassConstantCode($this->getRef()->getName(), $name, $this->getOptions());
            $c->setDeclaringClass($this->getRef()->getName(), true);
            $c->setLevel($this->getLevel() + 1);

            return $c->toCode();

        }, array_keys($cons)))) . "\n\n";
    }


    private function getProperties()
    {
        $ref = $this->getRef();

        $props = $ref->getProperties();

        if (! $props) {
            return '';
        }

        $defs = $ref->getDefaultProperties();

        $cls = $ref->getName();

        return implode("\n", array_filter(array_map(function (\ReflectionProperty $one) use ($defs, $cls) {

            $name = $one->getName();

            $c = new PropertyCode($one, $this->getOptions());
            $c->setDeclaringClass($cls, true);
            $c->setLevel($this->getLevel() + 1);
            if (isset($defs[$name]) && !is_null($defs[$name])) {
                $c->setDefaultValue($defs[$name], true);
            }

            return $c->toCode();

        }, $props))) . "\n\n";
    }


    private function getMethods()
    {
        $ref = $this->getRef();
        $mds = $ref->getMethods();

        if (! $mds) {
            return '';
        }

        $cls = $ref->getName();

        return implode("\n", array_filter(array_map(function (\ReflectionMethod $one) use ($cls) {

            $c = new MethodCode($one, $this->getOptions());
            $c->setDeclaringClass($cls, true);
            $c->setLevel($this->getLevel() + 1);
            return $c->toCode();

        }, $mds)));
    }
}