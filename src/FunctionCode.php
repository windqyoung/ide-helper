<?php


namespace Wqy\IdeHelper;


class FunctionCode extends AbstractFunctionCode implements ToCodeInterface
{
    /**
     * php 关键字. 关键字不能用作函数名(方法名可以),
     * 比如 php8.5 新增的内部函数 `clone()`, `exit()`.
     * @var string[]
     */
    private const KEYWORDS = [
        'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class',
        'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else',
        'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch',
        'endwhile', 'enum', 'eval', 'exit', 'extends', 'final', 'finally', 'fn', 'for',
        'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include',
        'include_once', 'instanceof', 'insteadof', 'interface', 'isset', 'list', 'match',
        'namespace', 'new', 'or', 'print', 'private', 'protected', 'public', 'readonly',
        'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait', 'try',
        'unset', 'use', 'var', 'while', 'xor', 'yield',
    ];

    public function toCode()
    {
        if ($this->isKeywordName()) {
            // 声明同名函数会直接导致语法错误, 只能跳过
            return '// 函数 ' . $this->getRef()->getName() . "() 与 php 关键字同名, 无法生成\n";
        }

        $code = $this->toFunctionCode();
        $wrap = $this->isWrapWithNamespace();
        return $wrap ? $this->wrapNamespace($code) : $code;
    }

    /**
     * @return boolean
     */
    private function isKeywordName()
    {
        return in_array(strtolower($this->getRef()->getName()), self::KEYWORDS);
    }

}
