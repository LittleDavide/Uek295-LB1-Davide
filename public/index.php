<?php

use Slim\Factory\AppFactory;

$config = json_decode(file_get_contents(__DIR__ . "/../config.json"), true);
require __DIR__ . "/../vendor/autoload.php";
$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$app->setBasePath("/api/v1");
require_once __DIR__ . "/api/api-main.php";

require_once __DIR__ . "/api/AuthController.php";
require_once __DIR__ . "/api/CreateCategoryController.php";
require_once __DIR__ . "/api/update-category-controller.php";
require_once __DIR__ . "/api/delete-category-controller.php";
require_once __DIR__ . "/api/get-single-category-controller.php";
require_once __DIR__ . "/api/get-all-category-controller.php";
require_once __DIR__ . "/api/create-update-products-controller.php";
require_once __DIR__ . "/api/delete-products-controller.php";
require_once __DIR__ . "/api/get-single-product-controller.php";
require_once __DIR__ . "/api/get-all-products-controller.php";

$database = new mysqli("localhost", "root", "", "uek295_lb01");

$app->post("/authenticate", [
        AuthController::class,
        "authenticate"
]);

$app->patch("/category/{id}", [
        UpdateCategoryController::class,
        "updateCategory"
]);

$app->post("/category", [
        CreateCategoryController::class,
        "createCategory"
]);

$app->delete("/category/{id}", [
        DeleteCategoryController::class,
        "deleteCategory"
]);

$app->get("/category/{id}", [
        GetSingleCategoryController::class,
        "getSingleCategory"
]);

$app->get("/categories", [
        GetAllCategoriesController::class,
        "getAllCategories"
]);

$app->put("/product/{sku}", [
        CreateUpdateProductsController::class,
        "createAndUpdateProducts"
]);

$app->delete("/product/{sku}", [
        DeleteProductsController::class,
        "deleteProducts"
]);

$app->get("/product/{sku}", [
        GetSingleProductController::class,
        "getSingleProduct"
]);

$app->get("/products", [
        GetAllProductsController::class,
        "getAllProducts"
]);

$app->run();
