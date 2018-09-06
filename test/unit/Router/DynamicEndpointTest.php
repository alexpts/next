<?php

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\Next;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class DynamicEndpointTest extends TestCase
{
    /** @var Next */
    protected $router;

    public function setUp()
    {
        parent::setUp();
        $this->router = new Next;
    }

    public function testEndPoint(): void
    {
        $request = new ServerRequest([], [], '/user-controller/');
        $store = $this->router->getStoreLayers();

        $endPoint = ['prefix' => 'PTS\\NextRouter\\Controller\\'];
        $layer = $store->getLayerFactory()->dynamicEndPoint($endPoint, ['path' => '/{_controller}/']);
        $store->addLayer($layer);

        /** @var JsonResponse $response */
        $response = $this->router->handle($request);
        $this->assertSame(['action' => 'get'], $response->getPayload());
    }

    public function testBadEndpointError(): void
    {
        $request = new ServerRequest([], [], '/user-controller/');
        $store = $this->router->getStoreLayers();

        $endPoint = ['prefix' => 'PTS\\NextRouter\\Controller\\', 'nextOnError' => false];
        $layer = $store->getLayerFactory()->dynamicEndPoint($endPoint, ['path' => '/user-controller/']);
        $store->addLayer($layer);

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Not found controller name for dynamic controller point');
        $this->router->handle($request);
    }
}
