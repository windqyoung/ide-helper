<?php
namespace Wqy\IdeHelper;


class Autoloader
{
    public static function register()
    {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    public static function autoload($cls)
    {
        if (strpos($cls, 'Wqy\IdeHelper\\') === 0) {
            $base = substr($cls, 14);
            $file = __DIR__ . '/' . $base . '.php';
            if (is_file($file)) {
                require $file;
            }
        }
    }
}