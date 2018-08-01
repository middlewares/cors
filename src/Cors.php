<?php
declare(strict_types = 1);

namespace Middlewares;

use Middlewares\Utils\Factory;
use Neomerx\Cors\Contracts\AnalysisResultInterface;
use Neomerx\Cors\Contracts\AnalyzerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Cors implements MiddlewareInterface
{
    /**
     * @var AnalyzerInterface
     */
    private $analyzer;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * Defines the analyzer used.
     */
    public function __construct(AnalyzerInterface $analyzer)
    {
        $this->analyzer = $analyzer;
    }

    /**
     * Set the response factory to return the error responses.
     */
    public function responseFactory(ResponseFactoryInterface $responseFactory): self
    {
        $this->responseFactory = $responseFactory;
        return $this;
    }

    /**
     * Process a request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $cors = $this->analyzer->analyze($request);
        $responseFactory = $this->responseFactory ?: Factory::getResponseFactory();

        switch ($cors->getRequestType()) {
            case AnalysisResultInterface::ERR_NO_HOST_HEADER:
            case AnalysisResultInterface::ERR_ORIGIN_NOT_ALLOWED:
            case AnalysisResultInterface::ERR_METHOD_NOT_SUPPORTED:
            case AnalysisResultInterface::ERR_HEADERS_NOT_SUPPORTED:
                return $responseFactory->createResponse(403);
            case AnalysisResultInterface::TYPE_REQUEST_OUT_OF_CORS_SCOPE:
                return $handler->handle($request);
            case AnalysisResultInterface::TYPE_PRE_FLIGHT_REQUEST:
                $response = $responseFactory->createResponse(200);
                return self::withCorsHeaders($response, $cors);
            default:
                $response = $handler->handle($request);
                return self::withCorsHeaders($response, $cors);
        }
    }

    /**
     * Adds cors headers to the response.
     */
    private static function withCorsHeaders(
        ResponseInterface $response,
        AnalysisResultInterface $cors
    ): ResponseInterface {
        foreach ($cors->getResponseHeaders() as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }
}
