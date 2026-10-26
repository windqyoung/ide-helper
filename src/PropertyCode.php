<?php


namespace Wqy\IdeHelper;

use Reflection;

/**
 * @method \ReflectionProperty getRef()
 * @author windq
 *
 */
class PropertyCode extends CodeBase implements ToCodeInterface
{
    /**
     * 只存在于反射里, 不是 php 代码中的修饰符
     * virtual 表示虚拟属性(php8.4 属性钩子, 没有实际的存储)
     * @var string[]
     */
    private const IGNORE_MODIFIERS = ['virtual'];

    public function toCode()
    {
        if (! $this->declaringInSameClass()) {
            return '';
        }

        // php8.4 属性钩子
        $hooks = $this->getHooksString();

        return $this->getDocComment()
            . $this->getAttributesString()
            . $this->getPrefixSpaces()
            . $this->getModifier()
            . $this->getPropertyTypeString()
            . '$' . $this->getRef()->getName()
            . $this->getDefaultAssign()
            . $hooks
            // 带钩子的属性后面没有分号
            . ($hooks === '' ? ";\n" : "\n");
    }

    protected function getModifier()
    {
        $ref = $this->getRef();

        if (! method_exists($ref, 'getModifiers') || ! ($mods = $ref->getModifiers())) {
            return '';
        }

        $names = Reflection::getModifierNames($mods);

        $isInterface = false;
        if (method_exists($ref, 'getDeclaringClass')) {
            $cls = $ref->getDeclaringClass();
            $isInterface = $cls && $cls->isInterface();
        }

        $names = array_filter($names, function ($one) use ($isInterface) {
            $one = strtolower($one);

            if (in_array($one, self::IGNORE_MODIFIERS)) {
                return false;
            }
            // 接口里的成员都是隐式的 abstract, 不能显式写 abstract
            if ($isInterface && $one === 'abstract') {
                return false;
            }

            return true;
        });

        return $names ? implode(' ', $names) . ' ' : '';
    }

    protected function getDefaultAssign()
    {
        $ref = $this->getRef();

        // readonly 属性(php8.1) 不允许有默认值
        if (method_exists($ref, 'isReadOnly') && $ref->isReadOnly()) {
            return '';
        }

        // 虚拟属性(php8.4) 没有存储, 不能有默认值
        if (method_exists($ref, 'isVirtual') && $ref->isVirtual()) {
            return '';
        }

        return parent::getDefaultAssign();
    }

    /**
     * php8.4 属性钩子
     *
     * public string $name {
     *     get {
     *     }
     *     set {
     *     }
     * }
     *
     * @return string
     */
    private function getHooksString()
    {
        $ref = $this->getRef();

        if (! method_exists($ref, 'hasHooks') || ! $ref->hasHooks()) {
            return '';
        }

        $hooks = $ref->getHooks();

        if (! $hooks) {
            return '';
        }

        $pre = $this->getPrefixSpaces();
        $inner = $this->getPrefixSpaces($this->getLevel() + 1);

        $s = " {\n";

        foreach ($hooks as $kind => $hook) {
            $s .= $inner . $kind;

            // 抽象属性(接口/抽象类里的属性)的钩子没有方法体
            if (method_exists($hook, 'isAbstract') && $hook->isAbstract()) {
                $s .= ";\n";
                continue;
            }

            $s .= "\n" . $inner . "{\n" . $inner . "}\n";
        }

        return $s . $pre . '}';
    }

    private function getPropertyTypeString()
    {
        $ref = $this->getRef();

        if (! method_exists($ref, 'getType')) {
            return '';
        }

        $type = $ref->getType();

        if (! $type) {
            return '';
        }

        $code = new TypeCode($type, $this->getOptions());

        // php8.4 起废弃隐式可空写法: `public int $x = null` 必须写成 `?int`
        if (! $type->allowsNull()
            && method_exists($ref, 'hasDefaultValue') && $ref->hasDefaultValue()
            && is_null($ref->getDefaultValue())) {
            $code->setForceNullable();
        }

        return $code->toCode();
    }

    public function getDocCommentSign($pre, $withAtProperty = true, $withAtSee = true)
    {
        $name = $this->getRef()->getName();
        $sign = $this->getPropertyTypeString() . '$' . $name;

        if (! $withAtProperty) {
            return $sign;
        }

        $rt = "$pre * @property $sign\n";
        if ($withAtSee) {
            $rt .= sprintf("$pre * @see \\%s::\$%s\n", $this->getRef()->getDeclaringClass()->getName(), $name);
        }
        return $rt;
    }
}
