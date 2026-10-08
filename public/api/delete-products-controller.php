<?php

use OpenApi\Attributes as OAT;
use ReallySimpleJWT\Token;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * Handles product deletion.
 */
class DeleteProductsController {
    /**
     * Deletes the product selected by its SKU.
     *
     * @param Request $request The incoming HTTP request.
     * @param Response $response The HTTP response to populate.
     * @param array $args The parameters extracted from the route.
     * @return Response The HTTP response with its status and body.
     */
    #[OAT\Delete(
        path: '/api/v1/product/{sku}',
        summary: 'Löscht ein Produkt anhand seiner SKU.',
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
                response: 204,
                description: 'Produkt erfolgreich gelöscht.'
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
    public static function deleteProducts(Request $request, Response $response, $args) {
        global $config;
        global $database;

        $token = $_COOKIE["token"] ?? "";

        if ($token == "" || !Token::validate($token, $config['password'])) {
            return $response->withStatus(401);
        }

        $sku = $args['sku'];

        $statement = $database->prepare("SELECT * FROM product WHERE sku = ?");

        $statement->execute([$sku]);

        if (mysqli_num_rows($statement->get_result()) == 0) {
            $response->getBody()->write(json_encode(
                ["error" => "products does not exits :("]
            ));
            return $response
                ->withStatus(404)
                ->withHeader("Content-Type", "application/json");
        }

        $statement = $database->prepare("DELETE FROM product where sku = ?");

        $statement->execute([$sku]);

        return $response
            ->withStatus(204)
            ->withHeader("Content-Type", "application/json");
    }
}
