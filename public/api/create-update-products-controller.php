<?php
use OpenApi\Attributes as OAT;
use ReallySimpleJWT\Token;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
class CreateUpdateProductsController
{
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
                        type: 'number',
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

        $request_data = json_decode((string) $request->getBody(), true);

        if (
            !isset(
            $request_data['active'],
            $request_data['name'],
            $request_data['price'],
            $request_data['stock']
        )
        ) {
            $response->getBody()->write(json_encode(
                ["error" => "JSON pflichtfelder fehlen"]
            ));
            return $response
                ->withStatus(400)
                ->withHeader("Content-Type", "application/json");
        }

        $active = $request_data['active'];
        $id_category = $request_data['id_category'] ?? null;
        $name = trim($request_data['name']);
        $image = trim($request_data['image'] ?? "");
        $description = trim($request_data['description'] ?? "");
        $price = $request_data['price'];
        $stock = $request_data['stock'];
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



        if (!is_int($id_category) && $id_category !== null) {
            $response->getBody()->write(json_encode(
                ["error" => "Muss eine nummer sein"]
            ));
            return $response
                ->withStatus(400)
                ->withHeader("Content-Type", "application/json");
        }

        if ($id_category !== null) {
            $statement = $database->prepare("SELECT * FROM category WHERE category_id = ?");
            $statement->execute([$id_category]);

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

            $statement->execute([$sku, $active, $id_category, $name, $image, $description, $price, $stock]);

            $response->getBody()->write(json_encode(
                ["success" => "Is created"]
            ));
            return $response
                ->withStatus(201)
                ->withHeader("Content-Type", "application/json");
        }

        $statement = $database->prepare("UPDATE product SET active = ?, id_category = ?, name = ?, image = ?, description = ?, price = ?, stock = ? WHERE sku = ?");

        $statement->execute([$active, $id_category, $name, $image, $description, $price, $stock, $sku]);

        $response->getBody()->write(json_encode(
            ["success" => "Is updatet"]
        ));
        return $response
            ->withStatus(201)
            ->withHeader("Content-Type", "application/json");
    }
}