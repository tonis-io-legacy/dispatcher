<?php

namespace Tonis\Dispatcher;

interface Dispatchable
{
    public function dispatch(array $params);
}
