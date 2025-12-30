<?php
// api/login.php
header('Content-Type: application/json');

// CORS para Angular
header('Access-Control-Allow-Origin: http://localhost:4200');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

// Preflight (Angular manda OPTIONS antes de POST)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once '../db.php'; // $pdo

// ==== Leer username / password ====

// 1) Si viene JSON (Angular, Postman)
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

// 2) Si no hay JSON, probamos con $_POST
if (!is_array($data)) {
    $data = $_POST;
}

// 3) Como ayuda para pruebas, aceptamos también por GET
$username = $data['username'] ?? ($_GET['username'] ?? null);
$password = $data['password'] ?? ($_GET['password'] ?? null);

if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(["error" => "Falta usuario o contraseña"]);
    exit;
}

try {
    // Buscar usuario por username
    $stmt = $pdo->prepare("
        SELECT 
            u.id,
            u.nombre_completo,
            u.username,
            u.password_hash,
            r.nombre AS rol
        FROM usuarios u
        JOIN roles r ON u.rol_id = r.id
        WHERE u.username = :user
        LIMIT 1
    ");
    $stmt->execute(['user' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Usuario no existe o contraseña incorrecta
    if (!$user || hash('sha256', $password) !== $user['password_hash']) {
        http_response_code(401);
        echo json_encode(["error" => "Credenciales incorrectas"]);
        exit;
    }

    // ==== Generar token simple (base64 de un JSON) ====
    $payload = [
        "id"   => $user['id'],
        "name" => $user['nombre_completo'],
        "role" => $user['rol'],
        "iat"  => time()
    ];

    $token = base64_encode(json_encode($payload));

    // ==== Respuesta ====
    echo json_encode([
        "token" => $token,
        "user"  => [
            "id"     => $user['id'],
            "nombre" => $user['nombre_completo'],
            "rol"    => $user['rol']
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error en el servidor", "detail" => $e->getMessage()]);
}
