<?php
/**
 * ide-helper 的 web 入口.
 *
 * 功能与 bin/code.php 完全一致, 只是把命令行选项换成了 GET 参数:
 *
 *   bin: php bin/code.php -eswow -cSwow\Coroutine -o out.php
 *   web: /code.php?e=swow&c=Swow\Coroutine&o=out.php
 *
 * 参数(与命令行选项一一对应):
 *   p, pre[*]    预包含的文件(相当于 -p)     仅允许本机访问
 *   f[*]         函数名(相当于 -f)
 *   c[*]         类名(相当于 -c), 也支持接口/trait/枚举
 *   e[*]         扩展名(相当于 -e)
 *   r            类名正则(相当于 -r)
 *   o, filename  下载的文件名(相当于 -o), 只是以附件形式返回, 不会写服务器文件
 *   download     1 强制以附件下载
 *   format       text(默认) | json
 *   h, help      显示帮助
 *
 * [*] 支持两种写法: 重复传参 `c[]=A&c[]=B`, 或逗号分隔 `c=A,B`
 *
 * 示例:
 *   /code.php?e=Core
 *   /code.php?c=ArrayObject&c=ArrayIterator&o=array.php
 *   /code.php?r=^Redis&format=json
 */

use Wqy\IdeHelper\Autoloader;
use Wqy\IdeHelper\FunctionCode;
use Wqy\IdeHelper\ClassCode;
use Wqy\IdeHelper\ExtensionCode;

require dirname(__DIR__) . '/src/Autoloader.php';

error_reporting(E_ALL & ~E_DEPRECATED);

Autoloader::register();

@set_time_limit(0);

/**
 * 取多值参数
 *
 * @param string $name
 * @param boolean $split 是否按逗号拆分
 * @return string[]
 */
function ide_helper_get_multi($name, $split = true)
{
    if (! isset($_GET[$name])) {
        return [];
    }

    $raw = $_GET[$name];
    $raw = is_array($raw) ? $raw : [$raw];

    $rt = [];
    foreach ($raw as $one) {
        if (! is_string($one)) {
            continue;
        }
        foreach ($split ? explode(',', $one) : [$one] as $item) {
            $item = trim($item);
            if ($item !== '') {
                $rt[] = $item;
            }
        }
    }

    return array_values(array_unique($rt));
}

/**
 * 取单值参数
 *
 * @param string $name
 * @param string|null $default
 * @return string|null
 */
function ide_helper_get_one($name, $default = null)
{
    if (! isset($_GET[$name]) || ! is_string($_GET[$name])) {
        return $default;
    }

    $v = trim($_GET[$name]);

    return $v === '' ? $default : $v;
}

/**
 * 预包含文件会执行任意 php 代码, 只允许本机使用
 *
 * @return boolean
 */
function ide_helper_is_local_request()
{
    if (PHP_SAPI === 'cli' || PHP_SAPI === 'cli-server') {
        return true;
    }

    $addr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

    return in_array($addr, ['127.0.0.1', '::1'], true);
}

function ide_helper_usage()
{
    $ver = PHP_VERSION;

    return <<<TXT
ide-helper web 入口 (php $ver)

用法: code.php?参数=值

  p / pre[*]     预包含的文件(相当于 -p), 仅允许本机访问
  f[*]           函数名(相当于 -f)
  c[*]           类名(相当于 -c), 支持类/接口/trait/枚举
  e[*]           扩展名(相当于 -e)
  r              类名正则(相当于 -r), 内部分隔符为 #, 可自带
  o / filename   返回的文件名(相当于 -o), 以附件下载, 不写服务器文件
  download=1     强制以附件下载
  format         text(默认) | json
  h / help       显示本帮助

[*] 支持重复传参 c[]=A&c[]=B 或者逗号分隔 c=A,B

示例:
  code.php?e=Core
  code.php?c=ArrayObject&c=ArrayIterator&o=array.php
  code.php?r=^Redis&format=json

TXT;
}

/**
 * 输出
 *
 * @param string $body
 * @param string $contentType
 * @param string|null $filename
 */
function ide_helper_respond($body, $contentType, $filename = null)
{
    if (! headers_sent()) {
        header('Content-Type: ' . $contentType . '; charset=utf-8');
        header('Cache-Control: no-store');
        if ($filename) {
            header('Content-Disposition: attachment; filename="' . str_replace('"', '', $filename) . '"');
        }
    }

    echo $body;
}

$format = strtolower((string) ide_helper_get_one('format', 'text'));

if (isset($_GET['h']) || isset($_GET['help'])) {
    ide_helper_respond(ide_helper_usage(), 'text/plain');
    exit;
}

$preFiles = ide_helper_get_multi('p');
if (! $preFiles) {
    $preFiles = ide_helper_get_multi('pre');
}
$functions = ide_helper_get_multi('f');
$classes = ide_helper_get_multi('c');
$extensions = ide_helper_get_multi('e');
$regex = ide_helper_get_one('r');

if (! $preFiles && ! $functions && ! $classes && ! $extensions && ! $regex) {
    ide_helper_respond(ide_helper_usage(), 'text/plain');
    exit;
}

$errors = [];

if ($preFiles) {
    if (! ide_helper_is_local_request()) {
        $errors[] = '预包含文件(p)只允许本机访问, 已忽略';
    }
    else {
        $root = dirname(__DIR__);

        foreach ($preFiles as $pi) {
            // 相对路径按项目根目录解析
            if (! is_file($pi) && is_file($root . '/' . $pi)) {
                $pi = $root . '/' . $pi;
            }

            if (! is_file($pi)) {
                $errors[] = "文件 $pi 不存在";
                continue;
            }

            include $pi;
        }
    }
}

$codes = [];
$exts = [];

foreach ($functions as $one) {
    if (! function_exists($one)) {
        $errors[] = "函数 $one 不存在";
        continue;
    }
    $codes[] = new FunctionCode(new ReflectionFunction($one));
}

foreach ($classes as $one) {
    if (! class_exists($one) && ! interface_exists($one) && ! trait_exists($one)
        && ! (function_exists('enum_exists') && enum_exists($one))) {
        $errors[] = "类 $one 不存在";
        continue;
    }
    $codes[] = new ClassCode(new ReflectionClass($one));
}

foreach ($extensions as $one) {
    if (! extension_loaded($one)) {
        $errors[] = "扩展 $one 未加载";
        continue;
    }
    $e = new ReflectionExtension($one);
    $codes[] = new ExtensionCode($e);
    $exts[] = sprintf('%s_%s', $e->getName(), $e->getVersion());
}

if (! is_null($regex)) {
    $pattern = $regex;
    if ($pattern[0] !== '#') {
        $pattern = '#' . $pattern . '#';
    }

    if (@preg_match($pattern, '') === false) {
        $errors[] = "正则 $regex 无效";
    }
    else {
        $allClasses = array_merge(get_declared_classes(), get_declared_interfaces(), get_declared_traits());
        $matched = [];

        foreach ($allClasses as $one) {
            if (preg_match($pattern, $one)) {
                $matched[$one] = true;
            }
        }

        foreach (array_keys($matched) as $one) {
            $codes[] = new ClassCode(new ReflectionClass($one));
        }

        if (! $matched) {
            $errors[] = "正则 $regex 没有匹配到类";
        }
    }
}

$codeStr = "<?php\n\n";

if ($errors) {
    $codeStr .= "// [ide-helper]\n// " . implode("\n// ", $errors) . "\n\n";
}

$codeStr .= implode("\n", array_map(function ($one) {
    return $one->toCode([]);
}, $codes));

// 文件名: 有 o/filename 就用它, 否则与 bin/code.php 的默认命名规则一致
$download = ide_helper_get_one('download', '');
$download = ! in_array(strtolower($download), ['', '0', 'false', 'no'], true);

$filename = ide_helper_get_one('o', ide_helper_get_one('filename'));

if (is_null($filename) && ($download || $exts)) {
    $filename = '_ide_helper_php_' . PHP_VERSION
        . ($exts ? '_' . implode('_', array_unique($exts)) : '')
        . '.php';
}

// 没有明确要求下载时, 直接输出文本
if (! $download && ! ide_helper_get_one('o') && ! ide_helper_get_one('filename')) {
    $filename = null;
}

if ($format === 'json') {
    $body = json_encode([
        'php_version' => PHP_VERSION,
        'count' => count($codes),
        'extensions' => array_values(array_unique($exts)),
        'errors' => $errors,
        'filename' => $filename,
        'code' => $codeStr,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    ide_helper_respond($body, 'application/json');
    exit;
}

ide_helper_respond($codeStr, 'text/plain', $filename);
