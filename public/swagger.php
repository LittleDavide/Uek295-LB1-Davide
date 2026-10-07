<?php

require_once __DIR__ . "/../vendor/autoload.php";

// Load every API file once.
$scripts = glob(__DIR__ . "/api/*.php");
foreach ($scripts as $script) {
    require_once $script;
}

// Build and return the OpenAPI documentation as YAML.
$result = (new \OpenApi\Builder())
    ->addSource(__DIR__ . "/api")
    ->build();

header('Content-Type: application/x-yaml');
echo $result->toYaml();
