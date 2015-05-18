<?php

namespace Tonis\Dispatcher\TestAsset;

use Tonis\Dispatcher\Dispatchable;

class TestDispatchable implements Dispatchable
{
    public function dispatch(array $params)
    {
        return $params;
    }
}
