# ide-helper
生成扩展里面的类,函数,常量代码, 让ide能自动完成.

# 用法示例
以下为生成的 Swow扩展 `Swow\Coroutine` 类
php bin\code.php -eswow


```php
<?php

namespace Swow {


    class Coroutine
    {

        public const STATE_NONE = 0;


        public const STATE_WAITING = 1;


        public const STATE_RUNNING = 2;


        public const STATE_DEAD = 3;



        public function __construct(callable $callable)
        {
        }


        public static function run(callable $callable, mixed ...$data) : static 
        {

            $rt = null;
            return $rt;
        }


        public function resume(mixed ...$data) : mixed 
        {

            $rt = null;
            return $rt;
        }


        public static function yield(mixed $data = NULL) : mixed 
        {

            $rt = null;
            return $rt;
        }


        public function getId() : int 
        {

            $rt = 1;
            return $rt;
        }


        public static function getCurrent() : \Swow\Coroutine|static 
        {

            $rt = null;
            return $rt;
        }


        public static function getMain() : \Swow\Coroutine|static 
        {

            $rt = null;
            return $rt;
        }


        public function getPrevious() : \Swow\Coroutine|static 
        {

            $rt = $this;
            return $rt;
        }


        public function getState() : int 
        {

            $rt = 1;
            return $rt;
        }


        public function getStateName() : string 
        {

            $rt = "";
            return $rt;
        }


        public function getSwitches() : int 
        {

            $rt = 1;
            return $rt;
        }


        public static function getGlobalSwitches() : int 
        {

            $rt = 1;
            return $rt;
        }


        public function getElapsed() : int 
        {

            $rt = 1;
            return $rt;
        }


        public function getElapsedAsString() : string 
        {

            $rt = "";
            return $rt;
        }


        public function getExitStatus() : int 
        {

            $rt = 1;
            return $rt;
        }


        public function isAvailable() : bool 
        {

            $rt = true;
            return $rt;
        }


        public function isAlive() : bool 
        {

            $rt = true;
            return $rt;
        }


        public function isExecuting() : bool 
        {

            $rt = true;
            return $rt;
        }


        public function getExecutedFilename(int $level = 0) : string 
        {

            $rt = "";
            return $rt;
        }


        public function getExecutedLineno(int $level = 0) : int 
        {

            $rt = 1;
            return $rt;
        }


        public function getExecutedFunctionName(int $level = 0) : string 
        {

            $rt = "";
            return $rt;
        }


        public function getTrace(int $level = 0, int $limit = 0, int $options = \DEBUG_BACKTRACE_PROVIDE_OBJECT) : array 
        {

            $rt = [];
            return $rt;
        }


        public function getTraceAsString(int $level = 0, int $limit = 0, int $options = \DEBUG_BACKTRACE_PROVIDE_OBJECT) : string 
        {

            $rt = "";
            return $rt;
        }


        public function getTraceAsList(int $level = 0, int $limit = 0, int $options = \DEBUG_BACKTRACE_PROVIDE_OBJECT) : array 
        {

            $rt = [];
            return $rt;
        }


        public function getTraceDepth(int $limit = 0) : int 
        {

            $rt = 1;
            return $rt;
        }


        public function getDefinedVars(int $level = 0) : array 
        {

            $rt = [];
            return $rt;
        }


        public function setLocalVar(string $name, mixed $value, int $level = 0, bool $force = true) : static 
        {

            $rt = $this;
            return $rt;
        }


        public function eval(string $string, int $level = 0) : mixed 
        {

            $rt = null;
            return $rt;
        }


        public function call(callable $callable, int $level = 0) : mixed 
        {

            $rt = null;
            return $rt;
        }


        public function throw(\Throwable $throwable) : mixed 
        {

            $rt = null;
            return $rt;
        }


        public function kill() : void 
        {
        }


        public static function killAll() : void 
        {
        }


        public static function count() : int 
        {

            $rt = 1;
            return $rt;
        }


        public static function get(int $id) : ?static 
        {

            $rt = null;
            return $rt;
        }


        public static function getAll() : array 
        {

            $rt = [];
            return $rt;
        }


        public function __debugInfo() : array 
        {

            $rt = [];
            return $rt;
        }


        public static function registerDeadlockHandler(callable $callable) : \Swow\Utils\Handler 
        {

            $rt = null;
            return $rt;
        }
    }

}

```