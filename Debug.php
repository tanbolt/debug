<?php
namespace Tanbolt\Debug;

use Throwable;
use ErrorException;

/**
 * Class Debug：格式化调试信息类。
 * > 该类的作用是作为收集 致命/非致命 异常的容器，然后通过模版的方式以更为友好的方式展示
 * @package Tanbolt\Debug
 */
class Debug implements DebugInterface
{
    /**
     * 致命错误 Severity 等级
     * @var array
     */
    protected static $errorSeverity = [
        E_PARSE,
        E_ERROR,
        E_USER_ERROR,
        E_CORE_ERROR,
        E_COMPILE_ERROR,
        E_RECOVERABLE_ERROR,
    ];

    /**
     * 警告级别的异常 容器
     * @var Throwable[]
     */
    protected $warning = [];

    /**
     * 最后一个致命错误
     * @var Throwable|null
     */
    protected $error = null;

    /**
     * HTML调试输出模版
     * @var string
     */
    protected $template = null;

    /**
     * 控制台调试输出模板
     * @var string
     */
    protected $consoleTemplate = null;

    /**
     * 当前调试输出模式, null 为自动获取
     * @var bool
     */
    protected $consoleMode = null;

    /**
     * @inheritdoc
     */
    public function addException(Throwable $e)
    {
        $error = in_array(($e instanceof ErrorException ? $e->getSeverity() : E_ERROR), static::$errorSeverity);
        return $error ? $this->setError($e) : $this->appendWarning($e);
    }

    /**
     * @inheritdoc
     */
    public function addWarning($exception, string $message = null, string $file = null, int $line = 0)
    {
        if ($exception instanceof Throwable) {
            $this->appendWarning($exception);
        } else {
            $this->appendWarning(new ErrorException($message, 0, (int) $exception, (string) $file, $line));
        }
        return $this;
    }

    /**
     * 添加一条 warning 调试信息
     * @param Throwable $e
     * @return $this
     */
    protected function appendWarning(Throwable $e)
    {
        $this->warning[] = $e;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getWarnings()
    {
        return $this->warning;
    }

    /**
     * @inheritdoc
     */
    public function clearWarning()
    {
        $this->warning = [];
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setError(Throwable $e)
    {
        $this->error = $e;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @inheritdoc
     */
    public function clearError()
    {
        $this->error = null;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTemplate(string $template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setConsoleTemplate(string $template)
    {
        $this->consoleTemplate = $template;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setConsoleMode(?bool $mode = true)
    {
        $this->consoleMode = $mode;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTemplate(bool $console = false)
    {
        return $console ? $this->consoleTemplate : $this->template;
    }

    /**
     * @inheritdoc
     */
    public function content()
    {
        $console = null === $this->consoleMode ? 'cli' == php_sapi_name() : (bool) $this->consoleMode;
        $template = $this->getTemplate($console);
        if (empty($template)) {
            $template = __DIR__ . DIRECTORY_SEPARATOR . 'Template' . DIRECTORY_SEPARATOR .
                ($console ? 'Console' : 'Browser') . '.php';
            $console ? ($this->consoleTemplate = $template) : ($this->template = $template);
        }
        $content = $this->renderContent($template);
        if ($console) {
            $content = preg_replace('/<!--(.+?)-->/s', '',$content);
            $content = preg_replace('/^(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/', '',$content);
        }
        return $content;
    }

    /**
     * 通过手动指定的模版获得调试输出信息
     * @param string $template
     * @return string
     */
    public function renderContent(string $template)
    {
        ob_start();
        $debug = $this;
        include($template);
        return ob_get_clean();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->content();
    }

    /**
     * 清除已经设置的警告/异常
     * @return $this
     */
    public function __destruct()
    {
        $this->warning = [];
        $this->error = null;
        return $this;
    }
}
