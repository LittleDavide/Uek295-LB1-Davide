<?php

use OpenApi\Attributes as OAT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Provides the API metadata and index response.
 */
#[OAT\Info(
    title: 'Person API',
    version: '1.0.0'
)]
class ApiMain {
    /**
     * Returns the index response.
     *
     * @param Request $request The incoming HTTP request.
     * @param Response $response The HTTP response to populate.
     * @param array $args The parameters extracted from the route.
     * @return Response The HTTP response with its status and body.
     */
    public static function index(Request $request, Response $response, $args) {
        $response->getBody()->write("Hello, world!");
        return $response;
    }
}
