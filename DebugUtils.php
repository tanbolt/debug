<?php
namespace Tanbolt\Debug;

use Throwable;
use ArrayObject;
use SplFileObject;
use DateTimeInterface;
use ReflectionProperty;
use __PHP_Incomplete_Class;

/**
 * Class DebugUtils: Exception 异常格式化, 可在 Debug 模版中使用
 * @package Tanbolt\Debug
 */
class DebugUtils
{
    const T_NULL     = 'Null';
    const T_BOOLEAN  = 'Boolean';
    const T_INT      = 'Int';
    const T_FLOAT    = 'Float';
    const T_STRING   = 'String';
    const T_ARRAY    = 'Array';
    const T_RESOURCE = 'Resource';
    const T_DATETIME   = 'Datetime';
    const T_OBJECT   = 'Object';
    const T_INCOMPLETE_OBJECT = 'Object(__PHP_Incomplete_Class)';

    /**
     * 解析 exception trace 中函数参数 最多分析个数
     * @var int
     */
    protected static $entriesDeep = 20;

    /**
     * exception trace 参数如果为数组, 最多分析项数
     * @var int
     */
    protected static $optionsDeep = 20;

    /**
     * exception trace 参数如果为数组, 最深分析层数
     * @var int
     */
    protected static $arrayDeep = 10;

    /**
     * 获取对象 class 名称，优化一下匿名类的显示名称
     * @param object $object
     * @return string
     */
    public static function getClassName(object $object)
    {
        $class = get_class($object);
        if ('c' === $class[0] && 0 === strpos($class, "class@anonymous\0")) {
            return (get_parent_class($class) ?: 'class').substr($class, 5);
        }
        return $class ?: 'UnknownClass';
    }

    /**
     * 根据 file:line 参数获取对应的代码片段
     * @param string $file
     * @param int $line
     * @param int $lines  提取行数
     * @return array
     */
    public static function code(string $file, int $line, int $lines = 9)
    {
        $texts = [];
        if (is_file($file) && $line) {
            $start = max(1, ($line - floor( $lines / 2) ));
            $end = $start + $lines;
            $fp = new SplFileObject($file, 'rb');
            $fp->seek($start - 1);
            while ($start < $end) {
                if ( !($text = $fp->current()) ) {
                    break;
                }
                $texts[$start] = htmlspecialchars($text);
                $fp->next();
                $start++;
            }
        }
        return $texts;
    }

    /**
     * 返回 方便控制台阅读的 异常信息
     * @param Throwable $e
     * @return string
     */
    public static function formatException(Throwable $e)
    {
        $output = static::getClassName($e) . ':';
        $output .= "\n  ".$e->getMessage();
        $output .= "\n  ".$e->getFile().':'.$e->getLine();
        $traces = $e->getTrace();
        if (count($traces)) {
            $output .= "\n\nStack trace:";
            foreach ($e->getTrace() as $key => $trace) {
                $output .= "\n".'  #'.$key.' ';
                $output .= $trace['class'] ?? '';
                $output .= $trace['type'] ?? '';
                $output .= $trace['function'] ?? '';
                $output .= '()';
                if (isset($trace['file'])) {
                    $output .= ' at '. $trace['file'];
                    if (isset($trace['line'])) {
                        $output .= ':'.$trace['line'];
                    }
                }
            }
        }
        $output .= "\n";
        return $output;
    }

    /**
     * 返回 方便控制台阅读的 警告信息
     * @param Throwable[] $warns
     * @return string
     */
    public static function formatWarning(array $warns = [])
    {
        $output = "PHP Notice:";
        foreach ($warns as $key => $warn) {
            $output .= "\n".'  #'.$key.' ';
            $output .= $warn->getMessage();
            if ($file = $warn->getFile()) {
                $output .= ' at '.$file;
                if ($line = $warn->getLine()) {
                    $output .= ':'.$line;
                }
            }
        }
        $output .= "\n";
        return $output;
    }

    /**
     * 格式化 Exception 的 trace
     * @param Throwable $e
     * @param ?int $entriesDeep   本次生效 : 最多解析参数, null 则使用默认值
     * @param ?int $optionsDeep   本次生效 : 最多分析项数
     * @param ?int $arrayDeep     本次生效 : 数组最深分析级数
     * @return Throwable|null
     */
    public static function format(Throwable $e, int $entriesDeep = null, int $optionsDeep = null, int $arrayDeep = null)
    {
        // 设置本次生效参数
        $defaultEntriesDeep = null;
        if ($entriesDeep !== null) {
            $defaultEntriesDeep = static::$entriesDeep;
            static::$entriesDeep = $entriesDeep;
        }
        $defaultOptionsDeep = null;
        if ($optionsDeep !== null) {
            $defaultOptionsDeep = static::$optionsDeep;
            static::$optionsDeep = $defaultOptionsDeep;
        }
        $defaultArrayDeep = null;
        if ($arrayDeep !== null) {
            $defaultArrayDeep = static::$arrayDeep;
            static::$arrayDeep = $arrayDeep;
        }

        $oldTrace = $e->getTrace();
        $trace = [];
        foreach ($oldTrace as $old) {
            $trace[] = static::formatExceptionTrace($old);
        }
        $traceReflector = new ReflectionProperty('Exception', 'trace');
        $traceReflector->setAccessible(true);
        $traceReflector->setValue($e, $trace);
        // 还原默认设置
        if ($defaultEntriesDeep !== null) {
            static::$entriesDeep = $defaultEntriesDeep;
        }
        if ($defaultOptionsDeep !== null) {
            static::$optionsDeep = $defaultOptionsDeep;
        }
        if ($defaultArrayDeep !== null) {
            static::$arrayDeep = $defaultArrayDeep;
        }
        return $e;
    }

    /**
     * 格式化 exception trace 的一项
     * @param array $arr
     * @return array
     */
    private static function formatExceptionTrace(array $arr)
    {
        $trace = [
            'fileShort' => $arr['file'] ?? null,
            'file'      => $arr['file'] ?? null,
            'line'      => $arr['line'] ?? null,
            'class'     => $arr['class'] ?? null,
            'namespace' => null,
            'classShort'=> null,
            'type'      => $arr['type'] ?? null,
            'function'  => $arr['function'] ?? null,
            'args'      => $arr['args'] ?? null,
            'argsBasic' => null,
            'argsDetail'=> null,
        ];
        if ($trace['fileShort'] && preg_match('#[^/\\\\]*$#', $trace['fileShort'], $file)) {
            $trace['fileShort'] = $file[0];
        }
        if ($trace['class']) {
            $segments = explode('\\', $trace['class']);
            $trace['classShort'] = array_pop($segments);
            $trace['namespace'] = implode('\\', $segments);
        }
        if ($trace['args']) {
            $args = static::preparedArgs($trace['args']);
            $trace['argsBasic'] = $args[0];
            $trace['argsDetail'] = $args[1];
        }
        return $trace;
    }

    /**
     * 处理 trace 中的 args
     * @param array $args
     * @return array
     */
    private static function preparedArgs(array $args)
    {
        $keys = [];
        $value = [];
        foreach ($args as $count=>$arg) {
            if ($count > static::$entriesDeep - 1) {
                $keys[] = '...$args';
                $value[] = 'skipped over '.static::$entriesDeep.'/'.count($args).' entries...';
                break;
            }
            $type = static::getVariableType($arg);
            $keys[] = $type;
            $value[] = static::formatArg($type, $arg);
        }
        return [$keys, $value];
    }

    /**
     * 由变量类型和值 返回用于显示的字符串结果
     * @param string $type
     * @param mixed $value
     * @param int $level
     * @param int $indent
     * @return string
     */
    private static function formatArg(string $type, $value, int $level = 0, int $indent = 0)
    {
        switch ($type) {
            case self::T_NULL:
                return 'NULL';
            case self::T_BOOLEAN:
                return $value ? 'true' : 'false';
            case self::T_INT:
                return (string) $value;
            case self::T_FLOAT:
                return is_infinite($value) ? ($value < 0 ? '-INF' : 'INF') : (is_nan($value) ? 'NaN' : (string) $value);
            case self::T_STRING:
                return $value;
            case self::T_ARRAY:
                return static::arrayToString($value, $level, $indent);
            case self::T_RESOURCE:
                return 'Resource(' . get_resource_type($value) . ')';
            case self::T_DATETIME:
                return 'DateTime(' . $value->format('Y-m-d H:i:s') . ')';
            case self::T_INCOMPLETE_OBJECT:
                return 'Incomplete(' . static::getClassNameFromIncomplete($value) . ')';
            case self::T_OBJECT:
                return 'Object(' . static::getClassName($value) . ')';
        }
        return 'TYPE(' . $type . ')';
    }

    /**
     * 数组转换为可用于显示的字符串
     * @param array $arr
     * @param int $level
     * @param int $indent
     * @return string
     */
    private static function arrayToString(array $arr, int $level = 0, int $indent = 0)
    {
        if ($level > static::$arrayDeep - 1) {
            return '[*DEEP NESTED ARRAY*]';
        }
        if (!count($arr)) {
            return '[]';
        }
        $blank = ' ';
        $prefix = str_repeat($blank, $indent);
        $spaceNum = 4;
        $space = str_repeat($blank, $spaceNum);
        $eol = PHP_EOL;
        $str = '[' . $eol;
        $max = max(array_map('strlen', array_keys($arr)));
        $count = 1;
        foreach ($arr as $key => $value) {
            if ($count++ > static::$optionsDeep) {
                $str .= $prefix . $space . 'skipped over '.static::$optionsDeep .'/'.count($arr).' options...' . $eol;
                break;
            }
            $type = static::getVariableType($value);
            $nextIndent = $indent;
            if (self::T_ARRAY === $type) {
                $nextIndent += $max + $spaceNum + 4;
            }
            $value = static::formatArg($type, $value, ($level + 1), $nextIndent);
            $str .= $prefix . $space . sprintf("%-{$max}s %s %s,$eol", $key, '=>', $value);
        }
        $str .= $prefix . ']';
        return $str;
    }

    /**
     * 获取一个变量的类型
     * @param mixed $value
     * @return string
     */
    private static function getVariableType($value)
    {
        if (null === $value) {
            return self::T_NULL;
        } elseif (is_bool($value)) {
            return self::T_BOOLEAN;
        } elseif (is_int($value)) {
            return self::T_INT;
        } elseif (is_float($value)) {
            return self::T_FLOAT;
        } elseif (is_string($value)) {
            return self::T_STRING;
        } elseif (is_array($value)) {
            return self::T_ARRAY;
        } elseif (is_resource($value)) {
            return self::T_RESOURCE;
        } elseif ($value instanceof DateTimeInterface) {
            return self::T_DATETIME;
        } elseif ($value instanceof __PHP_Incomplete_Class) {
            return self::T_INCOMPLETE_OBJECT;
        } else if (is_object($value)) {
            return self::T_OBJECT;
        }
        return gettype($value);
    }

    /**
     * 获取反序列化失败的 class name
     * @param __PHP_Incomplete_Class $value
     * @return string
     */
    private static function getClassNameFromIncomplete(__PHP_Incomplete_Class $value)
    {
        $array = new ArrayObject($value);
        return $array['__PHP_Incomplete_Class_Name'];
    }
}
