<?php

namespace Tonis\Dispatcher\TestAsset;

class Invokable
{
    public function __invoke($id, $slug = 'foo')
    {
        return 'id: ' . $id . ', slug: ' . $slug;
    }
}
