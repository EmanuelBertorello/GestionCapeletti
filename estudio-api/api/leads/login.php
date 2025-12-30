<?php
// api/login.php
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');              // para pruebas con Angular
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// 1) Leer el body JSON (Angular le va a mandar { username, password })
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$username = $data['username'] ?? null;
$password = $data['password'] ?? null;

if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta usuario o contraseña']);
    exit;
}

try {
    $pdo = getPDO();

    // 2) Buscar el usuario
    $sql = "
        SELECT u.id, u.nombre_completo, u.username, u.password_hash,
               r.nombre AS rol
        FROM usuarios u
        JOIN roles r ON u.rol_id = r.id
        WHERE u.username = :user AND u.activo = 1
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user' => $username]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Credenciales incorrectas']);
        exit;
    }

    // 3) Validar contraseña (SHA-256 como en la tabla)
    $hashInput = hash('sha256', $password);
    if ($hashInput !== $user['password_hash']) {
        http_response_code(401);
        echo json_encode(['error' => 'Credenciales incorrectas']);
        exit;
    }

    // 4) Generar token (simple base64 con JSON)
    $payload = [
        'id'       => (int)$user['id'],
        'name'     => $user['nombre_completo'],
        'username' => $user['username'],
        'role'     => $user['rol'],
        'iat'      => time(),
    ];

    $token = base64_encode(json_encode($payload));

    echo json_encode([
        'token' => $token,
        'user'  => [
            'id'       => (int)$user['id'],
            'nombre'  => $user['nombre_completo'],
            'username'=> $user['username'],
            'rol'     => $user['rol'],
        ],
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en el servidor', 'detail' => $e->getMessage()]);
}
