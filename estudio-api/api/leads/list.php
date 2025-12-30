<?php
// api/leads/list.php
header('Content-Type: application/json');

// CORS para Angular
header('Access-Control-Allow-Origin: http://localhost:4200');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

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
        reingreso,
        localidad,
        provincia,
        cuit_empresa,
        tipo_tramite,
        observaciones,
        captador_asignado
    FROM leads
";

$params = [];

// Regla de negocio:
// - CAPTADOR: solo ve leads donde captador_asignado = su nombre
// - Resto de roles (ABOGADO, ADMINISTRATIVO, SUPER_USUARIO): ven todo
if ($role === 'CAPTADOR') {
    $sql .= " WHERE captador_asignado = :captador";
    $params['captador'] = strtolower($name); 
    // en tu CSV está "matias" en minúscula, por eso usamos strtolower
}

$sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);

// Bindeo de parámetros variables
foreach ($params as $k => $v) {
    $stmt->bindValue(':' . $k, $v);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Respuesta final
echo json_encode([
    "user" => [
        "id"   => $currentUser['id'],
        "name" => $name,
        "role" => $role
    ],
    "count"   => count($data),
    "results" => $data
]);
