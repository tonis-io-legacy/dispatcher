<?php

namespace Tonis\Dispatcher;

use Tonis\Dispatcher\TestAsset\Invokable;
use Tonis\Dispatcher\TestAsset\TestDispatchable;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

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
     * @covers ::__invoke
     */
    public function testMiddlewareWithNoHandler()
    {
        $request = $this->newRequest('/');
        $response = new Response();

        $result = $this->d->__invoke(
            $request,
            $response,
            function($newRequest, $newResponse) use ($request, $response) {
                $this->assertSame($request, $newRequest);
                $this->assertSame($response, $newResponse);
                return true;
            }
        );
        $this->assertTrue($result);
    }

    /**
     * @covers ::__invoke
     */
    public function testMiddlewareWitHandler()
    {
        $request = $this->newRequest('/')->withAttribute('route.handler', 'foo');
        $response = new Response();

        $result = $this->d->__invoke(
            $request,
            $response,
            function($newRequest, $newResponse) use ($request, $response) {
                $this->assertNotSame($request, $newRequest);
                $this->assertSame($response, $newResponse);
                $this->assertArrayHasKey('dispatch.result', $newRequest->getAttributes());
                return true;
            }
        );
        $this->assertTrue($result);
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

    /**
     * @param string $path
     * @param array $server
     * @return \Zend\Diactoros\ServerRequest
     */
    protected function newRequest($path, array $server = [])
    {
        $server['REQUEST_URI'] = $path;
        $server = array_merge($_SERVER, $server);

        return ServerRequestFactory::fromGlobals($server);
    }
}
