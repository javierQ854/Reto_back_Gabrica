<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController {
    private $pdo;
    private $key = "secreto_super_seguro"; 

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function login() {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);
        $email = $data["email"] ?? "";
        $password = $data["password"] ?? "";

        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->execute([":email" => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario || !password_verify($password, $usuario["password"])) {
            http_response_code(401);
            echo json_encode(["error" => "Credenciales incorrectas"]);
            return;
        }

        $payload = [
            "id" => $usuario["id"],
            "email" => $usuario["email"],
            "exp" => time() + (60 * 60) // Expira en 1 hora
        ];

        $token = JWT::encode($payload, $this->key, 'HS256');
        echo json_encode(["token" => $token]);
    }

    public function verificarToken($token) {
        try {
            return JWT::decode($token, new Key($this->key, 'HS256'));
        } catch (Exception $e) {
            return null;
        }
    }
}
?>
