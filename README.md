# Tonis\Dispatcher

[![Build Status](https://scrutinizer-ci.com/g/tonis-io/dispatcher/badges/build.png?b=master)](https://scrutinizer-ci.com/g/tonis-io/dispatcher/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/tonis-io/dispatcher/badges/coverage.png?s=3f606f26f25597e7e41b36a35f23810754f8e34d)](https://scrutinizer-ci.com/g/tonis-io/dispatcher/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/tonis-io/dispatcher/badges/quality-score.png?s=f12c6af7ffc9a2d6da6ddec32c2953f3685c7fc7)](https://scrutinizer-ci.com/g/tonis-io/dispatcher/)

## Installation

Tonis\Dispatcher can be installed using composer which will setup any autoloading for you.

`composer require tonis/dispatcher`

Additionally, you can download or clone the repository and setup your own autoloading.

## Dispatching

Dispatching can be done via the `Dispatchable` interface, a `Closure`, `callable`, or `invokable`.

### Dispatchable

The `Dispatchable` interface has a single `dispatch` method that accepts an array of parameters. There is no
reflection involved in dispatching this way so it is the most performant.

```php
use Tonis\Dispatcher\Dispatcher;

class TestDispatchable implements Dispatchable
{
    public function dispatch(array $params) { return 'foo'; }
}

$d = new Dispatcher();
$d->add('foo', new TestDispatchable());

// outputs 'foo'
echo $d->ispatch('foo');

// the second argument will be passed to the $params array of the dispatch() method
echo $d->ispatch('foo', ['name' => 'bar']);
```

### Closure

Dispatching via a closure is useful for micro-frameworks or for quickly setting up prototypes.

```php
use Tonis\Dispatcher\Dispatcher();

$d = new Dispatcher();
$d->add('foo', function() { return 'foo'; });

// outputs 'foo'
echo $d->ispatch('foo');

// this closure has a default value of 'baz' for the $slug parameter
$d->add('bar', function($id, $slug = 'baz') {
    return $id . ': ' . $slug;
)};

// this uses the default value for $slug
// outputs '1: baz'
echo $d->ispatch('bar', ['id' => 1]);

// this overwrites the default value
// outputs '1: booze'
echo $d->ispatch('bar', ['id' => 1, 'slug' => 'booze']);
```

### Invokable
```php
use Tonis\Dispatcher\Dispatcher;

class TestInvokable implements Dispatchable
{
    public function __invoke($id, $slug = 'baz')
    {
        return $id . ': ' . $slug;
    }
}

$d = new Dispatcher();
$d->add('foo', new TestInvokable());

// this uses the default value for $slug
// outputs '1: baz'
echo $d->ispatch('bar', ['id' => 1]);

// this overwrites the default value
// outputs '1: booze'
echo $d->ispatch('bar', ['id' => 1, 'slug' => 'booze']);
```

### Callable

```php
use Tonis\Dispatcher\Dispatcher;

class TestCallable implements Dispatchable
{
    public function outputId($id)
    {
        return $id;
    }

    public static function outputStaticId($id)
    {
        return $id;
    }
}

$test = new TestCallable();

$d = new Dispatcher();
$d->add('foo', [$test, 'outputId']);

// output is '1'
$d->ispatch('foo', ['id' => 1]);

$d->add('bar', 'TestCallable::outputStaticId');

// output is '2'
$d->ispatch('bar', ['id' => 2]);
```

## Lazy-loading and recursion

The dispatcher will continue to dispatch as long as a dispatchable is returned. This allows you to lazy-load objects
by using a closure (which is dispatchable).

```php
<?php
use Tonis\Dispatcher\Dispatcher;

$d = new Dispatcher();

// assume the TestDispatchable class from above
// instead of this
$d->add('foo', new TestDispatchable());

// you would do this
$d->add('foo', function() {
    return new TestDispatchable();
});

// the output is identical to the output from Dispatchable above
// the only difference is that it's lazy loaded on dispatch() instead.
```

### What's up with `ispatch()` instead of `dispatch()`?

Because it's damn cute, that's why! If you prefer, though, you can use `dispatch()` instead as `ispatch()` is a simple
proxy call to `dispatch()`.
