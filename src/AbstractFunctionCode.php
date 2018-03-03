<?php


namespace Wqy\IdeHelper;


abstract class AbstractFunctionCode extends CodeBase
{
    public function toFunctionCode($options)
    {
        $ref = $this->getRef();

        $pre = $this->getPrefixSpaces($options);
        $code = $this->getDocComment($options)
            . $pre
            . $this->getModifier()
            . ($ref->returnsReference() ? '& ' : '')
            . 'function '
            . $this->getShortName()
            . '('
            . $this->getParameters()
            . ')'
            . $this->getReturnType()
        ;

        if ($ref->isAbstract()) {
            $code .= ";\n";
        }
        else {
            $code .= "\n"
            . $pre . "{\n"
            . $this->getStaticVariables($options)
            . $pre . "}\n";
        }

        return $code;
    }
    public function getReturnType()
    {
        $ref = $this->getRef();
        if (! (method_exists($ref, 'hasReturnType') && $ref->hasReturnType())) {
            return '';
        }

        $type = $ref->getReturnType();

        if (! $type) {
            return '';
        }

        return ' : ' . $this->getTypeString($type);
    }
    public function getStaticVariables($options)
    {
        $stVars = $this->getRef()->getStaticVariables();
        if (empty($stVars)) {
            return '';
        }

        $pre = $this->getPrefixSpaces($this->getLevel($options, 1) + 1);

        $s = '';
        foreach ($stVars as $name => $val) {
            $s .= "\n" . $pre . 'static $' . $name . ' = ' . var_export($val, true) . ";\n";
        }

        return $s;
    }

    public function getParameters()
    {
        return implode(', ', array_map(function (\ReflectionParameter $one) {
            return (new ParameterCode($one))->toCode();
        }, $this->getRef()->getParameters()));
    }
}
