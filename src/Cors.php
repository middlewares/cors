<?php

namespace Middlewares;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Neomerx\Cors\Contracts\AnalysisResultInterface;
use Neomerx\Cors\Contracts\AnalyzerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Cors implements MiddlewareInterface
{
    /**
     * @var AnalyzerInterface
     */
    private $analyzer;

    /**
     * Defines the analyzer used.
     *
     * @param AnalyzerInterface $analyzer
     */
    public function __construct(AnalyzerInterface $analyzer)
    {
        $this->analyzer = $analyzer;
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
        $cors = $this->analyzer->analyze($request);

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
