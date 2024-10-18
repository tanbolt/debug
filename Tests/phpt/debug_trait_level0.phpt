--TEST--
Test rethrowing in custom exception handler
--INI--
display_errors=0
--FILE--
<?php
$debug = require __DIR__.'/debug_trait.php';
$debug->setDebugLevel(0);
$debug->mockWarning();
$debug->mockNotice();
$debug->mockError();
$debug->output();
?>
--EXPECTF--
Internal Server Error