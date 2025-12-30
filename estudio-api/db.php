<?php
// db.php
// ---------- CONFIGURACIÓN ----------
const DB_HOST = 'localhost';
const DB_NAME = 'estudio';
const DB_USER = 'root';
const DB_PASS = '';      // en XAMPP por defecto es vacío

/**
 * Devuelve una instancia única de PDO
 * @return PDO
 */
function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    return $pdo;
}
