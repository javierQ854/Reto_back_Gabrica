<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/AuthController.php';

class LeadController {
    private $pdo;
    private $authController;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->authController = new AuthController($pdo);
    }

    function obtenerIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] === '::1' ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];
        }
    }

    public function listar() {
        header("Content-Type: application/json");

        $headers = getallheaders();
        $authHeader = $headers["Authorization"] ?? "";

        if (!$authHeader || !str_starts_with($authHeader, "Bearer ")) {
            http_response_code(401);
            echo json_encode(["error" => "Token no proporcionado o formato incorrecto"]);
            return;
        }

        // Extraer el token eliminando "Bearer "
        $token = str_replace("Bearer ", "", $authHeader);

        $decoded = $this->authController->verificarToken($token);
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(["error" => "Token inválido o expirado"]);
            return;
        }

        try {
            $stmt = $this->pdo->query("SELECT * FROM leads");
            $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(["data" => $leads]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Error al obtener datos: " . $e->getMessage()]);
        }
    }

    public function crear() {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data["nombre"]) || !isset($data["nit"]) || !isset($data["ciudad"]) || !isset($data["rtc"])) {
            echo json_encode(["error" => "Faltan datos requeridos"]);
            http_response_code(400);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO leads (nombre, nit,nombrePunto,nombreEquipo, ciudad,promotor, rtc,capitanUusuario, ip, fecha, hora)
                VALUES (:nombre, :nit,:nombrePunto,:nombreEquipo, :ciudad,:promotor, :rtc,:capitanUusuario, :ip, NOW(), NOW())
            ");

            $stmt->execute([
                ":nombre" => $data["nombre"],
                ":nit" => $data["nit"],
                ":nombrePunto" => $data["nombrePunto"], 
                ":nombreEquipo" => $data["nombreEquipo"], 
                ":ciudad" => $data["ciudad"],
                ":promotor" => $data["promotor"], 
                ":rtc" => $data["rtc"],
                ":capitanUusuario" => $data["capitanUusuario"], 
                ":ip" => $this->obtenerIP()
            ]);

            echo json_encode(["message" => "Lead creado exitosamente"]);
            http_response_code(201);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Error al insertar: " . $e->getMessage()]);
            http_response_code(500);
        }
    }
}
?>
