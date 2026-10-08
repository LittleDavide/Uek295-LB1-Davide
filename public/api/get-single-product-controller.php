<?php

use OpenApi\Attributes as OAT;
use ReallySimpleJWT\Token;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * Handles retrieval of individual products.
 */
class GetSingleProductController {
    /**
     * Returns the product selected by its SKU.
     *
     * @param Request $request The incoming HTTP request.
     * @param Response $response The HTTP response to populate.
     * @param array $args The parameters extracted from the route.
     * @return Response The HTTP response with its status and body.
     */
    #[OAT\Get(
        path: '/api/v1/product/{sku}',
        summary: 'Gibt das gesuchte Produkt anhand seiner SKU zurück.',
        tags: ['product'],
        parameters: [
            new OAT\Parameter(
                name: 'sku',
                in: 'path',
                required: true,
                description: 'Die SKU des Produkts',
                schema: new OAT\Schema(
                    type: 'string',
                    example: 'ART-001'
                )
            )
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Das gesuchte Produkt wird als JSON zurückgegeben.'
            ),
            new OAT\Response(
                response: 401,
                description: 'Kein aktives Token. Bitte anmelden.'
            ),
            new OAT\Response(
                response: 404,
                description: 'Produkt nicht gefunden.'
            )
        ]
    )]
    public static function getSingleProduct(Request $request, Response $response, $args) {
        global $config;
        global $database;

        $token = $_COOKIE["token"] ?? "";

        if ($token == "" || !Token::validate($token, $config['password'])) {
            return $response->withStatus(401);
        }

        $sku = $args['sku'];

        $statement = $database->prepare("SELECT * FROM product WHERE sku = ?");

        $statement->execute([$sku]);

        $result = $statement->get_result();

        if (mysqli_num_rows($result) == 0) {
            $response->getBody()->write(json_encode(
                ["error" => "product does not exists :("]
            ));
            return $response
                ->withStatus(404)
                ->withHeader("Content-Type", "application/json");
        }

        $resultData = $result->fetch_assoc();

        $response->getBody()->write(json_encode([
                "product_id" => $resultData['product_id'],
                "sku" => $resultData['sku'],
                "active" => $resultData['active'],
                "id_category" => $resultData['id_category'] ?? "",
                "name" => $resultData['name'],
                "image" => $resultData['image'],
                "description" => $resultData['description'],
                "price" => $resultData['price'],
                "stock" => $resultData['stock']
        ]));

        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json");
    }
}
