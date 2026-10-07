<?php 
use OpenApi\Attributes as OAT; 
use ReallySimpleJWT\Token;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
class AuthController {
        #[OAT\Post(
        path: '/api/v1/authenticate',
        summary: 'Authentifiziert einen Benutzer anhand von Benutzername und Passwort.',
        tags: ['auth'],
        requestBody: new OAT\RequestBody(
            required: true,
            description: 'Der JSON-Body muss username und password als Strings enthalten.',
            content: new OAT\JsonContent(
                properties: [
                    new OAT\Property(
                        property: 'username',
                        type: 'string',
                        example: 'max'
                    ),
                    new OAT\Property(
                        property: 'password',
                        type: 'string',
                        example: 'mein-passwort'
                    )
                ]
            )
        ),
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Authentifizierung erfolgreich. Gibt success: true zurück und setzt das Cookie token mit einem für eine Stunde gültigen JWT.'
            ),
            new OAT\Response(
                response: 401,
                description: 'Benutzername oder Passwort ist falsch. Die Antwort hat keinen Body.'
            )
        ]
    )]
    public static function authenticate(Request $request, Response $response, $args) {
        $requestBody = $request->getParsedBody();
        global $config;
        if ($requestBody["username"] != $config['username'] || $requestBody["password"] != $config["password"]) {
            return $response->withStatus(401);
        }
        // Check if credentials are not valid.
        $token = Token::create($config['username'], $config["password"], time() + 3600, "localhost");
        setcookie("token", $token, time() + 3600);

        $response = $response->withHeader("content-type", "application/json");
        $response->getBody()->write(json_encode(["success" => true]));
        return $response->withStatus(200);
    }
}