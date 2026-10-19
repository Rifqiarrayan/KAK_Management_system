<?php
/**
 * DEBUG FILE - Testing Login API
 * Gunakan untuk troubleshoot masalah login
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/Database.php';

echo "=== DEBUG LOGIN SYSTEM ===\n\n";

// 1. CEK KONEKSI DATABASE
echo "1. TESTING DATABASE CONNECTION:\n";
try {
    $pdo = getPDOConnection();
    echo "   ✓ Database Connected Successfully\n";
} catch (Exception $e) {
    echo "   ✗ Database Connection Failed: " . $e->getMessage() . "\n";
    exit;
}

// 2. CEK USER DATA
echo "\n2. CHECKING USER DATA IN DATABASE:\n";
try {
    $stmt = $pdo->query("SELECT USER_ID, NAMA_REVIEWER, USERNAME, PASSWORD, ROLE, AKTIF FROM USER_REVIEWER");
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "   ✗ NO USERS FOUND IN DATABASE!\n";
        echo "   → Jalankan setup.sql untuk insert sample users\n";
    } else {
        echo "   ✓ Found " . count($users) . " user(s):\n";
        foreach ($users as $user) {
            echo "     - Username: " . $user['USERNAME'] . " | Role: " . $user['ROLE'] . " | Active: " . ($user['AKTIF'] ? 'Yes' : 'No') . "\n";
        }
    }
} catch (Exception $e) {
    echo "   ✗ Error querying users: " . $e->getMessage() . "\n";
}

// 3. CEK LOGIC AUTHENTIKASI
echo "\n3. TESTING AUTHENTICATION LOGIC:\n";
$db = new Database($pdo);

$testUsername = 'admin';
$testPassword = 'admin123';

echo "   Testing credentials: username='$testUsername', password='$testPassword'\n";
$result = $db->authenticateUser($testUsername, $testPassword);
echo "   Result: " . ($result['success'] ? '✓ SUCCESS' : '✗ FAILED') . "\n";
if (isset($result['message'])) {
    echo "   Message: " . $result['message'] . "\n";
}
if ($result['success']) {
    echo "   - User ID: " . $result['userId'] . "\n";
    echo "   - Nama: " . $result['namaReviewer'] . "\n";
    echo "   - Role: " . $result['role'] . "\n";
}

// 4. CEK API ENDPOINT
echo "\n4. TESTING API ENDPOINT:\n";
echo "   Expected URL: http://localhost/KAK_Management_System_(PHP Version)/api/authenticate\n";
echo "   Method: POST\n";
echo "   Data: {\"username\":\"admin\",\"password\":\"admin123\"}\n";

// 5. SIMULASI API CALL
echo "\n5. SIMULATING API CALL:\n";
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/KAK_Management_System_(PHP Version)/api/authenticate';

$testData = ['username' => 'admin', 'password' => 'admin123'];
echo "   POST Data: " . json_encode($testData) . "\n";

$result = $db->authenticateUser($testData['username'], $testData['password']);
echo "   API Response: " . json_encode($result) . "\n";

echo "\n=== DEBUGGING TIPS ===\n";
echo "1. Jika user tidak ditemukan:\n";
echo "   → Buka phpMyAdmin\n";
echo "   → Pilih database 'kak_management_system'\n";
echo "   → Buka tabel 'USER_REVIEWER'\n";
echo "   → Insert data manual atau jalankan setup.sql\n\n";

echo "2. Jika authentication gagal:\n";
echo "   → Cek password yang disimpan di database\n";
echo "   → Pastikan tidak ada spasi atau karakter tambahan\n\n";

echo "3. Jika API tidak merespons:\n";
echo "   → Buka browser console (F12)\n";
echo "   → Lihat tab Network\n";
echo "   → Cek request ke /api/authenticate\n";
echo "   → Lihat response dari server\n\n";

echo "4. Untuk testing API secara langsung:\n";
echo "   → Gunakan Postman atau curl\n";
echo "   → POST ke: http://localhost/KAK_Management_System_(PHP Version)/api/authenticate\n";
echo "   → Body: {\"username\":\"admin\",\"password\":\"admin123\"}\n";
?>
