<?php

use PHPUnit\Framework\TestCase;
use Tanbolt\Debug\Debug;
use Tanbolt\Debug\Fixtures\DebugTraitPage;

class DebugTest extends TestCase
{
    protected function setUp():void
    {
        PHPUNIT_LOADER::addDir('Tanbolt\Debug\Fixtures', __DIR__.'/Fixtures');
        parent::setUp();
    }

    public function testConstruct()
    {
        $debug = new Debug();
        static::assertInstanceOf('Tanbolt\Debug\DebugInterface', $debug);
    }

    public function testAddException()
    {
        $debug = new Debug();
        $exception = new ErrorException('foo', 0, E_ERROR);
        static::assertSame($debug, $debug->addException($exception));
        static::assertCount(0, $debug->getWarnings());
        static::assertSame($exception, $debug->getError());

        $debug = new Debug();
        $exception = new ErrorException('foo', 0, E_NOTICE);
        static::assertSame($debug, $debug->addException($exception));
        static::assertCount(1, $warns = $debug->getWarnings());
        static::assertSame($exception, $warns[0]);
        static::assertNull($debug->getError());
    }

    public function testAddWarning()
    {
        $debug = new Debug();
        $foo = new ErrorException('foo');
        $bar = new ErrorException('bar');

        static::assertSame($debug, $debug->addWarning($foo));
        static::assertSame($debug, $debug->addWarning($bar));

        $warnings = $debug->getWarnings();
        static::assertCount(2, $warnings);
        static::assertSame($foo, $warnings[0]);
        static::assertEquals('foo', $warnings[0]->getMessage());
        static::assertSame($bar, $warnings[1]);
        static::assertEquals('bar', $warnings[1]->getMessage());

        static::assertSame($debug, $debug->clearWarning());
        static::assertCount(0, $debug->getWarnings());
    }

    public function testSetError()
    {
        $debug = new Debug();
        $error = new ErrorException('error');
        static::assertSame($debug, $debug->setError($error));
        static::assertSame($error, $debug->getError());
        static::assertEquals('error', $error->getMessage());

        static::assertSame($debug, $debug->clearError());
        static::assertNull($debug->getError());
    }

    public function testTemplate()
    {
        $debug = new Debug();
        static::assertNull($debug->getTemplate());
        static::assertNull($debug->getTemplate(true));

        static::assertSame($debug, $debug->setTemplate('template'));
        static::assertEquals('template', $debug->getTemplate());

        static::assertSame($debug, $debug->setConsoleTemplate('console_template'));
        static::assertEquals('console_template', $debug->getTemplate(true));
    }

    public function testContent()
    {
        $debug = new Debug();
        static::assertTrue(is_string($debug->content()));
    }

    public function testSetDebugLevel()
    {
        $reporting = E_ERROR | E_WARNING | E_PARSE | E_NOTICE;
        error_reporting($reporting);
        ini_set('display_errors', 'On');

        static::assertEquals($reporting, error_reporting());
        static::assertTrue(filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN));

        $debug = new DebugTraitPage();
        $debug->setDebugLevel(3);

        static::assertEquals(-1, error_reporting());
        static::assertFalse(filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN));

        $debug->setDebugLevel(-1);
        static::assertEquals($reporting, error_reporting());
        static::assertTrue(filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN));
    }
}
