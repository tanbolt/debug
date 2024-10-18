<?php
require __DIR__.'/../phpunit.php';
PHPUNIT_LOADER::addDir('Tanbolt\Debug\Fixtures', __DIR__.'/../Fixtures');

return new Tanbolt\Debug\Fixtures\DebugTraitPage();
