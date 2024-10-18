<?php
namespace Tanbolt\Debug;

use Throwable;
use Exception;
use ErrorException;

/**
 * Trait DebugTrait: 根据 debug 自动 handle 程序异常
 * > 只需在框架核心类中引入该 trait, 并调用 setDebugLevel 便可自动 handler 程序异常
 * @package Tanbolt\Debug
 */
trait DebugTrait
{
    /**
     * 调试等级 (-1|0|1|2|3|4)
     * @see setDebugLevel
     * @var int
     */
    protected $debugLevel = -1;

    /**
     * true:  使用 debug 实例处理异常
     * false: 使用原生 php 的方式输出错误
     * null:  还未实例化
     * @var bool
     */
    protected $debugHandled = null;

    /**
     * 框架载入前 display_errors 设置
     * @var int
     */
    private $display_errors = null;

    /**
     * 框架载入前 error_reporting 等级
     * @var int
     */
    private $error_reporting = null;

    /**
     * 当前的 debug 实例
     * @var DebugInterface|null
     */
    protected $debugInstance = null;

    /**
     * 错误等级
     * @var array
     */
    protected static $errorLevels = [
        E_DEPRECATED        =>  [3, 'Deprecated: '],
        E_USER_DEPRECATED   =>  [3, 'User Deprecated: '],

        E_NOTICE		    =>	[2, 'Notice: '],
        E_USER_NOTICE		=>	[2, 'User Notice: '],
        E_WARNING		    =>	[2, 'Warning: '],
        E_USER_WARNING	    =>	[2, 'User Warning: '],
        E_CORE_WARNING		=>	[2, 'Core Warning: '],
        E_COMPILE_WARNING   =>	[2, 'Compile Warning: '],
        E_STRICT		    =>	[2, 'Runtime Notice: '],

        E_PARSE			    =>	[1, 'Parsing Error: '],
        E_ERROR			    =>	[1, 'Fatal error: '],
        E_USER_ERROR	    =>	[1, 'User Error: '],
        E_CORE_ERROR		=>	[1, 'Core Error: '],
        E_COMPILE_ERROR		=>	[1, 'Compile Error: '],
        E_RECOVERABLE_ERROR =>  [1, 'Catchable Fatal Error: '],
    ];

    /**
     * 获取 Debug 实例对象
     * @return DebugInterface
     */
    protected function debug()
    {
        if (!$this->debugInstance) {
            $this->debugInstance = new Debug();
        }
        return $this->debugInstance;
    }

    /**
     * 设置 debug 等级
     * - -1: 关闭调试, 不插手错误处理, debug 根据 php.ini 设置原生处理
     * - 0: 不输出错误信息, 发生 Error 显示 500 错误, 用于线上生产环境(可通过日志记录错误信息)
     * - 1: 输出错误信息, 获取到第一个 Error/Warning/Notice(致命或非致命错误) 就立即中断并显示错误
     * - 2: 显示 Error (致命错误以上 / 影响程序正常运行), 如语法错误, Exception 等
     * - 3: 显示 Warning (警告信息以上 / 不影响程序运行,但有潜在危险), 如变量未定义,读取不存在的文件 等
     * - 4: 显示 Notice (提示信息以上 / 不影响程序运行,但建议修复), 如使用了即将弃用的函数
     * @param int $level
     * @return $this
     */
    public function setDebugLevel(int $level)
    {
        if ($level === $this->debugLevel) {
            return $this;
        }
        if (null === $this->debugHandled) {
            $this->debugHandled = false;
            $this->display_errors = ini_get('display_errors');
            $this->error_reporting = error_reporting();
        }
        $this->debugLevel = $level;
        if ($this->debugHandled && $this->debugLevel < 0) {
            error_reporting($this->error_reporting);
            ini_set('display_errors', $this->display_errors);
            restore_error_handler();
            restore_exception_handler();
            $this->debugHandled = false;
            return $this;
        }
        if ($this->debugHandled) {
            return $this;
        }
        error_reporting(-1);
        ini_set('display_errors', 'Off');
        register_shutdown_function(function() {
            $this->handleShutdown();
        });
        set_exception_handler(function($e) {
            $this->handleException($e);
        });
        set_error_handler(function($code, $message, $file, $line) {
            $this->handleError($code, $message, $file, $line);
        });
        $this->debugHandled = true;
        return $this;
    }

    /**
     * 获取当前设置的 debug 等级
     * @return int
     */
    public function getDebugLevel()
    {
        return $this->debugLevel;
    }

    /**
     * php中止运行的回调
     * @return $this
     */
    protected function handleShutdown()
    {
        if (is_array($e = error_get_last())) {
            $this->fixErrorLevel($e['type'], $e['message']);
            $this->handleException(new ErrorException($e['message'], 0, $e['type'], $e['file'], $e['line']));
        }
        return $this;
    }

    /**
     * 处理 PHP 错误的回调
     * @param int|string $code
     * @param string $message
     * @param string $file
     * @param int $line
     * @return $this
     * @throws ErrorException
     */
    protected function handleError($code, string $message = '', string $file = '', int $line = 0)
    {
        $debugLevel = $this->getDebugLevel();
        $this->fixErrorLevel($code, $message);
        $e = new ErrorException($message, 0, $code, $file, $line);
        if (1 === $debugLevel) {
            throw $e;
        } else {
            $this->handleException($e, true);
        }
        return $this;
    }

    /**
     * 抛出异常的回调
     * @param Throwable $e
     * @param bool $warning
     * @return $this
     */
    protected function handleException(Throwable $e, bool $warning = false)
    {
        return $this->catchDebug(
            ($e = $this->preparedException($e)),
            $warning ? $this->fixErrorLevel($e->getSeverity()) : 1
        );
    }

    /**
     * 格式化 throwable 为 exception
     * @param Throwable $e
     * @return Exception
     */
    protected function preparedException(Throwable $e)
    {
        return !$e instanceof Exception ? new ThrowableException($e) : $e;
    }

    /**
     * 根据错误等级 优化错误 message
     * @param int|string $code
     * @param ?string $message
     * @return int
     */
    protected function fixErrorLevel($code, string &$message = null)
    {
        if (isset(static::$errorLevels[$code])) {
            $level = static::$errorLevels[$code][0];
            $prefix = static::$errorLevels[$code][1];
        } else {
            $level = 1;
            $prefix = 'Fatal Error: ';
        }
        if ($message) {
            $message = $prefix . $message;
        }
        return $level;
    }

    /**
     * 在 php 中止时输出 debug 信息
     * @param Throwable $e
     * @param int $level
     * @return $this
     */
    protected function catchDebug(Throwable $e, int $level)
    {
        $e = $this->handleDebug($e, $level);
        $debugLevel = $this->getDebugLevel();
        // 根据 debug 设置缓存错误信息, 而不是直接抛出
        if (1 === $debugLevel || $debugLevel > $level) {
            1 === $level ? $this->debug()->setError($e) : $this->debug()->addWarning($e);
        }
        // 遇到致命错误, 此时正常的 php 运行已被打断, 就直接 一次性输出所有(错误/警告)信息
        if (1 === $level) {
            return $this->outputDebug(
                $this->debugInstance && $this->debugInstance->getError()
                    ? $this->debug()->content()
                    : 'Internal Server Error'
            );
        }
        return $this;
    }

    /**
     * 异常信息的回调接口。
     * 可再次对 $e 格式化, 或修改 $level, 或将异常记录到日志
     * @param Throwable $e
     * @param $level
     * @return Throwable
     */
    protected function handleDebug(Throwable $e, &$level)
    {
        return $e;
    }

    /**
     * 获取 Debug 信息。
     * > 若未抛出致命异常，无法自动 handle 并输出 debug 调试页面，在生产环境一般也没什么问题，
     * 只需通过异常信息的回调接口记录 warn 到日志即可，但在开发环境可能想要及时获取这些 warn 并输出，
     * 就可以在输出正常内容前，可通过该函数获取异常内容，通过 outputDebug 输出获取到的 warn，如:
     *     if (null !== ($error = $this->getError())) {
     *         $this->outputDebug($error)
     *     } else {
     *         echo 'Hello World';
     *     }
     * @return ?string
     */
    protected function renderDebug()
    {
        $debug = $this->debugInstance;
        return $debug && $debug->getWarnings() ? $debug->content() : null;
    }

    /**
     * 输出 debug 信息
     * @param string $content
     * @return $this
     */
    protected function outputDebug(string $content)
    {
        if ('cli' != php_sapi_name()) {
            header(sprintf('HTTP/%s %s %s', 1.1, 500, 'Internal Server Error'), true, 500);
        }
        echo $content;
        return $this;
    }
}
