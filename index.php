<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/src/AuthController.php';
require_once __DIR__ . '/src/LeadController.php';

$authController = new AuthController($pdo);
$leadController = new LeadController($pdo);

$request_uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$method = $_SERVER["REQUEST_METHOD"];

if ($request_uri == "/login" && $method == "POST") {
    $authController->login();
} elseif ($request_uri == "/listar" && $method == "GET") {
    $leadController->listar();
} elseif ($request_uri == "/crear" && $method == "POST") {
    $leadController->crear();
} else {
    http_response_code(404);
    echo json_encode(["error" => "Ruta no encontrada"]);
}
?>
