<?php
use OpenApi\Attributes as OAT;
use ReallySimpleJWT\Token;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
class GetSingleCategoryController
{
    #[OAT\Get(
        path: '/api/v1/category/{id}',
        summary: 'Gibt den gesuchten Kategorie zurück',
        tags: ['category'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Id der Kategorie',
                schema: new OAT\Schema(
                    type: 'int',
                    example: 1
                )
            )
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Erklärung der erfolgreichen Antwort.'
            ),
            new OAT\Response(
                response: 401,
                description: 'Benutzername oder Passwort ist falsch. Die Antwort hat keinen Body.'
            ),
            new OAT\Response(
                response: 404,
                description: 'Category not Found'
            )
        ]
    )]
    public static function getSingleCategory(Request $request, Response $response, $args)
    {
        global $config;
        global $database;

        $token = $_COOKIE["token"] ?? "";

        if ($token == "" || !Token::validate($token, $config['password'])) {
            return $response->withStatus(401);
        }

        $id = $args['id'];

        $statement = $database->prepare("SELECT * FROM category WHERE category_id = ?");

        $statement->execute([$id]);

        $result = $statement->get_result();

        if (mysqli_num_rows($result) == 0) {
            $response->getBody()->write(json_encode(
                ["error" => "category do not exist :("]
            ));
            return $response
                ->withStatus(404)
                ->withHeader("Content-Type", "application/json");
        }

        $result_data = $result->fetch_assoc();

        $response->getBody()->write(json_encode([
            "category_id" => $result_data['category_id'],
            "active" => $result_data['active'],
            "name" => $result_data['name']
        ]));

        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json");
    }
}
