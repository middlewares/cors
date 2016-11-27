<?php

namespace Middlewares;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Interop\Http\Middleware\DelegateInterface;
use Neomerx\Cors\Analyzer;
use Neomerx\Cors\Contracts\AnalysisResultInterface;
use Neomerx\Cors\Contracts\Strategies\SettingsStrategyInterface;

class Cors implements ServerMiddlewareInterface
{
    /**
     * @var SettingsStrategyInterface
     */
    private $settings;

    /**
     * Defines the settings used.
     *
     * @param SettingsStrategyInterface $settings
     */
    public function __construct(SettingsStrategyInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Process a request and return a response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $cors = Analyzer::instance($this->settings)->analyze($request);

        switch ($cors->getRequestType()) {
            case AnalysisResultInterface::ERR_NO_HOST_HEADER:
            case AnalysisResultInterface::ERR_ORIGIN_NOT_ALLOWED:
            case AnalysisResultInterface::ERR_METHOD_NOT_SUPPORTED:
            case AnalysisResultInterface::ERR_HEADERS_NOT_SUPPORTED:
                return Utils\Factory::createResponse(403);

            case AnalysisResultInterface::TYPE_REQUEST_OUT_OF_CORS_SCOPE:
                return $delegate->process($request);

            case AnalysisResultInterface::TYPE_PRE_FLIGHT_REQUEST:
                $response = Utils\Factory::createResponse(200);

                return self::withCorsHeaders($response, $cors);

            default:
                $response = $delegate->process($request);

                return self::withCorsHeaders($response, $cors);
        }
    }

    /**
     * Adds cors headers to the response.
     *
     * @param ResponseInterface       $response
     * @param AnalysisResultInterface $cors
     *
     * @return ResponseInterface
     */
    private static function withCorsHeaders(ResponseInterface $response, AnalysisResultInterface $cors)
    {
        foreach ($cors->getResponseHeaders() as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }
}
