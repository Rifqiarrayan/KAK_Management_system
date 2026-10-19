<?php
/**
 * KAK Management System - Main API Router
 * Routes all API requests to appropriate handlers
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';

$pdo = getPDOConnection();
$db = new Database($pdo);

function isAdminRole($role) {
    $role = strtolower(trim((string)$role));
    return $role === 'admin' || $role === 'admin/notulis';
}

// Get request method and endpoint
$method = $_SERVER['REQUEST_METHOD'];

// Get endpoint dari:
// 1. Query parameter ?endpoint=xxx
// 2. URL path /api/xxx (fallback untuk .htaccess routing)
$endpoint = '';

if (isset($_GET['endpoint'])) {
    // Prefer query parameter method
    $endpoint = $_GET['endpoint'];
} else {
    // Fallback: parse dari REQUEST_URI (untuk htaccess routing)
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $apiPos = strpos($requestUri, '/api/');
    if ($apiPos !== false) {
        $endpoint = substr($requestUri, $apiPos + 5); // +5 untuk skip "/api/"
        $endpoint = trim($endpoint, '/');
    }
}

// Debug logging
error_log('[API] METHOD: ' . $method);
error_log('[API] ENDPOINT: ' . $endpoint);
error_log('[API] REQUEST_URI: ' . $_SERVER['REQUEST_URI']);

// Parse input data
$input = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    switch ($endpoint) {
        // Authentication
        case 'authenticate':
            if ($method === 'POST') {
                $username = $input['username'] ?? '';
                $password = $input['password'] ?? '';
                
                if (!$username || !$password) {
                    sendError('Username dan password harus diisi', 400);
                }
                
                $result = $db->authenticateUser($username, $password);
                if (!empty($result['success'])) {
                    $db->addLogActivity([
                        'user_id' => $result['userId'] ?? 'UNKNOWN',
                        'nama_user' => $result['namaReviewer'] ?? $username,
                        'role_user' => $result['role'] ?? 'Unknown',
                        'aktivitas' => 'Login',
                        'modul' => 'Auth',
                        'id_referensi' => $result['userId'] ?? $username,
                        'deskripsi_aktivitas' => 'User login berhasil',
                        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
                    ]);
                }
                sendResponse($result['success'], 
                    [
                        'userId' => $result['userId'] ?? null,
                        'namaReviewer' => $result['namaReviewer'] ?? null,
                        'role' => $result['role'] ?? null
                    ],
                    $result['message'] ?? ''
                );
            }
            break;

        case 'logout':
            if ($method === 'POST') {
                $db->addLogActivity([
                    'user_id' => $input['userId'] ?? 'UNKNOWN',
                    'nama_user' => $input['namaUser'] ?? 'Unknown',
                    'role_user' => $input['roleUser'] ?? 'Unknown',
                    'aktivitas' => 'Logout',
                    'modul' => 'Auth',
                    'id_referensi' => $input['userId'] ?? null,
                    'deskripsi_aktivitas' => 'User logout',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
                ]);
                sendResponse(true, [], 'Logout berhasil');
            }
            break;
        
        // Pengadaan Management
        case 'pengadaan':
            if ($method === 'GET') {
                $pengadaanList = $db->getPengadaanList();
                sendResponse(true, ['pengadaan' => $pengadaanList]);
            } elseif ($method === 'POST') {
                $kodKantor = $input['kodKantor'] ?? '';
                $unitKerja = $input['unitKerja'] ?? '';
                $nmKegiatan = $input['nmKegiatan'] ?? '';
                $anggaran = $input['anggaran'] ?? 0;
                $pelaksanaan = $input['pelaksanaan'] ?? '';
                
                if (!$kodKantor || !$unitKerja || !$nmKegiatan || !$anggaran || !$pelaksanaan) {
                    sendError('Semua field pengadaan harus diisi', 400);
                }
                
                $result = $db->addPengadaan($kodKantor, $unitKerja, $nmKegiatan, $anggaran, $pelaksanaan);
                if (!empty($result['success'])) {
                    $db->addLogActivity([
                        'user_id' => $input['actorUserId'] ?? 'UNKNOWN',
                        'nama_user' => $input['actorNama'] ?? 'Unknown',
                        'role_user' => $input['actorRole'] ?? 'Unknown',
                        'aktivitas' => 'Tambah Pengadaan',
                        'modul' => 'Pengadaan',
                        'id_referensi' => $result['id'] ?? null,
                        'deskripsi_aktivitas' => 'Tambah data pengadaan',
                        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
                    ]);
                }
                sendResponse($result['success'], 
                    ['id' => $result['id'] ?? null],
                    $result['message']
                );
            }
            break;
        
        case 'pengadaan-by-id':
            if ($method === 'GET') {
                $idPengadaan = $input['idPengadaan'] ?? ($_GET['idPengadaan'] ?? '');
                if (!$idPengadaan) {
                    sendError('ID Pengadaan harus diisi', 400);
                }
                
                $pengadaan = $db->getPengadaanById($idPengadaan);
                if ($pengadaan) {
                    sendResponse(true, ['pengadaan' => $pengadaan]);
                } else {
                    sendError('Data pengadaan tidak ditemukan', 404);
                }
            }
            break;
        
        case 'pengadaan-update':
            if ($method === 'POST') {
                $idPengadaan = $input['idPengadaan'] ?? '';
                $kodKantor = $input['kodKantor'] ?? '';
                $unitKerja = $input['unitKerja'] ?? '';
                $nmKegiatan = $input['nmKegiatan'] ?? '';
                $anggaran = $input['anggaran'] ?? 0;
                $pelaksanaan = $input['pelaksanaan'] ?? '';
                
                if (!$idPengadaan) {
                    sendError('ID Pengadaan harus diisi', 400);
                }
                
                $result = $db->updatePengadaan($idPengadaan, $kodKantor, $unitKerja, $nmKegiatan, $anggaran, $pelaksanaan);
                if (!empty($result['success'])) {
                    $db->addLogActivity([
                        'user_id' => $input['actorUserId'] ?? 'UNKNOWN',
                        'nama_user' => $input['actorNama'] ?? 'Unknown',
                        'role_user' => $input['actorRole'] ?? 'Unknown',
                        'aktivitas' => 'Edit Pengadaan',
                        'modul' => 'Pengadaan',
                        'id_referensi' => $idPengadaan,
                        'deskripsi_aktivitas' => 'Edit data pengadaan',
                        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
                    ]);
                }
                sendResponse($result['success'], [], $result['message']);
            }
            break;
        
        case 'pengadaan-delete':
            if ($method === 'POST') {
                $idPengadaan = $input['idPengadaan'] ?? '';
                if (!$idPengadaan) {
                    sendError('ID Pengadaan harus diisi', 400);
                }
                
                $result = $db->deletePengadaan($idPengadaan);
                if (!empty($result['success'])) {
                    $db->addLogActivity([
                        'user_id' => $input['actorUserId'] ?? 'UNKNOWN',
                        'nama_user' => $input['actorNama'] ?? 'Unknown',
                        'role_user' => $input['actorRole'] ?? 'Unknown',
                        'aktivitas' => 'Hapus Pengadaan',
                        'modul' => 'Pengadaan',
                        'id_referensi' => $idPengadaan,
                        'deskripsi_aktivitas' => 'Hapus data pengadaan',
                        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
                    ]);
                }
                sendResponse($result['success'], [], $result['message']);
            }
            break;
        
        // KAK Management
        case 'kak-save':
            if ($method === 'POST') {
                $idPengadaan = $input['idPengadaan'] ?? '';
                $kakData = $input['kakData'] ?? [];
                
                if (!$idPengadaan) {
                    sendError('ID Pengadaan harus diisi', 400);
                }
                
                $result = $db->saveKAK($idPengadaan, $kakData);
                sendResponse($result['success'], [], $result['message']);
            }
            break;
        
        case 'kak-get':
            if ($method === 'GET') {
                $idPengadaan = $input['idPengadaan'] ?? ($_GET['idPengadaan'] ?? '');
                if (!$idPengadaan) {
                    sendError('ID Pengadaan harus diisi', 400);
                }
                
                $kak = $db->getKAKByPengadaanId($idPengadaan);
                if ($kak) {
                    sendResponse(true, ['kak' => $kak]);
                } else {
                    sendResponse(true, ['kak' => null]);
                }
            }
            break;
        
        case 'kak-fields':
            if ($method === 'GET') {
                $fields = $db->getKAKFields();
                sendResponse(true, ['fields' => $fields]);
            }
            break;
        
        // Review Management
        case 'review-submit':
            if ($method === 'POST') {
                $idPengadaan = $input['idPengadaan'] ?? '';
                $namaReviewer = $input['namaReviewer'] ?? '';
                $reviews = $input['reviews'] ?? [];
                $selectedReviewers = $input['selectedReviewers'] ?? [];
                
                if (!$idPengadaan || !$namaReviewer) {
                    sendError('ID Pengadaan dan Nama Reviewer harus diisi', 400);
                }

                $existingReviewCount = $db->countReviewByPengadaanId($idPengadaan);
                $result = $db->submitReview($idPengadaan, $namaReviewer, $reviews, $selectedReviewers);
                if (!empty($result['success'])) {
                    $db->addLogActivity([
                        'user_id' => $input['actorUserId'] ?? 'UNKNOWN',
                        'nama_user' => $input['actorNama'] ?? $namaReviewer,
                        'role_user' => $input['actorRole'] ?? 'Reviewer',
                        'aktivitas' => $existingReviewCount > 0 ? 'Update Review' : 'Submit Review',
                        'modul' => 'Review',
                        'id_referensi' => $idPengadaan,
                        'deskripsi_aktivitas' => 'Review untuk pengadaan ' . $idPengadaan,
                        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
                    ]);
                }
                sendResponse($result['success'], [], $result['message']);
            }
            break;
        
        case 'review-get':
            if ($method === 'GET') {
                $idPengadaan = $input['idPengadaan'] ?? ($_GET['idPengadaan'] ?? '');
                if (!$idPengadaan) {
                    sendError('ID Pengadaan harus diisi', 400);
                }
                
                $reviews = $db->getReviewByPengadaanId($idPengadaan);
                sendResponse(true, ['reviews' => $reviews]);
            }
            break;
        
        // Reviewer Management
        case 'reviewer-add':
            if ($method === 'POST') {
                $result = $db->addReviewer(
                    $input['userId'] ?? '',
                    $input['namaReviewer'] ?? '',
                    $input['username'] ?? '',
                    $input['password'] ?? '',
                    $input['aktif'] ?? true,
                    $input['role'] ?? 'Reviewer'
                );
                if (!empty($result['success'])) {
                    $db->addLogActivity([
                        'user_id' => $input['actorUserId'] ?? 'UNKNOWN',
                        'nama_user' => $input['actorNama'] ?? 'Unknown',
                        'role_user' => $input['actorRole'] ?? 'Unknown',
                        'aktivitas' => 'Tambah User',
                        'modul' => 'User',
                        'id_referensi' => $input['username'] ?? null,
                        'deskripsi_aktivitas' => 'Tambah user baru',
                        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
                    ]);
                }
                sendResponse($result['success'], [], $result['message']);
            }
            break;
        
        case 'reviewer-update':
            if ($method === 'POST') {
                $result = $db->updateReviewer(
                    $input['userId'] ?? '',
                    $input['namaReviewer'] ?? '',
                    $input['username'] ?? '',
                    $input['password'] ?? '',
                    $input['role'] ?? 'Reviewer',
                    $input['aktif'] ?? true
                );
                if (!empty($result['success'])) {
                    $db->addLogActivity([
                        'user_id' => $input['actorUserId'] ?? 'UNKNOWN',
                        'nama_user' => $input['actorNama'] ?? 'Unknown',
                        'role_user' => $input['actorRole'] ?? 'Unknown',
                        'aktivitas' => 'Update User',
                        'modul' => 'User',
                        'id_referensi' => $input['username'] ?? null,
                        'deskripsi_aktivitas' => 'Update data user',
                        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
                    ]);
                }
                sendResponse($result['success'], [], $result['message']);
            }
            break;
        
        case 'reviewer-list':
            if ($method === 'GET') {
                $reviewers = $db->getUserReviewerList();
                sendResponse(true, ['reviewers' => $reviewers]);
            }
            break;
        
        case 'reviewer-delete':
            if ($method === 'POST') {
                $username = $input['username'] ?? '';
                if (!$username) {
                    sendError('Username harus diisi', 400);
                }
                
                $result = $db->deleteReviewer($username);
                if (!empty($result['success'])) {
                    $db->addLogActivity([
                        'user_id' => $input['actorUserId'] ?? 'UNKNOWN',
                        'nama_user' => $input['actorNama'] ?? 'Unknown',
                        'role_user' => $input['actorRole'] ?? 'Unknown',
                        'aktivitas' => 'Hapus User',
                        'modul' => 'User',
                        'id_referensi' => $username,
                        'deskripsi_aktivitas' => 'Hapus user',
                        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
                    ]);
                }
                sendResponse($result['success'], [], $result['message']);
            }
            break;
        
        // Dashboard
        case 'dashboard':
            if ($method === 'GET') {
                $data = $db->getDashboardData();
                sendResponse(true, $data);
            }
            break;
        
        // Filter Options
        case 'filter-options':
            if ($method === 'GET') {
                $options = $db->getFilterOptions();
                sendResponse(true, $options);
            }
            break;

        case 'log-activity-options':
            if ($method === 'GET') {
                $role = $input['role'] ?? ($_GET['role'] ?? '');
                if (!isAdminRole($role)) {
                    sendError('Akses ditolak', 403);
                }

                $options = $db->getLogActivityOptions();
                sendResponse(true, $options);
            }
            break;

        case 'log-activity-list':
            if ($method === 'GET') {
                $role = $input['role'] ?? ($_GET['role'] ?? '');
                if (!isAdminRole($role)) {
                    sendError('Akses ditolak', 403);
                }

                $page = (int)($_GET['page'] ?? 1);
                $pageSize = (int)($_GET['pageSize'] ?? 10);
                $filters = [
                    'dateFrom' => $_GET['dateFrom'] ?? '',
                    'dateTo' => $_GET['dateTo'] ?? '',
                    'user' => $_GET['user'] ?? '',
                    'module' => $_GET['module'] ?? '',
                    'activity' => $_GET['activity'] ?? '',
                    'search' => $_GET['search'] ?? ''
                ];

                $result = $db->getLogActivityList($filters, max(1, $page), max(1, $pageSize));
                sendResponse(true, [
                    'items' => $result['items'],
                    'total' => $result['total'],
                    'page' => max(1, $page),
                    'pageSize' => max(1, $pageSize)
                ]);
            }
            break;

        case 'log-activity-pdf':
            if ($method === 'GET') {
                $role = $input['role'] ?? ($_GET['role'] ?? '');
                if (!isAdminRole($role)) {
                    sendError('Akses ditolak', 403);
                }

                $filters = [
                    'dateFrom' => $_GET['dateFrom'] ?? '',
                    'dateTo' => $_GET['dateTo'] ?? '',
                    'user' => $_GET['user'] ?? '',
                    'module' => $_GET['module'] ?? '',
                    'activity' => $_GET['activity'] ?? '',
                    'search' => $_GET['search'] ?? ''
                ];

                $result = $db->generateLogActivityPDF($filters);
                if ($result['success']) {
                    sendResponse(true, ['html' => $result['html'], 'filename' => $result['filename']]);
                } else {
                    sendError($result['message'], 500);
                }
            }
            break;
        
        // Per-Pengadaan PDF
        case 'pengadaan-pdf':
            if ($method === 'GET') {
                $idPengadaan = $input['idPengadaan'] ?? ($_GET['idPengadaan'] ?? '');
                if (!$idPengadaan) {
                    sendError('ID Pengadaan harus diisi', 400);
                }
                
                $result = $db->generatePengadaanPDF($idPengadaan);
                if ($result['success']) {
                    sendResponse(true, ['html' => $result['html'], 'filename' => $result['filename']]);
                } else {
                    sendError($result['message'], 500);
                }
            }
            break;
        
        // Comprehensive PDF
        case 'comprehensive-pdf':
            if ($method === 'GET') {
                $result = $db->generateComprehensivePDF();
                if ($result['success']) {
                    sendResponse(true, ['html' => $result['html'], 'filename' => $result['filename']]);
                } else {
                    sendError($result['message'], 500);
                }
            }
            break;
        
        // Database initialization
        case 'db-init':
            if ($method === 'POST') {
                $result = $db->initializeTables();
                sendResponse($result['success'], [], $result['message']);
            }
            break;
        
        default:
            sendError('Endpoint tidak ditemukan', 404);
    }
} catch (Exception $e) {
    sendError('Terjadi kesalahan: ' . $e->getMessage(), 500);
}
