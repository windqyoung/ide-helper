<?php


namespace Wqy\IdeHelper;


abstract class AbstractFunctionCode extends CodeBase
{
    protected function toFunctionCode()
    {
        $ref = $this->getRef();

        $pre = $this->getPrefixSpaces();
        $code = $this->getDocComment()
            . $this->getAttributesString()
            . $pre . $this->getModifier()
            . ($ref->returnsReference() ? '& ' : '')
            . 'function '
            . $this->getShortName()
            . '('
            . $this->getParameters()
            . ')'
            . $this->getReturnType()
        ;

        if (method_exists($ref, 'isAbstract') && $ref->isAbstract()) {
            $code .= ";\n";
        }
        else {
            $code .= "\n"
            . $pre . "{\n"
            . $this->getFunctionBody()
            . $pre . "}\n";
        }

        return $code;
    }

    private function hasReturnType($typeName = null)
    {
        $ref = $this->getRef();
        // 无方法
        if (! method_exists($ref, 'hasReturnType')) {
            return false;
        }

        // 无返回
        if (! $ref->hasReturnType() ) {
            return false;
        }

        // 有返回, 不判断名字
        if (is_null($typeName)) {
            return true;
        }

        return (new TypeCode($ref->getReturnType()))->hasType($typeName);

    }

    private function getReturnType()
    {
        if (! $this->hasReturnType()) {
            return '';
        }

        $type = $this->getRef()->getReturnType();

        if (! $type) {
            return '';
        }

        return ' : ' . $this->getTypeString($type);
    }

    private function getStaticVariables()
    {
        $stVars = $this->getRef()->getStaticVariables();
        if (empty($stVars)) {
            return '';
        }

        $pre = $this->getPrefixSpaces($this->getLevel() + 1);

        $s = '';
        foreach ($stVars as $name => $val) {
            $s .= "\n" . $pre . 'static $' . $name . ' = ' . var_export($val, true) . ";\n";
        }

        return $s;
    }

    private function getParameters()
    {
        return (new ParameterArrayCode($this->getRef()->getParameters(), $this->getOptions()))->toCode();
    }

    protected function getFunctionBody()
    {
        return $this->getStaticVariables() . $this->getFunctionReturnStatement();
    }

    protected function getFunctionReturnStatement()
    {
        if (! $this->hasReturnType()) {
            return '';
        }
        if ($this->hasReturnType('void')) {
            return '';
        }

        return "\n" . $this->getPrefixSpaces($this->getLevel() + 1) . "return null;\n";
    }
}
