<?php


use Wqy\IdeHelper\FunctionCode;
use Wqy\IdeHelper\ClassCode;
use Wqy\IdeHelper\ExtensionCode;

use Wqy\IdeHelper\Autoloader;

require __DIR__ . '/../src/Autoloader.php';

error_reporting(E_ALL & ~E_DEPRECATED);

Autoloader::register();

$opts = getopt('p::f::c::e::o::h');


if (empty($opts) || isset($opts['h'])) {
    echo <<<HTML
Usage: php code.php [options ...]
    -p<pre_include>    pre include files
    -f<function>       save function
    -c<class>          save class
    -e<extension>      save extension's classes, interfaces, const,
                            functions, ...
    -o<outputfile>     save code to this file
    -h                  show this help

HTML;

    exit;
}

if (! empty($opts['p'])) {
    foreach ((array)$opts['p'] as $pi) {
        if (is_file($pi)) {
            include $pi;
        } else {
            fwrite(STDERR, "file $pi not exits\n");
            exit;
        }
    }
}

$codes = [];

if (! empty($opts['f'])) {
    foreach ((array) $opts['f'] as $one) {
        $codes[] = new FunctionCode(new ReflectionFunction($one));
    }
}

if (! empty($opts['c'])) {
    foreach ((array) $opts['c'] as $one) {
        $codes[] = new ClassCode(new ReflectionClass($one));
    }
}

if (! empty($opts['e'])) {
    $exts = [];
    foreach ((array) $opts['e'] as $one) {
        $codes[] = new ExtensionCode($e = new ReflectionExtension($one));
        $exts[] = sprintf("%s_%s", $e->getName(), $e->getVersion());
    }
}

$codeStr = "<?php\n\n" . implode("\n", array_map(function ($one) {
    return $one->toCode([]);
}, $codes));

if (empty($opts['o'])) {
    if (empty($exts)) {
        $file = 'php://output';
    }
    else {
        $file = '_ide_helper_php_' . PHP_VERSION . '_' . implode('_', $exts) . '.php';
    }
}
else {
    $file = $opts['o'];
}

file_put_contents($file, $codeStr);

// 文件名输出到2号fd
fwrite(STDERR, $file . "\n");

