<?php
// auth_middleware.php
require_once __DIR__ . '/db.php';

/**
 * Obtiene el token desde el header Authorization: Bearer xxx
 * o desde ?token=xxx en la URL
 */
function getTokenFromRequest(): ?string
{
    // 1) Header Authorization
    $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];

    if (isset($headers['Authorization'])) {
        $auth = $headers['Authorization'];
    } elseif (isset($headers['authorization'])) {
        $auth = $headers['authorization'];
    } else {
        $auth = null;
    }

    if ($auth && stripos($auth, 'Bearer ') === 0) {
        return trim(substr($auth, 7));
    }

    // 2) Parámetro GET ?token=
    if (isset($_GET['token'])) {
        return $_GET['token'];
    }

    return null;
}

/**
 * Verifica que el usuario esté autenticado.
 * Si todo ok, devuelve el array con los datos del usuario.
 * Si falla, corta la ejecución con 401.
 */
function require_auth(): array
{
    header('Content-Type: application/json; charset=utf-8');

    $token = getTokenFromRequest();

    if (!$token) {
        http_response_code(401);
        echo json_encode(['error' => 'Falta token']);
        exit;
    }

    // Nuestro token es un JSON en base64 (sin firma, simple)
    $json = base64_decode($token, true);
    if ($json === false) {
        http_response_code(401);
        echo json_encode(['error' => 'Token inválido']);
        exit;
    }

    $payload = json_decode($json, true);
    if (!is_array($payload) || !isset($payload['id'], $payload['username'], $payload['role'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Token inválido']);
        exit;
    }

    // Podrías hacer validación de expiración si quisieras:
    // if (isset($payload['iat']) && $payload['iat'] < time() - 86400) { ... }

    return $payload; // id, name, username, role, iat
}
