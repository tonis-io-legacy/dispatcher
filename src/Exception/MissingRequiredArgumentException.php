<?php

namespace Tonis\Dispatcher\Exception;

class MissingRequiredArgumentException extends \RuntimeException
{
    /**
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct(sprintf(
            'Dispatchable failed to dispatch: missing required parameter "%s"',
            $name
        ));
    }
}
