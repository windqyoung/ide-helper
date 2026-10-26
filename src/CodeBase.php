<?php


namespace Wqy\IdeHelper;

use Reflection;

class CodeBase
{
    /**
     * @var \ReflectionClass|\ReflectionMethod|\ReflectionProperty|\ReflectionFunction|\ReflectionParameter|\ReflectionExtension|\ReflectionAttribute|\ReflectionAttribute[]|\ReflectionParameter[]|\ReflectionEnum
     */
    private $ref;

    /**
     * 每一级的缩进
     * @var string
     */
    private $prefixSpace = '    ';

    /**
     * 缩进等级
     * @var integer
     */
    private $level = 1;

    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @var string
     */
    private $declaringClass;

    /**
     * @var boolean
     */
    private $wrapWithNamespace = true;

    public function __construct($ref, $options = [])
    {
        $this->ref = $ref;

        $this->setLevel($options);
        $this->setDefaultValue($options);
        $this->setDeclaringClass($options);
    }

    /**
     * @return boolean
     */
    public function isWrapWithNamespace()
    {
        return $this->wrapWithNamespace;
    }

    /**
     * @param boolean $wrapWithNamespace
     */
    public function setWrapWithNamespace($wrapWithNamespace)
    {
        $this->wrapWithNamespace = $wrapWithNamespace;
    }

    public function setLevel($levelOrOptions, $default = 1)
    {
        if (is_int($levelOrOptions)) {
            $level = $levelOrOptions;
        }
        else if (isset($levelOrOptions['level'])) {
            $level = $levelOrOptions['level'];
        }
        else {
            $level = $default;
        }

        return $this->level = $level;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function getPrefixSpaces($level = null)
    {
        if (is_null($level)) {
            $level = $this->getLevel();
        }
        return str_repeat($this->prefixSpace, $level);
    }

    protected function getModifier()
    {
        if (method_exists($this->ref, 'getModifiers') && ($mods = $this->ref->getModifiers())) {
            return implode(' ', Reflection::getModifierNames($mods)) . ' ';
        }

        return '';
    }

    public function getRef()
    {
        return $this->ref;
    }

    public function setDefaultValue($options, $raw = false)
    {
        if ($raw) {
            $this->defaultValue = $options;
        }
        else if (isset($options['defaultValue'])) {
            $this->defaultValue = $options['defaultValue'];
        }
    }

    protected function getDefaultAssign()
    {
        $ref = $this->getRef();

        if (method_exists($ref, 'hasDefaultValue')) {
            // php 8.3+: 没有默认值时调用 getDefaultValue 会抛弃用提示
            if (! $ref->hasDefaultValue()) {
                return '';
            }
        }

        if (method_exists($ref, 'getDefaultValue')) {
            $def = $ref->getDefaultValue();
        }
        else {
            $def = $this->defaultValue;
        }

        // 如果是null, 不赋值
        if (is_null($def)) {
            return '';
        }

        $export = $this->exportValue($def);

        // 无法还原的默认值(比如 new Foo()), 不赋值
        return is_null($export) ? '' : (' = ' . $export);
    }

    /**
     * 把值导出成 php 代码.
     *
     * 相比 var_export, 这里额外处理了:
     * - 枚举(php 8.1): 导出为 `\Foo::BAR`
     * - 对象(php 8.1 `new` 初始化语法): var_export 会生成非法的 `Foo::__set_state()`,
     *   这种情况返回 null, 由调用方决定如何降级.
     *
     * @param mixed $value
     * @return string|null null 表示无法还原
     */
    public function exportValue($value)
    {
        if (is_array($value)) {
            $items = [];
            $index = 0;

            foreach ($value as $k => $v) {
                $one = $this->exportValue($v);
                if (is_null($one)) {
                    return null;
                }
                // 连续的数字键可以省略
                $items[] = $k === $index++ ? $one : var_export($k, true) . ' => ' . $one;
            }

            return '[' . implode(', ', $items) . ']';
        }

        if (is_object($value)) {
            // 枚举可以用 `\Foo::BAR` 还原
            if ($value instanceof \UnitEnum) {
                return '\\' . get_class($value) . '::' . $value->name;
            }
            return null;
        }

        return var_export($value, true);
    }

    public function getDocComment()
    {
        if (! method_exists($this->ref, 'getDocComment')) {
            return '';
        }

        $cmt = $this->ref->getDocComment();

        if (empty($cmt)) {
            return '';
        }

        $prefix = $this->getPrefixSpaces();

        $cmtFmt = implode("\n", array_map(function ($one) use ($prefix) {
            $l = ltrim($one);
            if ($l[0] == '/') {
                return $prefix . $l;
            }
            else if ($l[0] == '*') {
                return $prefix . ' ' . $l;
            }

            return $one;
        }, explode("\n", $cmt)));

        return $cmtFmt;
    }

    public function wrapNamespace($code)
    {
        return 'namespace ' . $this->getNamespaceName() . " {\n\n"
            . $code
            . "\n}\n";
    }

    public function getNamespaceName()
    {
        return $this->ref && method_exists($this->ref, 'getNamespaceName') ? $this->ref->getNamespaceName() : '';
    }

    /**
     * @param string $declaringClass
     */
    public function setDeclaringClass($options, $raw= false)
    {
        if ($raw) {
            $this->declaringClass = $options;
        }
        else if (isset($options['declaringClass'])) {
            $this->declaringClass = $options['declaringClass'];
        }
    }

    public function declaringInSameClass()
    {
        return $this->declaringClass == $this->getRef()->getDeclaringClass()->getName();
    }

    public function getShortName()
    {
        $name = $this->ref->getShortName();

        if (preg_match('/^\d/', $name)) {
            $name = '___DIGIT_' . $name;
        }

        // 匿名类的短名形如 `file.php:1$3`, 属于非法标识符, 替换掉
        $name = preg_replace('/[^a-zA-Z0-9_\x80-\xff]/', '_', $name);

        return $name;
    }

    /**
     * @param \ReflectionType $type
     * @return string
     */
    public function getTypeString($type)
    {
        return (new TypeCode($type, $this->getOptions()))->toCode();
    }

    public function getOptions()
    {
        return [
            'level' => $this->getLevel(),
        ];
    }

    public function hasAttributes()
    {
        return method_exists($this->ref, 'getAttributes') && $this->ref->getAttributes();
    }

    public function getAttributesString()
    {
        if (! method_exists($this->ref, 'getAttributes')) {
            return '';
        }

        return (new AttributeArrayCode($this->ref->getAttributes(), $this->getOptions()))->toCode();
    }

    public function isStatic()
    {
        return method_exists($this->getRef(), 'isStatic') && $this->getRef()->isStatic();
    }
}
