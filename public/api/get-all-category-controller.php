<?php

use OpenApi\Attributes as OAT;
use ReallySimpleJWT\Token;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * Handles retrieval of all categories.
 */
class GetAllCategoriesController {
    /**
     * Returns all categories as a JSON array.
     *
     * @param Request $request The incoming HTTP request.
     * @param Response $response The HTTP response to populate.
     * @returns Response The HTTP response with its status and body.
     */
    #[OAT\Get(
        path: '/api/v1/categories',
        summary: 'Gibt alle Kategorien zurück.',
        tags: ['category'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Die Kategorien werden als JSON-Array zurückgegeben.'
            ),
            new OAT\Response(
                response: 401,
                description: 'Kein aktives Token. Bitte anmelden.'
            )
        ]
    )]
    public static function getAllCategories(Request $request, Response $response) {
        global $config;
        global $database;

        $token = $_COOKIE["token"] ?? "";

        if ($token == "" || !Token::validate($token, $config['password'])) {
            return $response->withStatus(401);
        }

        $statement = $database->prepare("SELECT * FROM category");

        $statement->execute();

        $result = $statement->get_result();

        $categories = [ ];

        while ($resultData = $result->fetch_assoc()) {
            $categories[] = [
                "category_id" => $resultData['category_id'],
                "active" => $resultData['active'],
                "name" => $resultData['name']
            ];
        }

        $response->getBody()->write(json_encode($categories));

        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json");
    }
}
