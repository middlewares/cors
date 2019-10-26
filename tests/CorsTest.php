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
            ->setServerOrigin('http', 'example.com', 123)
            ->setAllowedOrigins(['http://good.example.com:321'])
            ->setAllowedMethods(['GET', 'POST', 'DELETE'])
            ->setAllowedHeaders(['content-type', 'x-enabled-custom-header'])
            ->setExposedHeaders(['Content-Type', 'X-Custom-Header'])
            ->setCredentialsNotSupported()
            ->setPreFlightCacheMaxAge(0)
            ->enableAddAllowedMethodsToPreFlightResponse()
            ->enableAddAllowedHeadersToPreFlightResponse()
            ->enableCheckHost();

        $analyzer = Analyzer::instance($settings);

        $response = Dispatcher::run(
            [
                new Cors($analyzer),
            ],
            Factory::createServerRequest($method, $url)
        );

        static::assertEquals($statusCode, $response->getStatusCode());
    }
}
