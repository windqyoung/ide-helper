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

    function toCode($options)
    {
        // 按命名空间分组
        $grpCodes = $this->groupByNs($this->codes);

        $rt = '';

        foreach ($grpCodes as $ns => $codes) {
            // 在每组前后加上命名空间信息
            $rt .= "\nnamespace $ns {\n\n";

            // 组内的代码不加命名空间
            $rt .= implode("\n\n", array_map(function ($one) use ($options) {
                $options['namespace'] = false;
                /** @var CodeBase $one */
                return $one->toCode($options);
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
