<?php

use OpenApi\Attributes as OAT;
use ReallySimpleJWT\Token;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * Handles product creation and updates.
 */
class CreateUpdateProductsController {
    /**
     * Validates the request and creates or updates the selected product.
     *
     * @param Request $request The incoming HTTP request.
     * @param Response $response The HTTP response to populate.
     * @param array $args The parameters extracted from the route.
     * @returns Response The HTTP response with its status and body.
     */
    #[OAT\Put(
        path: '/api/v1/product/{sku}',
        summary: 'Erstellt ein neues Produkt oder aktualisiert ein bestehendes Produkt.',
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
        requestBody: new OAT\RequestBody(
            required: true,
            description: 'Der JSON-Body muss active, name, price und stock enthalten. id_category, image und description sind optional. id_category darf null sein.',
            content: new OAT\JsonContent(
                properties: [
                    new OAT\Property(
                        property: 'active',
                        type: 'integer',
                        example: 1
                    ),
                    new OAT\Property(
                        property: 'id_category',
                        type: 'integer',
                        nullable: true,
                        example: 1
                    ),
                    new OAT\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Kochschinken'
                    ),
                    new OAT\Property(
                        property: 'image',
                        type: 'string',
                        example: 'kochschinken.jpg'
                    ),
                    new OAT\Property(
                        property: 'description',
                        type: 'string',
                        example: 'Frischer Kochschinken'
                    ),
                    new OAT\Property(
                        property: 'price',
                        type: 'double',
                        example: 19.90
                    ),
                    new OAT\Property(
                        property: 'stock',
                        type: 'integer',
                        example: 10
                    )
                ]
            )
        ),
        responses: [
            new OAT\Response(
                response: 201,
                description: 'Eintrag erfolgreich erstellt oder geupdatet'
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
    public static function createAndUpdateProducts(Request $request, Response $response, $args)
    {
        global $config;
        global $database;

        $token = $_COOKIE["token"] ?? "";

        if ($token == "" || !Token::validate($token, $config['password'])) {
            return $response->withStatus(401);
        }

        $requestData = json_decode((string) $request->getBody(), true);

        if (
            !isset(
                $requestData['active'],
                $requestData['name'],
                $requestData['price'],
                $requestData['stock']
            )
        ) {
            $response->getBody()->write(json_encode(
                ["error" => "JSON pflichtfelder fehlen"]
            ));
            return $response
                ->withStatus(400)
                ->withHeader("Content-Type", "application/json");
        }

        $active = $requestData['active'];
        $categoryId = $requestData['id_category'] ?? null;
        $name = trim($requestData['name']);
        $image = trim($requestData['image'] ?? "");
        $description = trim($requestData['description'] ?? "");
        $price = $requestData['price'];
        $stock = $requestData['stock'];
        $sku = trim($args['sku']);

        if (strlen($sku) > 100 || strlen($sku) < 1) {
            $response->getBody()->write(json_encode(
                ["error" => "Kein gültiger sku minimum 1 zeichen und max 100 Zeichen"]
            ));
            return $response
                ->withStatus(400)
                ->withHeader("Content-Type", "application/json");
        }

        if ($active > 1 || $active < 0) {
            $response->getBody()->write(json_encode(
                ["error" => "Keine gültige nummer. 1 = active, 0 = deactive"]
            ));
            return $response
                ->withStatus(400)
                ->withHeader("Content-Type", "application/json");
        }

        if (!is_int($categoryId) && $categoryId !== null) {
            $response->getBody()->write(json_encode(
                ["error" => "Muss eine nummer sein"]
            ));
            return $response
                ->withStatus(400)
                ->withHeader("Content-Type", "application/json");
        }

        if ($categoryId !== null) {
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
        }

        if (strlen($name) > 500 || strlen($name) < 1) {
            $response->getBody()->write(json_encode(
                ["error" => "Kein Name oder zu viele Zeichen"]
            ));
            return $response
                ->withStatus(400)
                ->withHeader("Content-Type", "application/json");
        }

        if (strlen($image) > 1000) {
            $response->getBody()->write(json_encode(
                ["error" => "Zu viele Zeichen"]
            ));
            return $response
                ->withStatus(400)
                ->withHeader("Content-Type", "application/json");
        }

        if (!is_double($price)) {
            $response->getBody()->write(json_encode(
                ["error" => "Kein gültiges Format bitte in Decimal eingeben"]
            ));
            return $response
                ->withStatus(400)
                ->withHeader("Content-Type", "application/json");
        }

        if (!is_int($stock) || $stock < 0) {
            $response->getBody()->write(json_encode(
                ["error" => "Muss eine Positive Nummer sein"]
            ));
            return $response
                ->withStatus(400)
                ->withHeader("Content-Type", "application/json");
        }

        $statement = $database->prepare("SELECT * FROM product WHERE sku = ?");

        $statement->execute([$sku]);

        if (mysqli_num_rows($statement->get_result()) == 0) {
            $statement = $database->prepare("INSERT INTO product (sku, active, id_category, name, image, description, price, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            $statement->execute([$sku, $active, $categoryId, $name, $image, $description, $price, $stock]);

            $response->getBody()->write(json_encode(
                ["success" => "Is created"]
            ));
            return $response
                ->withStatus(201)
                ->withHeader("Content-Type", "application/json");
        }

        $statement = $database->prepare("UPDATE product SET active = ?, id_category = ?, name = ?, image = ?, description = ?, price = ?, stock = ? WHERE sku = ?");

        $statement->execute([$active, $categoryId, $name, $image, $description, $price, $stock, $sku]);

        $response->getBody()->write(json_encode(
            ["success" => "Is updatet"]
        ));
        return $response
            ->withStatus(201)
            ->withHeader("Content-Type", "application/json");
    }
}
