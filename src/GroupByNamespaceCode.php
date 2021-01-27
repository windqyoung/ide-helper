<?php

namespace Wqy\IdeHelper;


/**
 * 把 codes 数组按命名空间进行分组, 然后同一组, 只使用一个 namespace xxx { }
 */
class GroupByNamespaceCode implements ToCodeInterface
{
    /**
     * @var CodeBase[]
     */
    private $codes;

    /**
     * @param CodeBase[] $codes
     */
    public function __construct($codes)
    {
        $this->codes = $codes;
    }

    function toCode()
    {
        // 按命名空间分组
        $grpCodes = $this->groupByNs($this->codes);

        $rt = '';

        foreach ($grpCodes as $ns => $codes) {

            $ns = $ns ? $ns : '/* GLOBAL NAMESPACE */';
            // 在每组前后加上命名空间信息
            $rt .= "\nnamespace $ns {\n\n";

            // 组内的代码不加命名空间
            $rt .= implode("\n\n", array_map(function ($codeOne) {
                /** @var CodeBase $one */
                $one = clone $codeOne;
                $one->setWrapWithNamespace(false);

                return $one->toCode();
            }, $codes));

            $rt .= "\n}\n";
        }

        return $rt;
    }

    /**
     * @param CodeBase[] $codes
     * @return array
     */
    private function groupByNs($codes)
    {
        $grp = [];

        foreach ($codes as $c) {
            $grp[$c->getNamespaceName()][] = $c;
        }

        return $grp;
    }
}
