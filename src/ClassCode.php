<?php


namespace Wqy\IdeHelper;
use ReflectionEnum;

class ClassCode extends CodeBase implements ToCodeInterface
{
    public function __construct($ref, $options = [])
    {
        if ($this->isEnum($ref)) {
            $ref = new ReflectionEnum($ref->getName());
        }
        parent::__construct($ref, $options);
    }

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
            . $this->getEnumBackingType()
            . $this->getExtends()
            . $this->getImplements()
            . "\n$pre{\n"
            . $this->toClassBody()
            . "$pre}\n"
        ;
    }

    private function getEnumBackingType()
    {
        $ref = $this->getRef();
        if ($ref instanceof ReflectionEnum && $ref->isEnum()) {
            return ' : ' . $ref->getBackingType();
        }
    }

    private function getClassKeyword()
    {
        $ref = $this->getRef();

        if ($this->isEnum($ref)) {
            return 'enum ';
        }

        if ($ref->isInterface()) {
            return 'interface ';
        }

        if ($ref->isTrait()) {
            return 'trait ';
        }

        $code = 'class ';

        if ($ref->isAbstract()) {
            $code = 'abstract ' . $code;
        }
        else if ($ref->isFinal()) {
            $code = 'final ' . $code;
        }

        return $code;
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
        // 枚举接口不需要实现
        $imps = array_filter($impsRaw, function ($x) {
            return !in_array($x, [ 'Traversable', 'UnitEnum', 'BackedEnum' ]);
        });

        if (! $imps) {
            return '';
        }

        usort($imps, function ($a, $b) {
            return in_array($a, class_implements($b)) ? -1 : 1;
        });

        return ($ref->isInterface() ? ' extends ' : ' implements ')
            . implode(', ', array_map(function ($one) {
                return '\\' . $one;
            }, $imps));
    }

    private function toClassBody()
    {
        return $this->getTraits()
            . $this->getEnumCases()
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
        $ref = $this->getRef();

        $cons = $ref->getConstants();

        if (! $cons) {
            return '';
        }

        // 过滤掉枚举常量
        if ($this->isEnum($ref)) {
            foreach ($cons as $k => $v) {
                if ($ref->hasCase($k)) {
                    unset($cons[$k]);
                }
            }
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

            // 枚举不得包含属属
            if ($this->isEnum($one->getDeclaringClass())) {
                return '';
            }

            return $this->getClassPropertyCode($one, $cls, $defs);
        }, $props))) . "\n\n";
    }

    private function getClassPropertyCode(\ReflectionProperty $one, $cls, $defs)
    {
        // 如果是构造方法提升, 则忽略此属属
        if (method_exists($one, 'isPromoted') && $one->isPromoted()) {
            return '';
        }
        $name = $one->getName();

        $c = new PropertyCode($one, $this->getOptions());
        $c->setDeclaringClass($cls, true);
        $c->setLevel($this->getLevel() + 1);
        if (isset($defs[$name]) && !is_null($defs[$name])) {
            $c->setDefaultValue($defs[$name], true);
        }

        return $c->toCode();
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

            if ($this->isEnum($one->getDeclaringClass()) && in_array($one->getName(), ['cases', 'from', 'tryFrom'])) {
                return '';
            }

            $c = new MethodCode($one, $this->getOptions());
            $c->setDeclaringClass($cls, true);
            $c->setLevel($this->getLevel() + 1);
            return $c->toCode();

        }, $mds)));
    }

    private function getEnumCases()
    {
        $ref = $this->getRef();
        if (! $this->isEnum($ref)) {
            return '';
        }

        return implode("\n", array_filter(array_map(function ($case) {
            $code = new EnumCaseCode($case, $this->getOptions());
            $code->setLevel($this->getLevel() + 1);
            return $code->toCode();
        }, $ref->getCases()))) . "\n\n";
    }

    private function isEnum($ref)
    {
        return method_exists($ref, 'isEnum') && $ref->isEnum();
    }
}