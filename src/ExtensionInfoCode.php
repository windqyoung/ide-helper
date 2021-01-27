<?php

namespace Wqy\IdeHelper;

class ExtensionInfoCode extends CodeBase implements ToCodeInterface
{
    /**
     * helo*\/world
     */
    public function toCode()
    {
        $ref = $this->getRef();

        ob_start();
        $ref->info();
        $info = ob_get_clean();

        return sprintf("/*\nname: %s, version: %s\n\n%s\n*/\n", $ref->getName(),
            $ref->getVersion(), str_replace('*/', '*\/', $info));
    }


}