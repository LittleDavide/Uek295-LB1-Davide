<?php

use OpenApi\Attributes as OAT;
use ReallySimpleJWT\Token;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * Handles category creation.
 */
class CreateCategoryController {
    /**
     * Validates the request and creates a category.
     *
     * @param Request $request The incoming HTTP request.
     * @param Response $response The HTTP response to populate.
     * @return Response The HTTP response with its status and body.
     */
    #[OAT\Post(
        path: '/api/v1/category',
        summary: 'Erstellt eine neue Produkt Kategorie',
        tags: ['category'],
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
                        example: 'GmbH'
                    )
                ]
            )
        ),
        responses: [
            new OAT\Response(
                response: 201,
                description: 'Eintrag erfolgreich erstellt'
            ),
            new OAT\Response(
                response: 400,
                description: 'Validation Fehler beim Request Body'
            ),
            new OAT\Response(
                response: 401,
                description: 'Kein aktives Token. Bitte anmelden.'
            )
        ]
    )]
    public static function createCategory(Request $request, Response $response) {
        global $config;
        global $database;

        $token = $_COOKIE["token"] ?? "";

        if ($token == "" || !Token::validate($token, $config['password'])) {
            return $response->withStatus(401);
        }

        $statement = $database->prepare("INSERT INTO category (active, name) VALUES (?, ?)");

        $requestData = json_decode((string) $request->getBody(), true);

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

        $statement->execute([$active, $name]);

        $response->getBody()->write(json_encode(
            ["success" => "Is created"]
        ));
        return $response
            ->withStatus(201)
            ->withHeader("Content-Type", "application/json");
    }
}
