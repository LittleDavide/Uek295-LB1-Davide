<?php
    use Slim\Factory\AppFactory;
    
    $config = json_decode(file_get_contents(__DIR__ . "/../config.json"), true);
    require __DIR__ . "/../vendor/autoload.php";
    $app = AppFactory::create();
    $app->addBodyParsingMiddleware();

    $app->setBasePath("/api/v1");
    require_once __DIR__ . "/api/api-main.php";

    require_once __DIR__ . "/api/AuthController.php";

    $app->post("/authenticate", [
        AuthController::class,
        "authenticate"
    ]);

    $app->run();