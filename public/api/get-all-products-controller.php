<?php
use OpenApi\Attributes as OAT;
use ReallySimpleJWT\Token;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class GetAllProductsController
{
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
    public static function getAllProducts(Request $request, Response $response)
    {
        global $config;
        global $database;

        $token = $_COOKIE["token"] ?? "";

        if ($token == "" || !Token::validate($token, $config['password'])) {
            return $response->withStatus(401);
        }

        $statement = $database->prepare("SELECT * FROM product");

        $statement->execute();

        $result = $statement->get_result();



        $products = [];

        while ($result_data = $result->fetch_assoc()) {
            $products[] = [
                "product_id" => $result_data['product_id'],
                "sku" => $result_data['sku'],
                "active" => $result_data['active'],
                "id_category" => $result_data['id_category'] ?? "",
                "name" => $result_data['name'],
                "image" => $result_data['image'],
                "description" => $result_data['description'],
                "price" => $result_data['price'],
                "stock" => $result_data['stock']
            ];
        }

        $response->getBody()->write(json_encode($products));

        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json");
    }
}