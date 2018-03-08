<?php


namespace Wqy\IdeHelper;

class ParameterCode extends CodeBase implements ToCodeInterface
{
    public function toCode($options = [])
    {
        return $this->getType()
            . $this->getName()
            . $this->getDefaultAssign()
        ;
    }

    public function getName()
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

    public function getType()
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

    public function getDefaultAssign($options = [])
    {
        $s = '';
        $ref = $this->getRef();

        if ($ref->isDefaultValueAvailable()) {
            if ($ref->isDefaultValueConstant()) {
                $s .= ' = ' . $ref->getDefaultValueConstantName();
            } else {
                $s .= ' = ' . var_export($ref->getDefaultValue(), true);
            }
        } else if ($ref->allowsNull() && $ref->isOptional()) {
            $s .= ' = null';
        }

        return $s;
    }
}
