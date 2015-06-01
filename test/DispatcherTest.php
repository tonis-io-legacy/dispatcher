<?php

namespace Tonis\Dispatcher;

use Tonis\Dispatcher\TestAsset\Invokable;
use Tonis\Dispatcher\TestAsset\TestDispatchable;

/**
 * @coversDefaultClass \Tonis\Dispatcher\Dispatcher
 */
class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Dispatcher
     */
    private $d;

    protected function setUp()
    {
        $this->d = new Dispatcher();
    }

    /**
     * @covers ::dispatch
     */
    public function testDispatchWithStringReturnsExpectedObject()
    {
        $result = $this->d->dispatch('\stdClass');

        $this->assertInstanceOf('\stdClass', $result);
    }

    /**
     * @covers ::dispatch
     * @covers ::dispatchClosure
     * @covers ::buildArgsFromReflectionFunction
     */
    public function testDispatchWithClosureReturnsExpectedResult()
    {
        $results = $this->d->dispatch(
            function ($arg1, $arg2) {
                return $arg1 . ', ' . $arg2;
            },
            [
                'arg1' => 'foo',
                'arg2' => 'bar'
            ]
        );

        $this->assertEquals('foo, bar', $results);
    }

    /**
     * @covers ::dispatch
     * @covers ::dispatchClosure
     * @covers ::buildArgsFromReflectionFunction
     * @covers \Tonis\Dispatcher\Exception\MissingRequiredArgumentException
     * @expectedException \Tonis\Dispatcher\Exception\MissingRequiredArgumentException
     * @expectedExceptionMessage Dispatchable failed to dispatch: missing required parameter "arg1"
     */
    public function testDispatchWithClosureMissingRequiredArgumentsThrowsExpectedException()
    {
        $results = $this->d->dispatch(
            function ($arg1, $arg2) {
                return $arg1 . ', ' . $arg2;
            }
        );

        $this->assertEquals('foo, bar', $results);
    }

    /**
     * @covers ::dispatch
     * @covers ::dispatchDispatchable
     */
    public function testDispatchWithDispatchableReturnsExpectedResult()
    {
        $params = ['foo', 'bar'];
        $result = $this->d->dispatch(new TestDispatchable(), $params);

        $this->assertSame($params, $result);
    }

    /**
     * @covers ::dispatch
     * @covers ::dispatchInvokable
     * @covers ::buildArgsFromReflectionFunction
     */
    public function testDispatchableWithInvokableReturnsExpectedResult()
    {
        $result = $this->d->dispatch(
            new Invokable(),
            [
                'id' => '1',
                'slug' => 'my-slug'
            ]
        );

        $this->assertEquals('id: 1, slug: my-slug', $result);
    }

    /**
     * @covers ::dispatch
     * @covers ::dispatchCallable
     * @covers ::buildArgsFromReflectionFunction
     */
    public function testDispatchWitCallableReturnsExpectedResult()
    {
        $result = $this->d->dispatch(
            [$this, 'callableFunction'],
            [
                'id' => '1',
                'slug' => 'my-slug'
            ]
        );

        $this->assertEquals('id: 1, slug: my-slug', $result);
    }

    public function callableFunction($id, $slug = 'foo')
    {
        return 'id: ' . $id . ', slug: ' . $slug;
    }
}
