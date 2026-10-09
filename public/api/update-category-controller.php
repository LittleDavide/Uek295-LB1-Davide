<?php

use OpenApi\Attributes as OAT;
use ReallySimpleJWT\Token;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * Handles category updates.
 */
class UpdateCategoryController {
    /**
     * Validates the request and updates the selected category.
     *
     * @param Request $request The incoming HTTP request.
     * @param Response $response The HTTP response to populate.
     * @param array $args The parameters extracted from the route.
     * @return Response The HTTP response with its status and body.
     */
    #[OAT\PATCH(
        path: '/api/v1/category/{id}',
        summary: 'Einen Kategorie daten ändern ',
        tags: ['category'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Die Id der Kategorie',
                schema: new OAT\Schema(
                    type: 'int',
                    example: '1'
                )
            )
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            description: 'Der JSON-Body muss active (tinyInt)und name als Strings enthalten.',
            content: new OAT\JsonContent(
                properties: [
                    new OAT\Property(
                        property: 'active',
                        type: 'int',
                        example: '1'
                    ),
                    new OAT\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Davide'
                    )
                ]
            )
        ),
        responses: [
            new OAT\Response(
                response: 201,
                description: 'Eintrag geupdatet'
            ),
            new OAT\Response(
                response: 400,
                description: 'Validation Fehler beim Request Body'
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
    public static function updateCategory(Request $request, Response $response, $args) {
        global $config;
        global $database;

        $token = $_COOKIE["token"] ?? "";

        if ($token == "" || !Token::validate($token, $config['password'])) {
            return $response->withStatus(401);
        }
        $requestData = $request->getParsedBody();

        if (!isset($requestData['name'], $requestData['active'])) {
            $response->getBody()->write(json_encode(
                ["error" => "JSON pflichtfelder fehlen"]
            ));
            return $response
                ->withStatus(400)
                ->withHeader("Content-Type", "application/json");
        }

        $name = trim($requestData['name']);
        $active = $requestData['active'];
        $categoryId = $args['id'];

        $statement = $database->prepare("SELECT * FROM category WHERE category_id = ?");

        $statement->execute([$categoryId]);

        // An unchanged update can affect zero rows even when the category exists.
        if (mysqli_num_rows($statement->get_result()) == 0) {
            $response->getBody()->write(json_encode(
                ["error" => "category do not exist :("]
            ));
            return $response
                ->withStatus(404)
                ->withHeader("Content-Type", "application/json");
        }

        $statement = $database->prepare("UPDATE category SET active = ?, name = ? WHERE category_id = ?");

        if ($active > 1 || $active < 0) {
            $response->getBody()->write(json_encode(
                ["error" => "Keine gültige nummer. 1 = active, 0 = deactive"]
            ));
            return $response
                ->withStatus(400)
                ->withHeader("Content-Type", "application/json");
        }

        if (strlen($name) > 500 || strlen($name) < 1) {
            $response->getBody()->write(json_encode(
                ["error" => "Kein Name oder zu viele Zeichen"]
            ));
            return $response
                ->withStatus(400)
                ->withHeader("Content-Type", "application/json");
        }

        $statement->execute([$active, $name, $categoryId]);

        $response->getBody()->write(json_encode(
            ["success" => "Is updated"]
        ));
        return $response
            ->withStatus(201)
            ->withHeader("Content-Type", "application/json");
    }
}
