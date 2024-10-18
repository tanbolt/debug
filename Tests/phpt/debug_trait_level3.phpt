--TEST--
Test rethrowing in custom exception handler
--INI--
display_errors=0
--FILE--
<?php
$debug = require __DIR__.'/debug_trait.php';
$debug->setDebugLevel(3);
$debug->mockWarning();
$debug->mockNotice();
$debug->mockError();
$debug->output();
?>
--EXPECTREGEX--
Undefined variable.*noneExistVar