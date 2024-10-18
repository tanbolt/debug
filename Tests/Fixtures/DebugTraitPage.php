<?php
namespace Tanbolt\Debug\Fixtures;

use Tanbolt\Debug\Debug;
use Tanbolt\Debug\DebugTrait;

class DebugTraitPage
{
    use DebugTrait;

    protected function debug()
    {
        if (!$this->debugInstance) {
            $this->debugInstance = new class extends Debug{
                public function content()
                {
                    $msg = [];
                    if ($error = $this->getError()) {
                        $msg[] = $error->getMessage();
                    }
                    if ($warns = $this->getWarnings()) {
                        foreach ($warns as $warn) {
                            $msg[] = $warn->getMessage();
                        }
                    }
                    return implode("\n", $msg);
                }
            };
        }
        return $this->debugInstance;
    }

    public function mockNotice()
    {
        trigger_error('Deprecated', E_USER_DEPRECATED);
    }

    public function mockWarning()
    {
        return $noneExistVar;
    }

    public function mockError()
    {
        throw new \ErrorException('MockError');
    }

    public function output($showWarn = true)
    {
        if ($showWarn && null !== ($debug = $this->renderDebug())) {
            echo $debug;
        } else {
            echo 'Hello World';
        }
    }
}
