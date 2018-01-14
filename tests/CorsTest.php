<?php
declare(strict_types = 1);

namespace Middlewares\Tests;

use Middlewares\Cors;
use Middlewares\Utils\Dispatcher;
use Middlewares\Utils\Factory;
use Neomerx\Cors\Analyzer;
use Neomerx\Cors\Contracts\Constants\CorsResponseHeaders;
use Neomerx\Cors\Strategies\Settings;
use PHPUnit\Framework\TestCase;

class CorsTest extends TestCase
{
    public function corsProvider(): array
    {
        return [
            ['GET', 'http://not-valid.com:321', 403],
            ['GET', 'http://example.com:123', 200],
            ['GET', 'https://example.com:123', 200],
        ];
    }

    /**
     * @dataProvider corsProvider
     */
    public function testCors(string $method, string $url, int $statusCode)
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

        $response = Dispatcher::run(
            [
                new Cors($analyzer),
            ],
            Factory::createServerRequest([], $method, $url)
        );

        $this->assertEquals($statusCode, $response->getStatusCode());
    }
}
