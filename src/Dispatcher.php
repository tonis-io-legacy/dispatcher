<?php

namespace Tonis\Dispatcher;

final class Dispatcher
{
    /**
     * @param $object
     * @param array $params
     * @return mixed
     */
    public function dispatch($object, array $params = [])
    {
        if (is_string($object) && class_exists($object)) {
            $result = new $object();
        } elseif ($object instanceof \Closure) {
            $result = $this->dispatchClosure($object, $params);
        } elseif ($object instanceof Dispatchable) {
            $result = $this->dispatchDispatchable($object, $params);
        } elseif (is_object($object) && is_callable($object)) {
            $result = $this->dispatchInvokable($object, $params);
        } elseif (is_callable($object)) {
            $result = $this->dispatchCallable($object, $params);
        } else {
            return $object;
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
        if (is_string($callable)) {
            $callable = explode('::', $callable);
        }

        $function = new \ReflectionMethod($callable[0], $callable[1]);
        $args = $this->buildArgsFromReflectionFunction($function, $params);

        return $function->invokeArgs(new $function->class, $args);
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
            if (isset($params[$param->getName()])) {
                $args[] = $params[$param->getName()];
            } elseif (!$param->isOptional()) {
                throw new Exception\MissingRequiredArgumentException($param->getName());
            }
        }

        return $args;
    }
}
