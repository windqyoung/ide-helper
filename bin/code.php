<?php


use Wqy\IdeHelper\FunctionCode;
use Wqy\IdeHelper\ClassCode;
use Wqy\IdeHelper\ExtensionCode;

$au = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php',
];


foreach ($au as $one) {
    if (is_file($one)) {
        $loader = require $one;
    }
}

if (! isset($loader)) {
    exit("autoload.php do not exists.\n");
}

$opts = getopt('f::c::e::o::h');

if (isset($opts['h'])) {
    echo <<<HTML
php code.php -ffunction -cclass -eextension -ooutputfile -hhelp
HTML;

    exit;
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
    foreach ((array) $opts['e'] as $one) {
        $codes[] = new ExtensionCode(new ReflectionExtension($one));
    }
}

$codeStr = "<?php\n\n" . implode("\n", array_map(function ($one) {
    return $one->toCode([]);
}, $codes));


$file = empty($opts['o']) ? 'php://output' : $opts['o'];

file_put_contents($file, $codeStr);


