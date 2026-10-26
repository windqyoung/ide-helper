<?php


namespace Wqy\IdeHelper;

class ParameterCode extends CodeBase implements ToCodeInterface
{
    public function toCode()
    {
        $code = '';
        if ($this->hasAttributes()) {
            $code .= $this->getAttributesString() . $this->getPrefixSpaces();
        }

        return $code
            . $this->getModifier()
            . $this->getType()
            . $this->getName()
            . $this->getDefaultAssign()
        ;
    }

    protected function getModifier()
    {
        $ref = $this->getRef();
        if (method_exists($ref, 'isPromoted') && $ref->isPromoted()) {
            $refProp = $ref->getDeclaringClass()->getProperty($ref->getName());
            return (new PropertyCode($refProp))->getModifier();
        }

        return '';
    }

    private function getName()
    {
        $s = '';

        $ref = $this->getRef();

        if ($ref->isPassedByReference()) {
            $s .= '&';
        }

        if (method_exists($ref, 'isVariadic') && $ref->isVariadic()) {
            $s .= '...';
        }
        $s .= '$' . $this->toValidName($ref->getName());

        return $s;
    }

    private $placeIndex = 0;

    private function toValidName($name) {
        if (! preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $name)) {
            return '____args_' . $this->placeIndex++ . "/* name = $name */";
        }
        return $name;
    }

    private function getType()
    {
        $ref = $this->getRef();

        $s = '';

        if (method_exists($ref, 'hasType') && $ref->hasType()) {
            $type = $ref->getType();

            $code = new TypeCode($type, $this->getOptions());

            // php8.4 起废弃隐式可空写法: `int $x = null` 必须写成 `?int $x = null`
            if ($type && ! $type->allowsNull() && $this->isNullDefault()) {
                $code->setForceNullable();
            }

            $s = $code->toCode();
        }
        else if (method_exists($ref, 'getClass')) {
            // php8 起这些方法都标记为废弃, 只作为老版本的兜底
            $cls = $ref->getClass();
            if ($cls) {
                $s .= '\\' . $cls->getName() . ' ';
            }
            else if (method_exists($ref, 'isArray') && $ref->isArray()) {
                $s .= 'array ';
            }
            else if (method_exists($ref, 'isCallable') && $ref->isCallable()) {
                $s .= 'callable ';
            }
        }

        return $s;
    }

    /**
     * 参数是否会被渲染成 `= null` 的默认值.
     *
     * 内部函数的可选参数取不到默认值(isDefaultValueAvailable 为 false),
     * 此时只能输出 `= null`, 类型也必须相应变成可空类型.
     *
     * @return boolean
     */
    private function isNullDefault()
    {
        $ref = $this->getRef();

        if ($ref->isDefaultValueAvailable()) {
            return is_null($ref->getDefaultValue());
        }

        if (method_exists($ref, 'isVariadic') && $ref->isVariadic()) {
            return false;
        }

        return $ref->isOptional();
    }

    protected function getDefaultAssign()
    {
        $s = '';
        $ref = $this->getRef();

        if ($ref->isDefaultValueAvailable()) {
            if ($ref->isDefaultValueConstant()) {
                $s .= ' = \\' . $ref->getDefaultValueConstantName();
            } else {
                // php8.1 `new` 初始化语法: var_export 会生成非法的 `Foo::__set_state()`,
                // 这种默认值无法还原, 直接省略
                $export = $this->exportValue($ref->getDefaultValue());
                if (! is_null($export)) {
                    $s .= ' = ' . $export;
                }
            }
        } else if (method_exists($ref, 'isVariadic') && $ref->isVariadic()) {
            // 可变参数, 没默认值
        } else if ($ref->isOptional()) {
            $s .= ' = null';
        }

        return $s;
    }
}
