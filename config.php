<?php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'qurbanku');

// Disable error display in production
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Secure session configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_secure', '0'); // Set to 1 if using HTTPS
    session_start();
}

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            http_response_code(500);
            jsonResponse(['status' => 'error', 'message' => 'Koneksi database gagal.']);
        }
    }
    return $pdo;
}

function jsonResponse($data) {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    echo json_encode($data);
    exit;
}

function safeString($val) {
    return $val === null || $val === false ? '' : trim((string)$val);
}

function safeNumber($val, $default = 0) {
    if ($val === null || $val === '') return (int)$default;
    $clean = str_replace(['.', ','], '', (string)$val);
    if (!is_numeric($clean)) return (int)$default;
    return (int)$clean;
}

function generateId($prefix) {
    return $prefix . '-' . round(microtime(true) * 1000) . '-' . rand(0, 99999);
}

function now() {
    return (int)(microtime(true) * 1000);
}

