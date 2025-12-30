diff --git a/estudio-api/api/leads/list.php b/estudio-api/api/leads/list.php
index 42828f38183be4560987a275aa67d059e22c9043..750c1e645902856c38f3a73dc5d75670bd1d27a2 100644
--- a/estudio-api/api/leads/list.php
+++ b/estudio-api/api/leads/list.php
@@ -1,37 +1,31 @@
 <?php
 // api/leads/list.php
 header('Content-Type: application/json');
 
-// CORS para Angular
-header('Access-Control-Allow-Origin: http://localhost:4200');
-header('Access-Control-Allow-Headers: Content-Type, Authorization');
-header('Access-Control-Allow-Methods: GET, OPTIONS');
-
-if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
-    exit;
-}
+require_once __DIR__ . '/../cors.php';
+apply_cors(['GET', 'OPTIONS']);
 
 require_once '../../db.php';           // leads -> api -> raíz
 require_once '../auth_middleware.php'; // leads -> api
 
 // auth_middleware.php deja $currentUser disponible
 $role = $currentUser['role']; // CAPTADOR, ABOGADO, ADMINISTRATIVO, SUPER_USUARIO
 $name = $currentUser['name']; // nombre completo (Matias, Casali, etc.)
 
 // Paginación
 $limit  = isset($_GET['limit'])  ? max(1, min((int)$_GET['limit'], 500)) : 100;
 $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
 
 // Consulta base
 $sql = "
     SELECT
         id,
         nombre,
         descripcion_lesion,
         art,
         cuil,
         tipo_contingencia,
         fecha_accidente,
         edad,
         tipo_lesion,
         diagnostico,
