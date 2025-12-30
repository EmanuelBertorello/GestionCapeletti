diff --git a/estudio-api/api/login.php b/estudio-api/api/login.php
index 74ad16d12e5c0ba3f070f3c7b91ad3dd15e1d0b6..3470ad10f78abef2a7a9cf43af4d5f9a3726c299 100644
--- a/estudio-api/api/login.php
+++ b/estudio-api/api/login.php
@@ -1,40 +1,34 @@
 <?php
 // api/login.php
 header('Content-Type: application/json');
 
-// CORS para Angular
-header('Access-Control-Allow-Origin: http://localhost:4200');
-header('Access-Control-Allow-Headers: Content-Type, Authorization');
-header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
-
-// Preflight (Angular manda OPTIONS antes de POST)
-if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
-    exit;
-}
+require_once __DIR__ . '/cors.php';
+apply_cors(['GET', 'POST', 'OPTIONS']);
 
 require_once '../db.php'; // $pdo
+$pdo = getPDO();
 
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
