diff --git a/estudio-api/api/leads/login.php b/estudio-api/api/leads/login.php
index 85fc0e181d1d1e9e2f1edfe344c1a4d796520f69..5c916e2bc09a106e7ccb819775348dacfac89dfb 100644
--- a/estudio-api/api/leads/login.php
+++ b/estudio-api/api/leads/login.php
@@ -1,38 +1,33 @@
 <?php
 // api/login.php
+require_once __DIR__ . '/../cors.php';
+apply_cors(['POST', 'OPTIONS']);
+
 require_once __DIR__ . '/../db.php';
 
 header('Content-Type: application/json; charset=utf-8');
-header('Access-Control-Allow-Origin: *');              // para pruebas con Angular
-header('Access-Control-Allow-Headers: Content-Type, Authorization');
-header('Access-Control-Allow-Methods: POST, OPTIONS');
-
-if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
-    http_response_code(204);
-    exit;
-}
 
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
