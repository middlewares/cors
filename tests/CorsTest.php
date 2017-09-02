<?php

namespace Middlewares\Tests;

use PHPUnit\Framework\TestCase;
use Middlewares\Cors;
use Middlewares\Utils\Dispatcher;
use Middlewares\Utils\Factory;
use Neomerx\Cors\Analyzer;
use Neomerx\Cors\Strategies\Settings;
use Neomerx\Cors\Contracts\Constants\CorsResponseHeaders;

class CorsTest extends TestCase
{
    public function corsProvider()
    {
        return [
            ['http://not-valid.com:321', 403],
            ['http://example.com:123', 200],
        ];
    }

    /**
     * @dataProvider corsProvider
     */
    public function testCors($url, $statusCode)
    {
        $settings = (new Settings())
            ->setServerOrigin([
                'scheme' => 'http',
                'host' => 'example.com',
                'port' => '123',
            ])
            ->setRequestAllowedOrigins([
                'http://good.example.com:321' => true,
                'http://evil.example.com:123' => null,
                CorsResponseHeaders::VALUE_ALLOW_ORIGIN_ALL => null,
                CorsResponseHeaders::VALUE_ALLOW_ORIGIN_NULL => null,
            ])
            ->setRequestAllowedMethods([
                'GET' => true,
                'PATCH' => null,
                'POST' => true,
                'PUT' => null,
                'DELETE' => true,
            ])
            ->setRequestAllowedHeaders([
                'content-type' => true,
                'some-disabled-header' => null,
                'x-enabled-custom-header' => true,
            ])
            ->setResponseExposedHeaders([
                'Content-Type' => true,
                'X-Custom-Header' => true,
                'X-Disabled-Header' => null,
            ])
            ->setRequestCredentialsSupported(false)
            ->setPreFlightCacheMaxAge(0)
            ->setForceAddAllowedMethodsToPreFlightResponse(true)
            ->setForceAddAllowedHeadersToPreFlightResponse(true)
            ->setCheckHost(true);

        $analyzer = Analyzer::instance($settings);

        $request = Factory::createServerRequest([], 'GET', $url);

        $response = Dispatcher::run([
            new Cors($analyzer),
        ], $request);

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals($statusCode, $response->getStatusCode());
    }
}