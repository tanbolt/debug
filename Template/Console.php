<?php
use Tanbolt\Debug\Debug;
use Tanbolt\Debug\DebugUtils;

/**
 * 致命异常可能不存在 ( null )
 * @var Debug $debug
 */
$output = '';
if ($e = $debug->getError()) {
    $output .= DebugUtils::formatException($e);
}

$warns = $debug->getWarnings();
if (count($warns)) {
    $output .= "\n" . DebugUtils::formatWarning($warns);
}
echo $output;
