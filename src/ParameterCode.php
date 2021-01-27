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
            . $this->getType()
            . $this->getName()
            . $this->getDefaultAssign()
        ;
    }

    private function getName()
    {
        $s = '';

        $ref = $this->getRef();

        if ($ref->isPassedByReference()) {
            $s .= '& ';
        }

        if (method_exists($ref, 'isVariadic') && $ref->isVariadic()) {
            $s .= '...';
        }
        $s .= '$' . $ref->getName();

        return $s;
    }

    private function getType()
    {
        $ref = $this->getRef();

        $s = '';

        if (method_exists($ref, 'hasType') && $ref->hasType()) {
            $s = $this->getTypeString($ref->getType());
        }
        else {
            $cls = $ref->getClass();
            if ($cls) {
                $s .= '\\' . $cls->getName() . ' ';
            }
            else if ($ref->isArray()) {
                $s .= 'array ';
            }
            else if ($ref->isCallable()) {
                $s .= 'callable ';
            }
        }

        return $s;
    }

    protected function getDefaultAssign()
    {
        $s = '';
        $ref = $this->getRef();

        if ($ref->isDefaultValueAvailable()) {
            if ($ref->isDefaultValueConstant()) {
                $s .= ' = ' . $ref->getDefaultValueConstantName();
            } else {
                $s .= ' = ' . var_export($ref->getDefaultValue(), true);
            }
        } else if (method_exists($ref, 'isVariadic') && $ref->isVariadic()) {
            // 可变参数, 没默认值
        } else if ($ref->isOptional()) {
            $s .= ' = null';
        }

        return $s;
    }
}
