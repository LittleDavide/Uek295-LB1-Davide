<?php

use OpenApi\Attributes as OAT;
use ReallySimpleJWT\Token;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * Handles category deletion.
 */
class DeleteCategoryController {
    /**
     * Deletes the category selected by its ID.
     *
     * @param Request $request The incoming HTTP request.
     * @param Response $response The HTTP response to populate.
     * @param array $args The parameters extracted from the route.
     * @returns Response The HTTP response with its status and body.
     */
    #[OAT\Delete(
        path: '/api/v1/category/{id}',
        summary: 'Einen Eintrag löschen aus der Datenbank',
        tags: ['category'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Id des Eintrags in der Datenbank',
                schema: new OAT\Schema(
                    type: 'int',
                    example: 1
                )
            )
        ],
        responses: [
            new OAT\Response(
                response: 204,
                description: 'Ressource erfolgreich gelöscht.'
            ),
            new OAT\Response(
                response: 401,
                description: 'Kein aktives Token. Bitte anmelden.'
            ),
            new OAT\Response(
                response: 404,
                description: 'Category not Found'
            )
        ]
    )]
    public static function deleteCategory(Request $request, Response $response, $args) {
        global $config;
        global $database;

        $token = $_COOKIE["token"] ?? "";

        if ($token == "" || !Token::validate($token, $config['password'])) {
            return $response->withStatus(401);
        }

        $categoryId = $args['id'];

        $statement = $database->prepare("SELECT * FROM category WHERE category_id = ?");

        $statement->execute([$categoryId]);

        if (mysqli_num_rows($statement->get_result()) == 0) {
            $response->getBody()->write(json_encode(
                ["error" => "category do not exist :("]
            ));
            return $response
                ->withStatus(404)
                ->withHeader("Content-Type", "application/json");
        }

        $statement = $database->prepare("DELETE FROM category where category_id = ?");

        $statement->execute([$categoryId]);

        return $response
            ->withStatus(204)
            ->withHeader("Content-Type", "application/json");
    }
}
