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
            . 'function '
            // 引用返回: function &foo()
            . ($ref->returnsReference() ? '&' : '')
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
        $type = $this->getReturnTypeRef();

        if (! $type) {
            return false;
        }

        // 有返回, 不判断名字
        if (is_null($typeName)) {
            return true;
        }

        return (new TypeCode($type))->hasType($typeName);

    }

    /**
     * 取返回类型.
     *
     * php 8.1 起, 内部函数的返回类型是"暂定(tentative)"的,
     * 此时 getReturnType() 返回 null, 需要回落到 getTentativeReturnType(),
     * 否则 ArrayObject::count() 这类方法会丢失返回类型.
     *
     * @return \ReflectionType|null
     */
    private function getReturnTypeRef()
    {
        $ref = $this->getRef();

        if (! method_exists($ref, 'hasReturnType')) {
            return null;
        }

        if ($ref->hasReturnType()) {
            return $ref->getReturnType();
        }

        if (method_exists($ref, 'hasTentativeReturnType') && $ref->hasTentativeReturnType()) {
            return $ref->getTentativeReturnType();
        }

        return null;
    }

    private function getReturnType()
    {
        $t = $this->getReturnTypeString();
        return $t ? (' : ' . $t) : $t;
    }

    private function getReturnTypeString()
    {
        $type = $this->getReturnTypeRef();

        if (! $type) {
            return '';
        }

        return $this->getTypeString($type);
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
            $export = $this->exportValue($val);
            if (is_null($export)) {
                // 无法还原的值(比如对象), 只声明变量
                $s .= "\n" . $pre . 'static $' . $name . ";\n";
                continue;
            }
            $s .= "\n" . $pre . 'static $' . $name . ' = ' . $export . ";\n";
        }

        return $s;
    }

    private function getParameters()
    {
        return (new ParameterArrayCode($this->getRef()->getParameters(), $this->getOptions()))->toCode();
    }

    protected function getFunctionBody()
    {
        return $this->generatorYield()
            . $this->getStaticVariables()
            . $this->getPrototypeString()
            . $this->getFunctionReturnStatement();
    }

    protected function getFunctionReturnStatement()
    {
        if (! $this->hasReturnType()) {
            return '';
        }
        if ($this->hasReturnType('void')) {
            return '';
        }
        // php8.1 never: 函数里出现 return 语句是编译期致命错误
        if ($this->hasReturnType('never')) {
            return '';
        }


        $rt = $this->getReturnVarValur();

        $space = $this->getPrefixSpaces($this->getLevel() + 1);
        return "\n$space\$rt = $rt;\n{$space}return \$rt;\n";
    }

    public function getDocCommentSign($pre, $withAtProperty = true, $withAtSee = true)
    {
        $name = $this->getRef()->getName();
        $sign = sprintf('%s%s(%s)', $this->getReturnTypeString(), $this->getShortName(), $this->getParameters());

        if (! $withAtProperty) {
            return $sign;
        }

        $rt = "$pre * @method $sign\n";
        if ($withAtSee) {
            $rt .= sprintf("$pre * @see \\%s::%s()\n", $this->getRef()->getDeclaringClass()->getName(), $name);
        }
        return $rt;
    }

    private function generatorYield()
    {
        $ref = $this->getRef();
        if ($ref->isGenerator()) {
            return $this->getPrefixSpaces($this->getLevel() + 1) . "/* 此函数是生成器 */ yield;\n";
        }
        return '';
    }

    private function getPrototypeString()
    {
        try {
            $proto = $this->getRef()->getPrototype();
            $see = '\\' . $proto->getDeclaringClass()->getName() . '::' . $proto->getName() . '()';
            $prefix = $this->getPrefixSpaces($this->getLevel() + 1);
            return "$prefix/**\n$prefix * @see {$see}\n$prefix */\n";
        }
        catch (\Exception $e) {
            return '';
        }
        catch (\Throwable $e) {
            return '';
        }
    }

    private function getReturnVarValur()
    {
        $rt = 'null';

        if ($this->hasReturnType('bool')) {
            $rt = 'true';
        }
        else if ($this->hasReturnType('true')) {
            $rt = 'true';
        }
        else if ($this->hasReturnType('false')) {
            $rt = 'false';
        }
        else if ($this->hasReturnType('string')) {
            $rt = '""';
        }
        else if ($this->hasReturnType('int')) {
            $rt = '1';
        }
        else if ($this->hasReturnType('float')) {
            $rt = '0.0';
        }
        else if ($this->hasReturnType('array')) {
            $rt = '[]';
        }
        else if (! $this->isStatic() && $this->hasReturnType('static')) {
            $rt = '$this';
        }

        return $rt;
    }
}
