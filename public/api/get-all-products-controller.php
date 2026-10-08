<?php

use OpenApi\Attributes as OAT;
use ReallySimpleJWT\Token;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * Handles retrieval of all products.
 */
class GetAllProductsController {
    /**
     * Returns all products as a JSON array.
     *
     * @param Request $request The incoming HTTP request.
     * @param Response $response The HTTP response to populate.
     * @returns Response The HTTP response with its status and body.
     */
    #[OAT\Get(
        path: '/api/v1/products',
        summary: 'Gibt alle Produkte zurück.',
        tags: ['product'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Die Produkte werden als JSON-Array zurückgegeben.'
            ),
            new OAT\Response(
                response: 401,
                description: 'Kein aktives Token. Bitte anmelden.'
            )
        ]
    )]
    public static function getAllProducts(Request $request, Response $response) {
        global $config;
        global $database;

        $token = $_COOKIE["token"] ?? "";

        if ($token == "" || !Token::validate($token, $config['password'])) {
            return $response->withStatus(401);
        }

        $statement = $database->prepare("SELECT * FROM product");

        $statement->execute();

        $result = $statement->get_result();

        $products = [ ];

        while ($resultData = $result->fetch_assoc()) {
            $products[] = [
                "product_id" => $resultData['product_id'],
                "sku" => $resultData['sku'],
                "active" => $resultData['active'],
                "id_category" => $resultData['id_category'] ?? "",
                "name" => $resultData['name'],
                "image" => $resultData['image'],
                "description" => $resultData['description'],
                "price" => $resultData['price'],
                "stock" => $resultData['stock']
            ];
        }

        $response->getBody()->write(json_encode($products));

        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json");
    }
}
