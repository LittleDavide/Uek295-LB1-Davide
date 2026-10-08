<?php
use OpenApi\Attributes as OAT;
use ReallySimpleJWT\Token;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
class GetSingleProductController
{
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
    public static function getSingleProduct(Request $request, Response $response, $args)
    {
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

        $result_data = $result->fetch_assoc();

        $response->getBody()->write(json_encode([
            "product_id" => $result_data['product_id'],
            "sku" => $result_data['sku'],
            "active" => $result_data['active'],
            "id_category" => $result_data['id_category'] ?? "",
            "name" => $result_data['name'],
            "image" => $result_data['image'],
            "description" => $result_data['description'],
            "price" => $result_data['price'],
            "stock" => $result_data['stock']
        ]));

        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json");
    }
}
