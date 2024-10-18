<?php
namespace Tanbolt\Debug;

use Throwable;

/**
 * Interface DebugInterface
 * @package Tanbolt\Debug
 */
interface DebugInterface
{
    /**
     * 添加一个异常，可以是致命 error，也可以是非致命 warning
     * @param Throwable $e
     * @return static
     */
    public function addException(Throwable $e);

    /**
     * 添加一条非致命异常的调试信息，可直接添加 Throwable 对象，
     * 或通过 message/file/line 添加，此时 $exception 设置为 errorCode
     * @param Throwable|int $exception
     * @param ?string $message
     * @param ?string $file
     * @param int $line
     * @return static
     */
    public function addWarning($exception, string $message = null, string $file = null, int $line = 0);

    /**
     * 获取所有已经添加的非致命异常
     * @return Throwable[]
     */
    public function getWarnings();

    /**
     * 清除所有已经添加非致命异常
     * @return static
     */
    public function clearWarning();

    /**
     * 设置致命异常对象
     * @param Throwable $e
     * @return static
     */
    public function setError(Throwable $e);

    /**
     * 获取致命异常对象
     * @return ?Throwable
     */
    public function getError();

    /**
     * 清除致命异常对象
     * @return static
     */
    public function clearError();

    /**
     * 设置HTML调试输出模版
     * @param string $template
     * @return static
     */
    public function setTemplate(string $template);

    /**
     * 设置控制台调试输出模板
     * @param string $template
     * @return static
     */
    public function setConsoleTemplate(string $template);

    /**
     * 强制设置输出模式
     * - true:控制台模式
     * - false:HTML模式
     * - null:自动判断
     * @param ?bool $mode
     * @return static
     */
    public function setConsoleMode(?bool $mode = true);

    /**
     * 获取当前的调试页面模版
     * @param bool $console
     * @return string
     */
    public function getTemplate(bool $console = false);

    /**
     * 根据当前设定的模版获取 debug 输出信息
     * @return string
     */
    public function content();
}
