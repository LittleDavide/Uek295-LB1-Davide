<?php
use OpenApi\Attributes as OAT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
#[OAT\Info(
    title: 'Person API',
    version: '1.0.0'
)]
class ApiMain {
    public static function index(Request $request, Response $response, $args) {
        $response->getBody()->write("Hello, world!");
        return $response;
    }
}