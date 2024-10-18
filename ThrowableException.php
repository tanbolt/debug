<?php
namespace Tanbolt\Debug;

use Throwable;
use TypeError;
use ParseError;
use ErrorException;
use ReflectionProperty;

/**
 * Class ThrowableException: 转 Throwable(Error) 为 Exception(ErrorException)
 * @package Tanbolt\Debug
 */
class ThrowableException extends ErrorException
{
    /**
     * 转换错误抛出类型为  ErrorException
     * @param Throwable $e
     */
    public function __construct(Throwable $e)
    {
        $message = DebugUtils::getClassName($e) . ': ' . $e->getMessage();
        if ($e instanceof ParseError) {
            $severity = E_PARSE;
        } elseif ($e instanceof TypeError) {
            $severity = E_RECOVERABLE_ERROR;
        } else {
            $severity = E_ERROR;
        }
        //getCode 函数并不是总能返回数字 http://php.net/manual/en/throwable.getcode.php
        if (!is_int($code = $e->getCode())) {
            $code = 1;
        }
        parent::__construct($message, $code, $severity, $e->getFile(), $e->getLine());
        $this->setTrace($e->getTrace());
    }

    /**
     * @param $trace
     */
    protected function setTrace($trace)
    {
        $traceReflector = new ReflectionProperty('Exception', 'trace');
        $traceReflector->setAccessible(true);
        $traceReflector->setValue($this, $trace);
    }
}
