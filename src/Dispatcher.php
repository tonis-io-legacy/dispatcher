<?php

namespace Tonis\Dispatcher;

final class Dispatcher
{
    /**
     * @param mixed $input
     * @param array $params
     * @return mixed
     */
    public function dispatch($input, array $params = [])
    {
        if (is_string($input) && class_exists($input)) {
            $result = new $input();
        } elseif ($input instanceof \Closure) {
            $result = $this->dispatchClosure($input, $params);
        } elseif ($input instanceof Dispatchable) {
            $result = $this->dispatchDispatchable($input, $params);
        } elseif (is_object($input) && is_callable($input)) {
            $result = $this->dispatchInvokable($input, $params);
        } elseif (is_callable($input)) {
            $result = $this->dispatchCallable($input, $params);
        } else {
            return $input;
        }

        return $this->dispatch($result, $params);
    }

    /**
     * @param callable $callable
     * @param array $params
     * @return mixed
     */
    public function dispatchCallable($callable, array $params = [])
    {
        $function = new \ReflectionMethod($callable[0], $callable[1]);
        $args = $this->buildArgsFromReflectionFunction($function, $params);

        return call_user_func_array($callable, $args);
    }

    /**
     * @param $object
     * @param array $params
     * @return mixed
     */
    public function dispatchInvokable($object, array $params = [])
    {
        $function = new \ReflectionMethod($object, '__invoke');
        $args = $this->buildArgsFromReflectionFunction($function, $params);

        return $function->invokeArgs($object, $args);
    }

    /**
     * @param \Closure $closure
     * @param array $params
     * @return mixed
     */
    public function dispatchClosure(\Closure $closure, array $params = [])
    {
        $function = new \ReflectionFunction($closure);
        $args = $this->buildArgsFromReflectionFunction($function, $params);

        return $function->invokeArgs($args);
    }

    /**
     * @param Dispatchable $dispatchable
     * @param array $params
     * @return mixed
     */
    public function dispatchDispatchable(Dispatchable $dispatchable, array $params = [])
    {
        return $dispatchable->dispatch($params);
    }

    /**
     * @param \ReflectionFunctionAbstract $reflection
     * @param array $params
     * @return array
     * @throws Exception\MissingRequiredArgumentException
     */
    public function buildArgsFromReflectionFunction(\ReflectionFunctionAbstract $reflection, array $params)
    {
        $args = [];

        foreach ($reflection->getParameters() as $param) {
            if (isset($params[$param->name])) {
                $args[] = $params[$param->name];
            } elseif (!$param->isOptional()) {
                throw new Exception\MissingRequiredArgumentException($param->name);
            }
        }

        return $args;
    }
}
