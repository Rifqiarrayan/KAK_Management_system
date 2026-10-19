<?php
/**
 * KAK Management System - Database Class
 * Handles all database operations
 */

class Database {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Initialize database tables
     */
    public function initializeTables() {
        try {
            // Create DATA_PENGADAAN table
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS DATA_PENGADAAN (
                ID_PENGADAAN VARCHAR(50) PRIMARY KEY,
                KODE_KANTOR VARCHAR(50) NOT NULL,
                UNIT_KERJA VARCHAR(100) NOT NULL,
                NM_KEGIATAN_BARU VARCHAR(255) NOT NULL,
                ANGGARAN BIGINT NOT NULL,
                PELAKSANAAN_PENGADAAN VARCHAR(100) NOT NULL,
                STATUS_REVIEW VARCHAR(50) DEFAULT 'BELUM DIREVIEW',
                TANGGAL_INPUT DATE NOT NULL,
                CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UPDATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Create DATA_KAK table
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS DATA_KAK (
                ID_KAK INT AUTO_INCREMENT PRIMARY KEY,
                ID_PENGADAAN VARCHAR(50) NOT NULL UNIQUE,
                Link_data_kak VARCHAR(500),
                CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UPDATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (ID_PENGADAAN) REFERENCES DATA_PENGADAAN(ID_PENGADAAN) ON DELETE CASCADE
            ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Create USER_REVIEWER table
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS USER_REVIEWER (
                USER_ID VARCHAR(50) PRIMARY KEY,
                NAMA_REVIEWER VARCHAR(100) NOT NULL,
                USERNAME VARCHAR(50) NOT NULL UNIQUE,
                PASSWORD VARCHAR(255) NOT NULL,
                ROLE VARCHAR(50) NOT NULL DEFAULT 'Reviewer',
                AKTIF BOOLEAN DEFAULT TRUE,
                CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UPDATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Create HASIL_REVIEW table
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS HASIL_REVIEW (
                ID_REVIEW VARCHAR(100) PRIMARY KEY,
                ID_PENGADAAN VARCHAR(50) NOT NULL,
                BAGIAN_KAK VARCHAR(100) NOT NULL,
                STATUS_REVIEW VARCHAR(50) NOT NULL,
                CATATAN_REVIEW LONGTEXT,
                NAMA_REVIEWER VARCHAR(100) NOT NULL,
                TANGGAL_REVIEW DATETIME,
                BULAN_REVIEW VARCHAR(50),
                REVIEWERS_INVOLVED LONGTEXT,
                CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UPDATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (ID_PENGADAAN) REFERENCES DATA_PENGADAAN(ID_PENGADAAN) ON DELETE CASCADE
            ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // Create Log_Aktivitas table
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS Log_Aktivitas (
                Id_log BIGINT AUTO_INCREMENT PRIMARY KEY,
                User_id VARCHAR(50) NOT NULL,
                Nama_user VARCHAR(100) NOT NULL,
                Role_user VARCHAR(50) NOT NULL,
                Aktivitas VARCHAR(100) NOT NULL,
                Modul VARCHAR(100) NOT NULL,
                Id_referensi VARCHAR(100),
                Deskripsi_aktivitas LONGTEXT,
                Ip_address VARCHAR(50),
                Created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_created_at (Created_at),
                INDEX idx_user (User_id),
                INDEX idx_modul (Modul),
                INDEX idx_aktivitas (Aktivitas)
            ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            return ['success' => true, 'message' => 'Tables initialized successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Authenticate user
     */
    public function authenticateUser($username, $password) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT USER_ID, NAMA_REVIEWER, ROLE, AKTIF 
                FROM USER_REVIEWER 
                WHERE USERNAME = :username AND PASSWORD = :password
            ");
            
            $stmt->execute([':username' => $username, ':password' => $password]);
            $user = $stmt->fetch();
            
            if ($user && $user['AKTIF']) {
                return [
                    'success' => true,
                    'userId' => $user['USER_ID'],
                    'namaReviewer' => $user['NAMA_REVIEWER'],
                    'role' => $user['ROLE']
                ];
            }
            
            return ['success' => false, 'message' => 'Username atau password salah'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Add new pengadaan
     */
    public function addPengadaan($kodKantor, $unitKerja, $nmKegiatan, $anggaran, $pelaksanaan) {
        try {
            $idPengadaan = 'PG-' . time();
            $tanggalInput = date('Y-m-d');
            
            $stmt = $this->pdo->prepare("
                INSERT INTO DATA_PENGADAAN 
                (ID_PENGADAAN, KODE_KANTOR, UNIT_KERJA, NM_KEGIATAN_BARU, ANGGARAN, PELAKSANAAN_PENGADAAN, STATUS_REVIEW, TANGGAL_INPUT)
                VALUES (?, ?, ?, ?, ?, ?, 'BELUM DIREVIEW', ?)
            ");
            
            $stmt->execute([$idPengadaan, $kodKantor, $unitKerja, $nmKegiatan, $anggaran, $pelaksanaan, $tanggalInput]);
            
            return ['success' => true, 'message' => 'Data pengadaan berhasil disimpan', 'id' => $idPengadaan];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get pengadaan list
     */
    public function getPengadaanList() {
        try {
            $stmt = $this->pdo->query("
                SELECT * FROM DATA_PENGADAAN ORDER BY TANGGAL_INPUT DESC
            ");
            
            $pengadaanList = $stmt->fetchAll();
            
            // Add completion percentage to each item
            foreach ($pengadaanList as &$item) {
                $item['COMPLETION_PERCENT'] = $this->calculateCompletionPercentage($item['ID_PENGADAAN']);
            }
            
            return $pengadaanList;
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Get pengadaan by ID
     */
    public function getPengadaanById($idPengadaan) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM DATA_PENGADAAN WHERE ID_PENGADAAN = ?");
            $stmt->execute([$idPengadaan]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Update pengadaan
     */
    public function updatePengadaan($idPengadaan, $kodKantor, $unitKerja, $nmKegiatan, $anggaran, $pelaksanaan) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE DATA_PENGADAAN 
                SET KODE_KANTOR = ?, UNIT_KERJA = ?, NM_KEGIATAN_BARU = ?, ANGGARAN = ?, PELAKSANAAN_PENGADAAN = ?
                WHERE ID_PENGADAAN = ?
            ");
            
            $stmt->execute([$kodKantor, $unitKerja, $nmKegiatan, $anggaran, $pelaksanaan, $idPengadaan]);
            
            return ['success' => true, 'message' => 'Data pengadaan berhasil diperbarui'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Delete pengadaan and related data
     */
    public function deletePengadaan($idPengadaan) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM DATA_PENGADAAN WHERE ID_PENGADAAN = ?");
            $stmt->execute([$idPengadaan]);
            
            return ['success' => true, 'message' => 'Pengadaan dan data terkait berhasil dihapus'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Save KAK
     */
    public function saveKAK($idPengadaan, $kakData) {
        try {
            $stmt = $this->pdo->prepare("SELECT ID_PENGADAAN FROM DATA_KAK WHERE ID_PENGADAAN = ?");
            $stmt->execute([$idPengadaan]);
            $exists = $stmt->fetch();
            
            $linkDataKak = $kakData['Link_data_kak'] ?? ($kakData['TUJUAN'] ?? null);

            if ($exists) {
                // Update existing
                $stmt = $this->pdo->prepare("
                    UPDATE DATA_KAK 
                    SET Link_data_kak = ?
                    WHERE ID_PENGADAAN = ?
                ");
                
                $stmt->execute([
                    $linkDataKak,
                    $idPengadaan
                ]);
            } else {
                // Insert new
                $stmt = $this->pdo->prepare("
                    INSERT INTO DATA_KAK 
                    (ID_PENGADAAN, Link_data_kak)
                    VALUES (?, ?)
                ");
                
                $stmt->execute([
                    $idPengadaan,
                    $linkDataKak
                ]);
            }
            
            return ['success' => true, 'message' => 'KAK berhasil disimpan'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get KAK by pengadaan ID
     */
    public function getKAKByPengadaanId($idPengadaan) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM DATA_KAK WHERE ID_PENGADAAN = ?");
            $stmt->execute([$idPengadaan]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Get KAK fields
     */
    public function getKAKFields() {
        return [
            'TUJUAN',
            'RUANG_LINGKUP_PEKERJAAN',
            'KRITERIA_PENYEDIA',
            'HASIL_OUTPUT_PEKERJAAN',
            'JANGKA_WAKTU_PELAKSANAAN',
            'BULAN',
            'ANGGARAN_BIAYA',
            'HAK_CIPTA_DAN_KERAHASIAAN',
            'PENUTUP'
        ];
    }
    
    /**
     * Submit review
     */
    public function submitReview($idPengadaan, $namaReviewer, $reviews, $selectedReviewers) {
        try {
            $tanggalReview = date('Y-m-d H:i:s');
            $reviewerList = implode(', ', (array)$selectedReviewers);
            
            foreach ($reviews as $bagian => $review) {
                $idReview = 'RV-' . time() . '-' . bin2hex(random_bytes(5));
                $bulanValue = ($bagian === 'BULAN' && isset($review['bulan'])) ? $review['bulan'] : '';
                
                $stmt = $this->pdo->prepare("
                    INSERT INTO HASIL_REVIEW 
                    (ID_REVIEW, ID_PENGADAAN, BAGIAN_KAK, STATUS_REVIEW, CATATAN_REVIEW, NAMA_REVIEWER, TANGGAL_REVIEW, BULAN_REVIEW, REVIEWERS_INVOLVED)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $idReview,
                    $idPengadaan,
                    $bagian,
                    $review['status'] ?? null,
                    $review['catatan'] ?? null,
                    $namaReviewer,
                    $tanggalReview,
                    $bulanValue,
                    $reviewerList
                ]);
            }
            
            $this->updateStatusReviewOtomatis($idPengadaan);
            
            return ['success' => true, 'message' => 'Review berhasil disubmit'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get reviews by pengadaan ID
     */
    public function getReviewByPengadaanId($idPengadaan) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM HASIL_REVIEW 
                WHERE ID_PENGADAAN = ? 
                ORDER BY TANGGAL_REVIEW DESC
            ");
            $stmt->execute([$idPengadaan]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Count reviews for a pengadaan
     */
    public function countReviewByPengadaanId($idPengadaan) {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM HASIL_REVIEW WHERE ID_PENGADAAN = ?");
            $stmt->execute([$idPengadaan]);
            return (int)($stmt->fetch()['total'] ?? 0);
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    /**
     * Update status review otomatis
     */
    public function updateStatusReviewOtomatis($idPengadaan) {
        try {
            $reviews = $this->getReviewByPengadaanId($idPengadaan);
            
            if (empty($reviews)) {
                return;
            }
            
            $statusReview = 'DISETUJUI';
            foreach ($reviews as $review) {
                if (strtoupper($review['STATUS_REVIEW']) === 'PERLU REVISI') {
                    $statusReview = 'PERLU REVISI';
                    break;
                }
            }
            
            $stmt = $this->pdo->prepare("UPDATE DATA_PENGADAAN SET STATUS_REVIEW = ? WHERE ID_PENGADAAN = ?");
            $stmt->execute([$statusReview, $idPengadaan]);
        } catch (PDOException $e) {
            // Log error silently
        }
    }
    
    /**
     * Calculate completion percentage
     */
    public function calculateCompletionPercentage($idPengadaan) {
        try {
            $reviews = $this->getReviewByPengadaanId($idPengadaan);
            $fields = $this->getKAKFields();
            
            if (empty($reviews) || empty($fields)) {
                return 0;
            }
            
            $weight = 100 / count($fields);
            $statusMap = [];
            
            foreach ($reviews as $review) {
                $key = strtoupper($review['BAGIAN_KAK']);
                $statusMap[$key] = strtoupper($review['STATUS_REVIEW']);
            }
            
            $deduction = 0;
            foreach ($fields as $field) {
                $key = strtoupper($field);
                $status = $statusMap[$key] ?? null;
                if ($status === 'PERLU REVISI') {
                    $deduction += $weight;
                }
            }
            
            $percent = max(0, 100 - $deduction);
            return round($percent * 10) / 10;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Add reviewer
     */
    public function addReviewer($userId, $namaReviewer, $username, $password, $aktif, $role) {
        try {
            if (!$userId || !$namaReviewer || !$username || !$password) {
                return ['success' => false, 'message' => 'Nama, username, dan password wajib diisi'];
            }
            
            $stmt = $this->pdo->prepare("
                INSERT INTO USER_REVIEWER (USER_ID, NAMA_REVIEWER, USERNAME, PASSWORD, ROLE, AKTIF)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $userId ?: 'USR-' . time(),
                $namaReviewer,
                $username,
                $password,
                $role ?? 'Reviewer',
                $aktif ? 1 : 0
            ]);
            
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update reviewer
     */
    public function updateReviewer($userId, $namaReviewer, $username, $password, $role, $aktif) {
        try {
            if (!$userId || !$namaReviewer || !$username || !$password) {
                return ['success' => false, 'message' => 'Semua field wajib diisi'];
            }
            
            $stmt = $this->pdo->prepare("
                UPDATE USER_REVIEWER 
                SET NAMA_REVIEWER = ?, USERNAME = ?, PASSWORD = ?, ROLE = ?, AKTIF = ?
                WHERE USER_ID = ?
            ");
            
            $stmt->execute([$namaReviewer, $username, $password, $role, $aktif ? 1 : 0, $userId]);
            
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get user reviewer list
     */
    public function getUserReviewerList() {
        try {
            $stmt = $this->pdo->query("SELECT USER_ID, NAMA_REVIEWER, USERNAME, ROLE, AKTIF FROM USER_REVIEWER");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Delete reviewer
     */
    public function deleteReviewer($username) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM USER_REVIEWER WHERE USERNAME = ?");
            $stmt->execute([$username]);
            
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get dashboard data
     */
    public function getDashboardData() {
        try {
            // Count statuses
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM DATA_PENGADAAN");
            $total = $stmt->fetch()['total'] ?? 0;
            
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM DATA_PENGADAAN WHERE STATUS_REVIEW = 'BELUM DIREVIEW'");
            $belumReview = $stmt->fetch()['count'] ?? 0;
            
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM DATA_PENGADAAN WHERE STATUS_REVIEW = 'PERLU REVISI'");
            $perluRevisi = $stmt->fetch()['count'] ?? 0;
            
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM DATA_PENGADAAN WHERE STATUS_REVIEW = 'DISETUJUI'");
            $disetujui = $stmt->fetch()['count'] ?? 0;
            
            // Monthly data
            $stmt = $this->pdo->query("
                SELECT DATE_FORMAT(TANGGAL_INPUT, '%b %Y') as month, COUNT(*) as count 
                FROM DATA_PENGADAAN 
                GROUP BY DATE_FORMAT(TANGGAL_INPUT, '%Y-%m')
                ORDER BY TANGGAL_INPUT DESC
            ");
            $monthlyData = $stmt->fetchAll();
            
            // Count each procurement ID once per reviewer, not each KAK section.
            $stmt = $this->pdo->query("
                SELECT NAMA_REVIEWER as nama, COUNT(DISTINCT ID_PENGADAAN) as count 
                FROM HASIL_REVIEW 
                GROUP BY NAMA_REVIEWER
                ORDER BY count DESC
            ");
            $reviewerData = $stmt->fetchAll();

                        // Count unique procurement IDs reviewed by implementation period.
            $stmt = $this->pdo->query("
                                SELECT dp.PELAKSANAAN_PENGADAAN as month,
                                             COUNT(DISTINCT hr.ID_PENGADAAN) as count
                                FROM DATA_PENGADAAN dp
                                LEFT JOIN HASIL_REVIEW hr ON hr.ID_PENGADAAN = dp.ID_PENGADAAN
                                WHERE dp.PELAKSANAAN_PENGADAAN IS NOT NULL
                                    AND dp.PELAKSANAAN_PENGADAAN != ''
                                GROUP BY dp.PELAKSANAAN_PENGADAAN
                                ORDER BY MIN(dp.TANGGAL_INPUT) ASC
            ");
            $reviewProgressData = $stmt->fetchAll();

            $stmt = $this->pdo->query("
                SELECT ID_PENGADAAN as idPengadaan,
                       NM_KEGIATAN_BARU as namaKegiatan,
                       STATUS_REVIEW as statusReview,
                       TANGGAL_INPUT as tanggalInput,
                       UPDATED_AT as updatedAt
                FROM DATA_PENGADAAN
                WHERE STATUS_REVIEW IN ('BELUM DIREVIEW', 'PERLU REVISI')
                ORDER BY
                    CASE STATUS_REVIEW
                        WHEN 'PERLU REVISI' THEN 1
                        WHEN 'BELUM DIREVIEW' THEN 2
                        ELSE 3
                    END,
                    UPDATED_AT DESC
                LIMIT 10
            ");
            $actionRequired = $stmt->fetchAll();
            
            return [
                'total' => $total,
                'belumReview' => $belumReview,
                'perluRevisi' => $perluRevisi,
                'disetujui' => $disetujui,
                'monthlyData' => $monthlyData,
                'reviewerData' => $reviewerData,
                'reviewProgressData' => $reviewProgressData,
                'actionRequired' => $actionRequired
            ];
        } catch (PDOException $e) {
            return [
                'total' => 0,
                'belumReview' => 0,
                'perluRevisi' => 0,
                'disetujui' => 0,
                'monthlyData' => [],
                'reviewerData' => [],
                'reviewProgressData' => [],
                'actionRequired' => []
            ];
        }
    }
    
    /**
     * Get filter options for pengadaan
     */
    public function getFilterOptions() {
        try {
            $kodeKantor = $this->pdo->query("SELECT DISTINCT KODE_KANTOR FROM DATA_PENGADAAN WHERE KODE_KANTOR IS NOT NULL AND KODE_KANTOR != '' ORDER BY KODE_KANTOR")->fetchAll(PDO::FETCH_COLUMN);
            $unitKerja = $this->pdo->query("SELECT DISTINCT UNIT_KERJA FROM DATA_PENGADAAN WHERE UNIT_KERJA IS NOT NULL AND UNIT_KERJA != '' ORDER BY UNIT_KERJA")->fetchAll(PDO::FETCH_COLUMN);
            $pelaksanaan = $this->pdo->query("SELECT DISTINCT PELAKSANAAN_PENGADAAN FROM DATA_PENGADAAN WHERE PELAKSANAAN_PENGADAAN IS NOT NULL AND PELAKSANAAN_PENGADAAN != '' ORDER BY PELAKSANAAN_PENGADAAN")->fetchAll(PDO::FETCH_COLUMN);
            
            return [
                'kodeKantor' => $kodeKantor,
                'unitKerja' => $unitKerja,
                'pelaksanaan' => $pelaksanaan
            ];
        } catch (PDOException $e) {
            return [
                'kodeKantor' => [],
                'unitKerja' => [],
                'pelaksanaan' => []
            ];
        }
    }

    /**
     * Add log activity
     */
    public function addLogActivity($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO Log_Aktivitas
                (User_id, Nama_user, Role_user, Aktivitas, Modul, Id_referensi, Deskripsi_aktivitas, Ip_address)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $data['user_id'] ?? 'UNKNOWN',
                $data['nama_user'] ?? 'Unknown',
                $data['role_user'] ?? 'Unknown',
                $data['aktivitas'] ?? 'Unknown',
                $data['modul'] ?? 'Unknown',
                $data['id_referensi'] ?? null,
                $data['deskripsi_aktivitas'] ?? null,
                $data['ip_address'] ?? null
            ]);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get log activity options
     */
    public function getLogActivityOptions() {
        try {
            $users = $this->pdo->query("SELECT DISTINCT Nama_user FROM Log_Aktivitas WHERE Nama_user IS NOT NULL AND Nama_user != '' ORDER BY Nama_user")->fetchAll(PDO::FETCH_COLUMN);
            $modules = $this->pdo->query("SELECT DISTINCT Modul FROM Log_Aktivitas WHERE Modul IS NOT NULL AND Modul != '' ORDER BY Modul")->fetchAll(PDO::FETCH_COLUMN);
            $activities = $this->pdo->query("SELECT DISTINCT Aktivitas FROM Log_Aktivitas WHERE Aktivitas IS NOT NULL AND Aktivitas != '' ORDER BY Aktivitas")->fetchAll(PDO::FETCH_COLUMN);

            return [
                'users' => $users,
                'modules' => $modules,
                'activities' => $activities
            ];
        } catch (PDOException $e) {
            return [
                'users' => [],
                'modules' => [],
                'activities' => []
            ];
        }
    }

    /**
     * Get log activity list with filters and pagination
     */
    public function getLogActivityList($filters, $page, $pageSize) {
        try {
            $where = [];
            $params = [];

            if (!empty($filters['dateFrom'])) {
                $where[] = 'Created_at >= ?';
                $params[] = $filters['dateFrom'] . ' 00:00:00';
            }
            if (!empty($filters['dateTo'])) {
                $where[] = 'Created_at <= ?';
                $params[] = $filters['dateTo'] . ' 23:59:59';
            }
            if (!empty($filters['user'])) {
                $where[] = 'Nama_user = ?';
                $params[] = $filters['user'];
            }
            if (!empty($filters['module'])) {
                $where[] = 'Modul = ?';
                $params[] = $filters['module'];
            }
            if (!empty($filters['activity'])) {
                $where[] = 'Aktivitas = ?';
                $params[] = $filters['activity'];
            }
            if (!empty($filters['search'])) {
                $where[] = "(Nama_user LIKE ? OR Role_user LIKE ? OR Aktivitas LIKE ? OR Modul LIKE ? OR Id_referensi LIKE ? OR Deskripsi_aktivitas LIKE ? OR Ip_address LIKE ?)";
                $search = '%' . $filters['search'] . '%';
                $params = array_merge($params, [$search, $search, $search, $search, $search, $search, $search]);
            }

            $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
            $offset = max(0, ($page - 1) * $pageSize);

            $countStmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM Log_Aktivitas $whereSql");
            $countStmt->execute($params);
            $total = (int)($countStmt->fetch()['total'] ?? 0);

            $stmt = $this->pdo->prepare("SELECT * FROM Log_Aktivitas $whereSql ORDER BY Created_at DESC LIMIT ? OFFSET ?");
            foreach ($params as $index => $value) {
                $stmt->bindValue($index + 1, $value);
            }
            $stmt->bindValue(count($params) + 1, (int)$pageSize, PDO::PARAM_INT);
            $stmt->bindValue(count($params) + 2, (int)$offset, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'items' => $stmt->fetchAll(),
                'total' => $total
            ];
        } catch (PDOException $e) {
            return [
                'items' => [],
                'total' => 0
            ];
        }
    }

    /**
     * Get log activity list for export
     */
    public function getLogActivityListForExport($filters) {
        try {
            $where = [];
            $params = [];

            if (!empty($filters['dateFrom'])) {
                $where[] = 'Created_at >= ?';
                $params[] = $filters['dateFrom'] . ' 00:00:00';
            }
            if (!empty($filters['dateTo'])) {
                $where[] = 'Created_at <= ?';
                $params[] = $filters['dateTo'] . ' 23:59:59';
            }
            if (!empty($filters['user'])) {
                $where[] = 'Nama_user = ?';
                $params[] = $filters['user'];
            }
            if (!empty($filters['module'])) {
                $where[] = 'Modul = ?';
                $params[] = $filters['module'];
            }
            if (!empty($filters['activity'])) {
                $where[] = 'Aktivitas = ?';
                $params[] = $filters['activity'];
            }
            if (!empty($filters['search'])) {
                $where[] = "(Nama_user LIKE ? OR Role_user LIKE ? OR Aktivitas LIKE ? OR Modul LIKE ? OR Id_referensi LIKE ? OR Deskripsi_aktivitas LIKE ? OR Ip_address LIKE ?)";
                $search = '%' . $filters['search'] . '%';
                $params = array_merge($params, [$search, $search, $search, $search, $search, $search, $search]);
            }

            $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
            $stmt = $this->pdo->prepare("SELECT * FROM Log_Aktivitas $whereSql ORDER BY Created_at DESC");
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Generate PDF for log activity
     */
    public function generateLogActivityPDF($filters) {
        try {
            $logs = $this->getLogActivityListForExport($filters);
            if (empty($logs)) {
                return ['success' => false, 'message' => 'Tidak ada data log untuk diunduh'];
            }

            $html = $this->generateLogActivityPDFHTML($logs, $filters);
            $filename = 'LOG_AKTIVITAS_' . date('Y-m-d') . '.pdf';

            return [
                'success' => true,
                'html' => $html,
                'filename' => $filename
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function generateLogActivityPDFHTML($logs, $filters) {
        $filterSummary = [];
        if (!empty($filters['dateFrom']) || !empty($filters['dateTo'])) {
            $filterSummary[] = 'Tanggal: ' . ($filters['dateFrom'] ?: '-') . ' s/d ' . ($filters['dateTo'] ?: '-');
        }
        if (!empty($filters['user'])) {
            $filterSummary[] = 'User: ' . $filters['user'];
        }
        if (!empty($filters['module'])) {
            $filterSummary[] = 'Modul: ' . $filters['module'];
        }
        if (!empty($filters['activity'])) {
            $filterSummary[] = 'Aktivitas: ' . $filters['activity'];
        }
        if (!empty($filters['search'])) {
            $filterSummary[] = 'Search: ' . $filters['search'];
        }
        $filterText = $filterSummary ? implode(' | ', $filterSummary) : 'Semua data';

        $html = '<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: Arial, sans-serif; margin: 15px; background-color: white; font-size: 11px; }
    .container { max-width: 900px; margin: 0 auto; background-color: white; padding: 20px; }
    h1 { text-align: center; color: #000; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 10px; font-size: 18px; }
    .filters { font-size: 10px; color: #333; margin-bottom: 15px; text-align: center; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th { background-color: #e0e0e0; text-align: left; padding: 8px; border: 1px solid #000; font-weight: bold; font-size: 10px; }
    td { padding: 8px; border: 1px solid #ccc; font-size: 10px; }
    tr:nth-child(even) { background-color: #f8f8f8; }
    .footer { margin-top: 20px; padding-top: 15px; border-top: 1px solid #000; font-size: 9px; color: #333; text-align: center; }
  </style>
</head>
<body>
  <div class="container">
    <h1>LAPORAN LOG AKTIVITAS</h1>
    <div class="filters">' . htmlspecialchars($filterText) . '</div>
    <table>
      <thead>
        <tr>
          <th>Waktu</th>
          <th>User</th>
          <th>Role</th>
          <th>Aktivitas</th>
          <th>Modul</th>
          <th>Referensi</th>
        </tr>
      </thead>
      <tbody>';

        foreach ($logs as $log) {
            $html .= '<tr>
              <td>' . htmlspecialchars($log['Created_at'] ?? '-') . '</td>
              <td>' . htmlspecialchars($log['Nama_user'] ?? '-') . '</td>
              <td>' . htmlspecialchars($log['Role_user'] ?? '-') . '</td>
              <td>' . htmlspecialchars($log['Aktivitas'] ?? '-') . '</td>
              <td>' . htmlspecialchars($log['Modul'] ?? '-') . '</td>
              <td>' . htmlspecialchars($log['Id_referensi'] ?? '-') . '</td>
            </tr>';
        }

        $html .= '</tbody>
    </table>
    <div class="footer">
      <p>Generated on: ' . date('Y-m-d H:i:s') . '</p>
      <p>KAK Management System - BPJS Ketenagakerjaan</p>
    </div>
  </div>
</body>
</html>';

        return $html;
    }
    
    /**
     * Generate comprehensive PDF for all pengadaan and reviews
     */
    public function generateComprehensivePDF() {
        try {
            $pengadaanList = $this->getPengadaanList();
            
            if (empty($pengadaanList)) {
                return ['success' => false, 'message' => 'Tidak ada data pengadaan untuk diunduh'];
            }
            
            $html = $this->generateComprehensivePDFHTML($pengadaanList);
            $filename = 'LAPORAN_LENGKAP_PENGADAAN_' . date('Y-m-d') . '.pdf';
            
            return [
                'success' => true,
                'html' => $html,
                'filename' => $filename
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

        /**
         * Generate PDF for a single pengadaan
         */
        public function generatePengadaanPDF($idPengadaan) {
                try {
                        $pengadaan = $this->getPengadaanById($idPengadaan);
                        if (!$pengadaan) {
                                return ['success' => false, 'message' => 'Data pengadaan tidak ditemukan'];
                        }

                        $kak = $this->getKAKByPengadaanId($idPengadaan);
                        if (!$kak) {
                                return ['success' => false, 'message' => 'Data KAK tidak ditemukan'];
                        }

                        $reviews = $this->getReviewByPengadaanId($idPengadaan);

                        $html = $this->generatePengadaanPDFHTML($pengadaan, $kak, $reviews);
                        $filename = 'KAK_' . $idPengadaan . '_' . date('Y-m-d') . '.pdf';

                        return [
                                'success' => true,
                                'html' => $html,
                                'filename' => $filename
                        ];
                } catch (Exception $e) {
                        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
                }
        }

        /**
         * Generate PDF HTML for a single pengadaan
         */
        private function generatePengadaanPDFHTML($pengadaan, $kak, $reviews) {
                $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4; margin: 2.5cm; }
        body { font-family: Arial, Calibri, sans-serif; margin: 0; background: #fff; color: #122B40; font-size: 10.5pt; }
        .container { max-width: 900px; margin: 0 auto; padding-bottom: 35px; }
        .document-header { display: flex; align-items: center; gap: 18px; padding-bottom: 12px; border-bottom: 3px solid #122B40; }
        .document-header img { width: 86px; height: 86px; object-fit: contain; }
        .organization-name { margin: 0; color: #122B40; font-size: 17pt; font-weight: bold; }
        .organization-tagline { margin: 5px 0 0; color: #526575; font-size: 9pt; }
        h1 { text-align: center; color: #122B40; font-size: 20pt; margin: 20px 0 3px; font-weight: bold; }
        .document-subtitle { text-align: center; color: #526575; font-size: 11pt; margin: 0 0 24px; }
        h2 { color: #122B40; margin: 28px 0 12px; border-left: 5px solid #39B44A; padding: 5px 0 5px 12px; font-size: 13pt; page-break-after: avoid; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0 22px; }
        th { background-color: #122B40; color: #fff; text-align: left; padding: 8px 9px; border: .5pt solid #9BAAB4; font-weight: bold; }
        td { padding: 8px 9px; border: .5pt solid #C9D2D8; }
        tbody tr:nth-child(even) { background-color: #F1F7F2; }
        .section-title { font-weight: bold; margin-top: 20px; color: #122B40; }
        .section-content { margin: 8px 0 24px 17px; padding: 10px 12px; border-left: 2px solid #A0D4FF; white-space: pre-wrap; word-wrap: break-word; }
    </style>
</head>
<body>
    <div class="container">
        <div class="document-header">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c3/BPJS_Ketenagakerjaan_logo.svg/3840px-BPJS_Ketenagakerjaan_logo.svg.png" alt="Logo BPJS Ketenagakerjaan">
            <div>
                <p class="organization-name">BPJS Ketenagakerjaan</p>
                <p class="organization-tagline">Deputi Pengadaan | KAK Management System</p>
            </div>
        </div>
        <h1>Laporan KAK Management System</h1>
        <p class="document-subtitle">BPJS Ketenagakerjaan</p>

        <h2>A. Data Pengadaan</h2>
        <table>
            <tr><th style="width:30%;">Field</th><th>Value</th></tr>
            <tr><td>ID Pengadaan</td><td>' . htmlspecialchars($pengadaan['ID_PENGADAAN'] ?? '-') . '</td></tr>
            <tr><td>Kode Kantor</td><td>' . htmlspecialchars($pengadaan['KODE_KANTOR'] ?? '-') . '</td></tr>
            <tr><td>Unit Kerja</td><td>' . htmlspecialchars($pengadaan['UNIT_KERJA'] ?? '-') . '</td></tr>
            <tr><td>Nama Kegiatan</td><td>' . htmlspecialchars($pengadaan['NM_KEGIATAN_BARU'] ?? '-') . '</td></tr>
            <tr><td>Anggaran</td><td>Rp ' . number_format($pengadaan['ANGGARAN'] ?? 0, 0, ',', '.') . '</td></tr>
            <tr><td>Pelaksanaan</td><td>' . htmlspecialchars($pengadaan['PELAKSANAAN_PENGADAAN'] ?? '-') . '</td></tr>
            <tr><td>Status Review</td><td><strong>' . htmlspecialchars($pengadaan['STATUS_REVIEW'] ?? '-') . '</strong></td></tr>
            <tr><td>Tanggal Input</td><td>' . htmlspecialchars($pengadaan['TANGGAL_INPUT'] ?? '-') . '</td></tr>
        </table>

        <h2>B. Detail KAK</h2>
        <div class="section-title">Link Data KAK:</div>
        <div class="section-content">' . htmlspecialchars($kak['TUJUAN'] ?? '-') . '</div>

        <h2>C. Hasil Review</h2>';

                if (empty($reviews)) {
                        $html .= '<p style="color: #999;">Belum ada review</p>';
                } else {
                        $html .= '<table><thead><tr><th>Bagian KAK</th><th>Status</th><th>Bulan</th><th>Catatan</th><th>Reviewer</th><th>Tanggal</th></tr></thead><tbody>';

                        foreach ($reviews as $review) {
                                $reviewerNames = $review['REVIEWERS_INVOLVED'] ?? $review['NAMA_REVIEWER'] ?? '-';
                                $bulanVal = $review['BULAN_REVIEW'] ?? '';
                                $html .= '<tr>
                                    <td>' . htmlspecialchars($review['BAGIAN_KAK'] ?? '-') . '</td>
                                    <td><strong>' . htmlspecialchars($review['STATUS_REVIEW'] ?? '-') . '</strong></td>
                                    <td>' . htmlspecialchars($bulanVal) . '</td>
                                    <td>' . htmlspecialchars($review['CATATAN_REVIEW'] ?? '-') . '</td>
                                    <td>' . htmlspecialchars($reviewerNames) . '</td>
                                    <td>' . htmlspecialchars($review['TANGGAL_REVIEW'] ?? '-') . '</td>
                                </tr>';
                        }

                        $html .= '</tbody></table>';
                }

                $html .= '</div>
</body>
</html>';

                return $html;
        }
    
    /**
     * Generate comprehensive PDF HTML
     */
    private function generateComprehensivePDFHTML($pengadaanList) {
        $html = '<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    @page { size: A4; margin: 2.5cm; }
    body { font-family: Arial, Calibri, sans-serif; margin: 0; background: #fff; color: #122B40; font-size: 10px; }
    .container { max-width: 900px; margin: 0 auto; padding-bottom: 35px; }
    .document-header { display: flex; align-items: center; gap: 18px; padding-bottom: 12px; border-bottom: 3px solid #122B40; }
    .document-header img { width: 86px; height: 86px; object-fit: contain; }
    .organization-name { margin: 0; color: #122B40; font-size: 17px; font-weight: bold; }
    .organization-tagline { margin: 5px 0 0; color: #526575; font-size: 9px; }
    h1 { text-align: center; color: #122B40; margin: 18px 0 3px; font-size: 20px; font-weight: bold; }
    .document-subtitle { text-align: center; color: #526575; font-size: 11px; margin: 0 0 22px; }
    h2 { color: #122B40; margin-top: 26px; margin-bottom: 12px; border-left: 5px solid #39B44A; padding: 5px 0 5px 12px; font-size: 14px; page-break-after: avoid; }
    h3 { color: #122B40; margin-top: 18px; margin-bottom: 10px; font-size: 12px; border-left: 3px solid #A0D4FF; padding: 5px 0 5px 10px; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th { background-color: #122B40; color: #fff; text-align: left; padding: 8px; border: .5pt solid #9BAAB4; font-weight: bold; font-size: 10px; }
    td { padding: 8px; border: .5pt solid #C9D2D8; font-size: 10px; }
    tbody tr:nth-child(even) { background-color: #F1F7F2; }
    .pengadaan-section { margin-bottom: 26px; page-break-inside: avoid; }
  </style>
</head>
<body>
  <div class="container">
        <div class="document-header">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c3/BPJS_Ketenagakerjaan_logo.svg/3840px-BPJS_Ketenagakerjaan_logo.svg.png" alt="Logo BPJS Ketenagakerjaan">
            <div>
                <p class="organization-name">BPJS Ketenagakerjaan</p>
                <p class="organization-tagline">Deputi Pengadaan | KAK Management System</p>
            </div>
        </div>
        <h1>Laporan Lengkap Data Pengadaan dan Hasil Review</h1>
        <p class="document-subtitle">BPJS Ketenagakerjaan</p>
    
    <h2>Ringkasan Data Pengadaan</h2>
    <table>
      <thead>
        <tr>
          <th>ID Pengadaan</th>
          <th>Kode Kantor</th>
          <th>Unit Kerja</th>
          <th>Nama Kegiatan</th>
          <th>Anggaran (Rp)</th>
          <th>Status Review</th>
        </tr>
      </thead>
      <tbody>';
        
        foreach ($pengadaanList as $pengadaan) {
            $budgetFormatted = number_format($pengadaan['ANGGARAN'] ?? 0, 0, ',', '.');
            $html .= '<tr>
              <td><strong>' . htmlspecialchars($pengadaan['ID_PENGADAAN'] ?? '-') . '</strong></td>
              <td>' . htmlspecialchars($pengadaan['KODE_KANTOR'] ?? '-') . '</td>
              <td>' . htmlspecialchars($pengadaan['UNIT_KERJA'] ?? '-') . '</td>
              <td>' . htmlspecialchars($pengadaan['NM_KEGIATAN_BARU'] ?? '-') . '</td>
              <td style="text-align: right;">' . $budgetFormatted . '</td>
              <td><strong>' . htmlspecialchars($pengadaan['STATUS_REVIEW'] ?? '-') . '</strong></td>
            </tr>';
        }
        
        $html .= '</tbody>
    </table>
    
    <h2>Detail Hasil Review per Pengadaan</h2>';
        
        foreach ($pengadaanList as $pengadaan) {
            $reviews = $this->getReviewByPengadaanId($pengadaan['ID_PENGADAAN']);
            $kakFields = $this->getKAKFields();
            
            $html .= '<div class="pengadaan-section">
              <h3>' . htmlspecialchars($pengadaan['ID_PENGADAAN'] ?? '-') . ' - ' . htmlspecialchars($pengadaan['NM_KEGIATAN_BARU'] ?? '-') . ' (Status: ' . htmlspecialchars($pengadaan['STATUS_REVIEW'] ?? 'Belum Direview') . ')</h3>';
            
            if (empty($reviews)) {
                $html .= '<p style="color: #999; margin: 8px 0;">Belum ada review</p>';
            } else {
                $html .= '<table>
                  <thead>
                    <tr style="background-color: #e0e0e0;">
                      <th style="width: 22%;">Bagian KAK</th>
                      <th style="width: 13%;">Status</th>
                      <th style="width: 15%;">Bulan</th>
                      <th style="width: 30%;">Catatan/Keterangan</th>
                      <th style="width: 20%;">Reviewer</th>
                    </tr>
                  </thead>
                  <tbody>';
                
                // Group reviews by BAGIAN_KAK
                $reviewsByKAK = [];
                foreach ($reviews as $review) {
                    $bagian = strtoupper($review['BAGIAN_KAK'] ?? '');
                    if (!isset($reviewsByKAK[$bagian])) {
                        $reviewsByKAK[$bagian] = [];
                    }
                    $reviewsByKAK[$bagian][] = $review;
                }
                
                foreach ($kakFields as $kriteria) {
                    $kakReviews = $reviewsByKAK[strtoupper($kriteria)] ?? [];
                    $kriteriaNama = $this->formatFieldName($kriteria);
                    
                    if (empty($kakReviews)) {
                        $html .= '<tr>
                          <td><strong>' . $kriteriaNama . '</strong></td>
                          <td style="color: #999;">Belum Review</td>
                          <td style="color: #999;">-</td>
                          <td style="color: #999;">-</td>
                          <td style="color: #999;">-</td>
                        </tr>';
                    } else {
                        foreach ($kakReviews as $idx => $review) {
                            $reviewerNames = $review['REVIEWERS_INVOLVED'] ?? $review['NAMA_REVIEWER'] ?? '-';
                            $bulanVal = $review['BULAN_REVIEW'] ?? '';
                            $catatan = substr($review['CATATAN_REVIEW'] ?? '-', 0, 100);
                            
                            $html .= '<tr>';
                            if ($idx === 0) {
                                $html .= '<td rowspan="' . count($kakReviews) . '"><strong>' . $kriteriaNama . '</strong></td>';
                            }
                            $html .= '<td><strong>' . htmlspecialchars($review['STATUS_REVIEW'] ?? '-') . '</strong></td>
                              <td>' . htmlspecialchars($bulanVal) . '</td>
                              <td>' . htmlspecialchars($catatan) . '</td>
                              <td>' . htmlspecialchars($reviewerNames) . '</td>
                            </tr>';
                        }
                    }
                }
                
                $html .= '</tbody>
                </table>';
            }
            
            $html .= '</div>';
        }
        
                $html .= '</div>
    </body>
    </html>';
        
        return $html;
    }
    
    /**
     * Format field name for display
     */
    private function formatFieldName($field) {
        if ($field === 'PENUTUP') return 'Kesimpulan';
        return ucwords(str_replace('_', ' ', strtolower($field)));
    }
}
