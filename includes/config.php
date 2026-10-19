<?php
/**
 * KAK Management System - Configuration File
 * Database configuration for MySQL
 */

// Database Configuration
define('DB_HOST', 'localhost'); define('DB_USER', 'root'); define('DB_PASS', ''); define('DB_NAME', 'kak_management_system');

// Application Settings
define('APP_NAME', 'KAK Management System');
define('APP_TITLE', 'BPJS Ketenagakerjaan');
define('DEFAULT_TIMEZONE', 'Asia/Jakarta');

// Set timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// PDO Connection function
function getPDOConnection() {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';port=3308;dbname=' . DB_NAME . ';charset=utf8mb4';

        $pdo = new PDO(
            $dsn,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        return $pdo;

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection error: ' . $e->getMessage()
        ]);
        exit();
    }
}

// Helper function for JSON responses
function sendResponse($success, $data = [], $message = '') {
    $response = ['success' => $success];
    if ($message) {
        $response['message'] = $message;
    }
    $response = array_merge($response, $data);
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit();
}

// Helper function for error responses
function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit();
}
