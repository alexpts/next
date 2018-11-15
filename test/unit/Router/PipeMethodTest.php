<?php

use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use PTS\NextRouter\Next;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class PipeMethodTest extends TestCase
{

    /** @var Next */
    protected $app;

    public function setUp()
    {
        parent::setUp();

        $this->app = new Next;
    }

    public function testMethod(): void
    {
        $request = new ServerRequest([], [], '/profile', 'GET');

        $this->app->getStoreLayers()
            ->get('/user', function ($request, $next) {
                return new JsonResponse(['status' => 'user']);
            })
            ->pipe([
                function($request, RequestHandlerInterface $next) {
                    /** @var JsonResponse $response */
                    $response = $next->handle($request);
                    $body = array_merge($response->getPayload(), ['pipe1' => true]);
                    return new JsonResponse($body);
                },
                function($request, RequestHandlerInterface $next) {
                    $response = $next->handle($request);
                    $body = array_merge($response->getPayload(), ['pipe2' => true]);
                    return new JsonResponse($body);
                },
            ], ['method' => ['GET'], 'path' => '/profile'])->use(function(){
                return new JsonResponse(['status' => 404]);
            }, ['name' => 'otherwise']);

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);

        $this->assertSame(['status' => 404, 'pipe2' => true, 'pipe1' => true], $response->getPayload());
    }

    public function testMethod2(): void
    {
        $request = new ServerRequest([], [], '/profile', 'GET');

        $this->app->getStoreLayers()
            ->get('/user', function ($request, $next) {
                return new JsonResponse(['status' => 'user']);
            })
            ->pipe([
                function($request, RequestHandlerInterface $next) {
                    /** @var JsonResponse $response */
                    $response = $next->handle($request);
                    $body = array_merge($response->getPayload(), ['pipe1' => true]);
                    return new JsonResponse($body);
                },
                function($request, $next) {
                    return new JsonResponse(['pipe2' => true]);
                },
            ], ['method' => ['GET'], 'path' => '/profile']);

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);

        $this->assertSame(['pipe2' => true, 'pipe1' => true], $response->getPayload());
    }
}
