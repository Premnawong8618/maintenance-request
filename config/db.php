<?php
/**
 * db.php
 * คลาสและฟังก์ชันจัดการฐานข้อมูล MySQL (PDO) พร้อมระบบ Fallback Local Storage
 * รองรับการใช้งานบน Web Hosting ทุกประเภท 100%
 */

require_once __DIR__ . '/config.php';

class DB {
    private static ?PDO $pdo = null;
    private static bool $isConnected = false;
    private static ?string $errorMessage = null;

    private static array $defaultCategories = [
        ['id' => 1, 'name' => 'คอม', 'icon' => 'monitor'],
        ['id' => 2, 'name' => 'โน็ตบุ๊ค', 'icon' => 'laptop'],
        ['id' => 3, 'name' => 'เครื่องพิม', 'icon' => 'printer'],
        ['id' => 4, 'name' => 'อุปกรณ์เครือข่าย', 'icon' => 'wifi']
    ];

    private static array $defaultRequests = [
        [
            'id' => 1,
            'tracking_code' => 'REQ-2026-0001',
            'reporter_name' => 'สมชาย ใจดี',
            'equipment_name' => 'คอม',
            'equipment_model' => 'Dell OptiPlex 7090',
            'issue_description' => 'เปิดเครื่องไม่ติด มีไฟกระพริบสีส้มที่ปุ่ม Power และมีเสียง Beep 3 ครั้ง',
            'department' => 'ฝ่ายบัญชีและการเงิน',
            'location' => 'อาคาร A ชั้น 2 ห้อง 204',
            'phone' => '081-234-5678',
            'urgency' => 'urgent',
            'status' => 'in_progress',
            'technician_name' => 'ช่างวิชัย แสงทอง',
            'technician_notes' => 'ตรวจเช็คเบื้องต้นพบปัญหา RAM หลวมและ Power Supply จ่ายไฟไม่นิ่ง กำลังเปลี่ยนอะไหล่',
            'estimated_completion' => '2026-09-26',
            'repair_cost' => 1200.00,
            'image_url' => null,
            'created_at' => '2026-09-24 09:30:00',
            'updated_at' => '2026-09-24 14:15:00'
        ],
        [
            'id' => 2,
            'tracking_code' => 'REQ-2026-0002',
            'reporter_name' => 'ศิริพร วงศ์สวัสดิ์',
            'equipment_name' => 'เครื่องพิม',
            'equipment_model' => 'HP LaserJet Pro MFP M428fdw',
            'issue_description' => 'พิมพ์เอกสารแล้วกระดาษติดบ่อย และมีคราบหมึกเปื้อนเป็นแถบดำด้านข้างกระดาษ',
            'department' => 'ฝ่ายทรัพยากรบุคคล (HR)',
            'location' => 'อาคาร B ชั้น 3 ห้อง 301',
            'phone' => '089-876-5432',
            'urgency' => 'normal',
            'status' => 'completed',
            'technician_name' => 'ช่างกิตติพงษ์ ซ่อมดี',
            'technician_notes' => 'ทำความสะอาดชุดลูกยางดึงกระดาษ (Roller) และเปลี่ยนชุดดรัมหมึกใหม่ ทดสอบพิมพ์ 50 แผ่นเรียบร้อย',
            'estimated_completion' => '2026-09-24',
            'repair_cost' => 850.00,
            'image_url' => null,
            'created_at' => '2026-09-23 11:00:00',
            'updated_at' => '2026-09-24 16:30:00'
        ],
        [
            'id' => 3,
            'tracking_code' => 'REQ-2026-0003',
            'reporter_name' => 'ประสิทธิ์ รุ่งโรจน์',
            'equipment_name' => 'อุปกรณ์เครือข่าย',
            'equipment_model' => 'Cisco Catalyst 2960 Switch 24-Port',
            'issue_description' => 'สัญญาณเครือข่ายขัดข้อง พอร์ตเชื่อมต่อ LAN มีไฟสีส้มกระพริบ อินเทอร์เน็ตหลุดทั้งชั้น',
            'department' => 'ฝ่ายขายและการตลาด',
            'location' => 'อาคาร A ชั้น 4 ห้อง Server ประจำชั้น',
            'phone' => '086-555-1234',
            'urgency' => 'emergency',
            'status' => 'assigned',
            'technician_name' => 'ช่างสมหมาย เย็นฉ่ำ',
            'technician_notes' => 'ประสานงานทีมเน็ตเวิร์กเข้าตรวจเช็คคอนฟิกพอร์ตและสายแพทช์พาแนล',
            'estimated_completion' => '2026-09-25',
            'repair_cost' => 1500.00,
            'image_url' => null,
            'created_at' => '2026-09-25 08:15:00',
            'updated_at' => '2026-09-25 09:00:00'
        ],
        [
            'id' => 4,
            'tracking_code' => 'REQ-2026-0004',
            'reporter_name' => 'กานดา แก้วมณี',
            'equipment_name' => 'โน็ตบุ๊ค',
            'equipment_model' => 'Lenovo ThinkPad E14 Gen 4',
            'issue_description' => 'แป้นพิมพ์กดปุ่ม Spacebar และ Enter ไม่ตอบสนอง แบตเตอรี่เริ่มบวม ชาร์จไฟไม่เข้า',
            'department' => 'ฝ่ายการตลาด',
            'location' => 'อาคาร A ชั้น 3 โซน Creative',
            'phone' => '082-345-6789',
            'urgency' => 'urgent',
            'status' => 'pending',
            'technician_name' => null,
            'technician_notes' => 'รอหัวหน้าช่างตรวจสอบและมอบหมายงาน',
            'estimated_completion' => '2026-09-27',
            'repair_cost' => 0.00,
            'image_url' => null,
            'created_at' => '2026-09-25 10:45:00',
            'updated_at' => '2026-09-25 10:45:00'
        ],
        [
            'id' => 5,
            'tracking_code' => 'REQ-2026-0005',
            'reporter_name' => 'ธนพล มั่นคง',
            'equipment_name' => 'คอม',
            'equipment_model' => 'Dell OptiPlex 7080 Micro',
            'issue_description' => 'เปิดเครื่องแล้วภาพไม่ขึ้นบนจอ พัดลมหมุนเสียงดัง มีเสียงเตือนต่อเนื่อง',
            'department' => 'ฝ่ายฝึกอบรม',
            'location' => 'อาคาร C ชั้น 1 ห้องสัมมนา',
            'phone' => '083-999-8877',
            'urgency' => 'normal',
            'status' => 'completed',
            'technician_name' => 'ช่างวิชัย แสงทอง',
            'technician_notes' => 'เปลี่ยนหน่วยความจำ RAM และทำความสะอาดคราบฝุ่น ทดสอบบูต Windows ปกติ',
            'estimated_completion' => '2026-09-22',
            'repair_cost' => 1800.00,
            'image_url' => null,
            'created_at' => '2026-09-21 14:00:00',
            'updated_at' => '2026-09-22 17:00:00'
        ]
    ];

    public static function init(): bool {
        if (self::$pdo !== null) {
            return self::$isConnected;
        }

        try {
            // ลองเชื่อมต่อตรงไปยังฐานข้อมูลก่อน
            self::$pdo = new PDO(getDSN(true), DB_USER, DB_PASSWORD, getPDOOptions());
            self::$isConnected = true;
            self::$errorMessage = null;
            self::ensureTables();
            return true;
        } catch (PDOException $e) {
            // หากฐานข้อมูลยังไม่มี ลองเชื่อมต่อเพื่อสร้างฐานข้อมูล (ถ้า hosting อนุญาต)
            try {
                $serverPdo = new PDO(getDSN(false), DB_USER, DB_PASSWORD, getPDOOptions());
                $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                unset($serverPdo);

                self::$pdo = new PDO(getDSN(true), DB_USER, DB_PASSWORD, getPDOOptions());
                self::$isConnected = true;
                self::$errorMessage = null;
                self::ensureTables();
                return true;
            } catch (Exception $ex) {
                self::$isConnected = false;
                self::$errorMessage = $e->getMessage();
                self::ensureLocalData();
                return false;
            }
        }
    }

    private static function ensureTables(): void {
        if (!self::$isConnected || !self::$pdo) return;

        try {
            self::$pdo->exec("
                CREATE TABLE IF NOT EXISTS `equipment_categories` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(100) NOT NULL UNIQUE,
                    `icon` VARCHAR(50) DEFAULT 'wrench',
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            self::$pdo->exec("
                CREATE TABLE IF NOT EXISTS `maintenance_requests` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `tracking_code` VARCHAR(30) NOT NULL UNIQUE,
                    `reporter_name` VARCHAR(150) NOT NULL,
                    `equipment_name` VARCHAR(150) NOT NULL,
                    `equipment_model` VARCHAR(150) NOT NULL,
                    `issue_description` TEXT NOT NULL,
                    `department` VARCHAR(100) DEFAULT 'ทั่วไป',
                    `location` VARCHAR(150) DEFAULT 'สำนักงาน',
                    `phone` VARCHAR(30) DEFAULT '',
                    `urgency` ENUM('normal', 'urgent', 'emergency') DEFAULT 'normal',
                    `status` ENUM('pending', 'assigned', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
                    `technician_name` VARCHAR(150) DEFAULT NULL,
                    `technician_notes` TEXT DEFAULT NULL,
                    `estimated_completion` DATE DEFAULT NULL,
                    `repair_cost` DECIMAL(10,2) DEFAULT 0.00,
                    `image_url` VARCHAR(255) DEFAULT NULL,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_tracking` (`tracking_code`),
                    INDEX `idx_status` (`status`),
                    INDEX `idx_equipment` (`equipment_name`),
                    INDEX `idx_created` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // ตรวจสอบและลงข้อมูล Categories เริ่มต้น
            $stmt = self::$pdo->query("SELECT COUNT(*) FROM `equipment_categories`");
            if ($stmt && (int)$stmt->fetchColumn() === 0) {
                $insCat = self::$pdo->prepare("INSERT INTO `equipment_categories` (`name`, `icon`) VALUES (?, ?)");
                foreach (self::$defaultCategories as $c) {
                    $insCat->execute([$c['name'], $c['icon']]);
                }
            }

            // ตรวจสอบและลงข้อมูลตัวอย่าง 5 รายการหากยังไม่มีข้อมูล
            $stmtReq = self::$pdo->query("SELECT COUNT(*) FROM `maintenance_requests`");
            if ($stmtReq && (int)$stmtReq->fetchColumn() === 0) {
                $insReq = self::$pdo->prepare("
                    INSERT INTO `maintenance_requests` 
                    (`tracking_code`, `reporter_name`, `equipment_name`, `equipment_model`, `issue_description`, `department`, `location`, `phone`, `urgency`, `status`, `technician_name`, `technician_notes`, `estimated_completion`, `repair_cost`, `created_at`, `updated_at`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                foreach (self::$defaultRequests as $r) {
                    $insReq->execute([
                        $r['tracking_code'],
                        $r['reporter_name'],
                        $r['equipment_name'],
                        $r['equipment_model'],
                        $r['issue_description'],
                        $r['department'],
                        $r['location'],
                        $r['phone'],
                        $r['urgency'],
                        $r['status'],
                        $r['technician_name'],
                        $r['technician_notes'],
                        $r['estimated_completion'],
                        $r['repair_cost'],
                        $r['created_at'],
                        $r['updated_at']
                    ]);
                }
            }
        } catch (Exception $e) {
            error_log("Table initialization error: " . $e->getMessage());
        }
    }

    // ==========================================
    // Local JSON Fallback Helpers
    // ==========================================
    private static function ensureLocalData(): array {
        if (!file_exists(LOCAL_STORAGE_FILE)) {
            $initial = [
                'categories' => self::$defaultCategories,
                'requests' => self::$defaultRequests,
                'nextId' => 6
            ];
            @file_put_contents(LOCAL_STORAGE_FILE, json_encode($initial, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return $initial;
        }
        $content = @file_get_contents(LOCAL_STORAGE_FILE);
        $data = json_decode($content, true);
        if (!is_array($data) || empty($data['requests'])) {
            $data = [
                'categories' => self::$defaultCategories,
                'requests' => self::$defaultRequests,
                'nextId' => 6
            ];
            @file_put_contents(LOCAL_STORAGE_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        return $data;
    }

    private static function saveLocalData(array $data): void {
        @file_put_contents(LOCAL_STORAGE_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    // ==========================================
    // Operations
    // ==========================================

    public static function getStats(): array {
        self::init();
        if (self::$isConnected && self::$pdo) {
            try {
                $sql = "
                    SELECT 
                        COUNT(*) AS total,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                        SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) AS assigned,
                        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled,
                        SUM(CASE WHEN urgency = 'emergency' THEN 1 ELSE 0 END) AS emergency
                    FROM `maintenance_requests`
                ";
                $row = self::$pdo->query($sql)->fetch();
                if ($row) {
                    return [
                        'total' => (int)($row['total'] ?? 0),
                        'pending' => (int)($row['pending'] ?? 0),
                        'assigned' => (int)($row['assigned'] ?? 0),
                        'in_progress' => (int)($row['in_progress'] ?? 0),
                        'completed' => (int)($row['completed'] ?? 0),
                        'cancelled' => (int)($row['cancelled'] ?? 0),
                        'emergency' => (int)($row['emergency'] ?? 0)
                    ];
                }
            } catch (Exception $e) {
                error_log("getStats error: " . $e->getMessage());
            }
        }

        $data = self::ensureLocalData();
        $list = $data['requests'] ?? [];
        return [
            'total' => count($list),
            'pending' => count(array_filter($list, fn($r) => ($r['status'] ?? '') === 'pending')),
            'assigned' => count(array_filter($list, fn($r) => ($r['status'] ?? '') === 'assigned')),
            'in_progress' => count(array_filter($list, fn($r) => ($r['status'] ?? '') === 'in_progress')),
            'completed' => count(array_filter($list, fn($r) => ($r['status'] ?? '') === 'completed')),
            'cancelled' => count(array_filter($list, fn($r) => ($r['status'] ?? '') === 'cancelled')),
            'emergency' => count(array_filter($list, fn($r) => ($r['urgency'] ?? '') === 'emergency'))
        ];
    }

    public static function getRequests(array $filters = []): array {
        self::init();
        $status = $filters['status'] ?? null;
        $equipment = $filters['equipment'] ?? null;
        $urgency = $filters['urgency'] ?? null;
        $search = $filters['search'] ?? null;
        $limit = isset($filters['limit']) ? (int)$filters['limit'] : 50;
        $offset = isset($filters['offset']) ? (int)$filters['offset'] : 0;

        if (self::$isConnected && self::$pdo) {
            try {
                $sql = "SELECT * FROM `maintenance_requests` WHERE 1=1";
                $params = [];

                if ($status && $status !== 'all') {
                    $sql .= " AND `status` = ?";
                    $params[] = $status;
                }
                if ($equipment && $equipment !== 'all') {
                    $sql .= " AND `equipment_name` = ?";
                    $params[] = $equipment;
                }
                if ($urgency && $urgency !== 'all') {
                    $sql .= " AND `urgency` = ?";
                    $params[] = $urgency;
                }
                if (!empty($search)) {
                    $sql .= " AND (`tracking_code` LIKE ? OR `reporter_name` LIKE ? OR `equipment_model` LIKE ? OR `issue_description` LIKE ? OR `phone` LIKE ?)";
                    $term = "%" . trim($search) . "%";
                    array_push($params, $term, $term, $term, $term, $term);
                }

                $sql .= " ORDER BY `created_at` DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
                $stmt = self::$pdo->prepare($sql);
                $stmt->execute($params);
                return $stmt->fetchAll();
            } catch (Exception $e) {
                error_log("getRequests error: " . $e->getMessage());
            }
        }

        $data = self::ensureLocalData();
        $list = $data['requests'] ?? [];

        if ($status && $status !== 'all') {
            $list = array_values(array_filter($list, fn($r) => ($r['status'] ?? '') === $status));
        }
        if ($equipment && $equipment !== 'all') {
            $list = array_values(array_filter($list, fn($r) => ($r['equipment_name'] ?? '') === $equipment));
        }
        if ($urgency && $urgency !== 'all') {
            $list = array_values(array_filter($list, fn($r) => ($r['urgency'] ?? '') === $urgency));
        }
        if (!empty($search)) {
            $s = mb_strtolower(trim($search), 'UTF-8');
            $list = array_values(array_filter($list, function($r) use ($s) {
                return (
                    str_contains(mb_strtolower($r['tracking_code'] ?? '', 'UTF-8'), $s) ||
                    str_contains(mb_strtolower($r['reporter_name'] ?? '', 'UTF-8'), $s) ||
                    str_contains(mb_strtolower($r['equipment_model'] ?? '', 'UTF-8'), $s) ||
                    str_contains(mb_strtolower($r['issue_description'] ?? '', 'UTF-8'), $s) ||
                    str_contains($r['phone'] ?? '', $s)
                );
            }));
        }

        usort($list, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        return array_slice($list, $offset, $limit);
    }

    public static function searchTrackRequests(string $query): array {
        $clean = trim($query);
        if ($clean === '') return [];

        self::init();
        if (self::$isConnected && self::$pdo) {
            try {
                $term = "%" . $clean . "%";
                $sql = "
                    SELECT * FROM `maintenance_requests` 
                    WHERE `tracking_code` = ? 
                       OR `tracking_code` LIKE ?
                       OR `reporter_name` LIKE ? 
                       OR `phone` = ? 
                       OR `phone` LIKE ?
                    ORDER BY 
                      (CASE 
                        WHEN `tracking_code` = ? THEN 1 
                        WHEN `tracking_code` LIKE ? THEN 2
                        WHEN `reporter_name` = ? THEN 3
                        WHEN `reporter_name` LIKE ? THEN 4
                        ELSE 5 
                      END), 
                      `created_at` DESC 
                    LIMIT 25
                ";
                $stmt = self::$pdo->prepare($sql);
                $stmt->execute([$clean, $term, $term, $clean, $term, $clean, $term, $clean, $term]);
                return $stmt->fetchAll();
            } catch (Exception $e) {
                error_log("searchTrackRequests error: " . $e->getMessage());
            }
        }

        $data = self::ensureLocalData();
        $qLower = mb_strtolower($clean, 'UTF-8');
        $qPhone = str_replace('-', '', $clean);

        $matches = array_values(array_filter($data['requests'] ?? [], function($r) use ($qLower, $clean, $qPhone) {
            $codeExact = mb_strtolower($r['tracking_code'] ?? '', 'UTF-8') === $qLower;
            $codePartial = str_contains(mb_strtolower($r['tracking_code'] ?? '', 'UTF-8'), $qLower);
            $nameMatch = str_contains(mb_strtolower($r['reporter_name'] ?? '', 'UTF-8'), $qLower);
            $cleanP = str_replace('-', '', $r['phone'] ?? '');
            $phoneMatch = str_contains($cleanP, $qPhone) || str_contains($r['phone'] ?? '', $clean);
            return $codeExact || $codePartial || $nameMatch || $phoneMatch;
        }));

        usort($matches, function($a, $b) use ($qLower) {
            $aExact = mb_strtolower($a['tracking_code'] ?? '', 'UTF-8') === $qLower;
            $bExact = mb_strtolower($b['tracking_code'] ?? '', 'UTF-8') === $qLower;
            if ($aExact && !$bExact) return -1;
            if (!$aExact && $bExact) return 1;
            return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
        });

        return array_slice($matches, 0, 25);
    }

    public static function getRequestByTrackingCode(string $code): ?array {
        if (empty($code)) return null;
        $results = self::searchTrackRequests($code);
        return !empty($results) ? $results[0] : null;
    }

    public static function createRequest(array $payload): array {
        $year = date('Y');
        $randomSuffix = rand(1000, 9999);
        $trackingCode = sprintf("REQ-%s-%04d", $year, $randomSuffix);
        $now = date('Y-m-d H:i:s');

        $record = [
            'tracking_code' => $trackingCode,
            'reporter_name' => trim($payload['reporter_name'] ?? ''),
            'equipment_name' => trim($payload['equipment_name'] ?? ''),
            'equipment_model' => trim($payload['equipment_model'] ?? ''),
            'issue_description' => trim($payload['issue_description'] ?? ''),
            'department' => trim($payload['department'] ?? 'ทั่วไป') ?: 'ทั่วไป',
            'location' => trim($payload['location'] ?? 'สำนักงาน') ?: 'สำนักงาน',
            'phone' => trim($payload['phone'] ?? ''),
            'urgency' => $payload['urgency'] ?? 'normal',
            'status' => 'pending',
            'technician_name' => null,
            'technician_notes' => null,
            'estimated_completion' => null,
            'repair_cost' => 0.00,
            'image_url' => $payload['image_url'] ?? null,
            'created_at' => $now,
            'updated_at' => $now
        ];

        self::init();
        if (self::$isConnected && self::$pdo) {
            try {
                $sql = "
                    INSERT INTO `maintenance_requests` 
                    (`tracking_code`, `reporter_name`, `equipment_name`, `equipment_model`, `issue_description`, `department`, `location`, `phone`, `urgency`, `status`, `image_url`, `created_at`, `updated_at`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";
                $stmt = self::$pdo->prepare($sql);
                $stmt->execute([
                    $record['tracking_code'],
                    $record['reporter_name'],
                    $record['equipment_name'],
                    $record['equipment_model'],
                    $record['issue_description'],
                    $record['department'],
                    $record['location'],
                    $record['phone'],
                    $record['urgency'],
                    $record['status'],
                    $record['image_url'],
                    $record['created_at'],
                    $record['updated_at']
                ]);
                $record['id'] = (int)self::$pdo->lastInsertId();
                return $record;
            } catch (Exception $e) {
                error_log("createRequest MySQL error: " . $e->getMessage());
            }
        }

        $data = self::ensureLocalData();
        $record['id'] = (int)($data['nextId'] ?? 6);
        $data['nextId'] = $record['id'] + 1;
        array_unshift($data['requests'], $record);
        self::saveLocalData($data);
        return $record;
    }

    public static function updateRequestStatus(string $id, array $updateData): ?array {
        $now = date('Y-m-d H:i:s');
        self::init();

        if (self::$isConnected && self::$pdo) {
            try {
                $fields = [];
                $params = [];

                if (!empty($updateData['status'])) {
                    $fields[] = "`status` = ?";
                    $params[] = $updateData['status'];
                }
                if (isset($updateData['technician_name'])) {
                    $fields[] = "`technician_name` = ?";
                    $params[] = $updateData['technician_name'];
                }
                if (isset($updateData['technician_notes'])) {
                    $fields[] = "`technician_notes` = ?";
                    $params[] = $updateData['technician_notes'];
                }
                if (isset($updateData['estimated_completion'])) {
                    $fields[] = "`estimated_completion` = ?";
                    $params[] = !empty($updateData['estimated_completion']) ? $updateData['estimated_completion'] : null;
                }
                if (isset($updateData['repair_cost'])) {
                    $fields[] = "`repair_cost` = ?";
                    $params[] = (float)$updateData['repair_cost'];
                }

                $fields[] = "`updated_at` = ?";
                $params[] = $now;

                $params[] = $id;

                $sql = "UPDATE `maintenance_requests` SET " . implode(', ', $fields) . " WHERE `id` = ?";
                $stmt = self::$pdo->prepare($sql);
                $stmt->execute($params);

                $getStmt = self::$pdo->prepare("SELECT * FROM `maintenance_requests` WHERE `id` = ?");
                $getStmt->execute([$id]);
                return $getStmt->fetch() ?: null;
            } catch (Exception $e) {
                error_log("updateRequestStatus MySQL error: " . $e->getMessage());
            }
        }

        $data = self::ensureLocalData();
        foreach ($data['requests'] as &$r) {
            if ((string)($r['id'] ?? '') === (string)$id || ($r['tracking_code'] ?? '') === $id) {
                if (!empty($updateData['status'])) $r['status'] = $updateData['status'];
                if (isset($updateData['technician_name'])) $r['technician_name'] = $updateData['technician_name'];
                if (isset($updateData['technician_notes'])) $r['technician_notes'] = $updateData['technician_notes'];
                if (isset($updateData['estimated_completion'])) $r['estimated_completion'] = $updateData['estimated_completion'];
                if (isset($updateData['repair_cost'])) $r['repair_cost'] = (float)$updateData['repair_cost'];
                $r['updated_at'] = $now;
                self::saveLocalData($data);
                return $r;
            }
        }
        return null;
    }

    public static function deleteRequest(string $id): bool {
        self::init();
        if (self::$isConnected && self::$pdo) {
            try {
                $stmt = self::$pdo->prepare("DELETE FROM `maintenance_requests` WHERE `id` = ? OR `tracking_code` = ?");
                $stmt->execute([$id, $id]);
                return $stmt->rowCount() > 0;
            } catch (Exception $e) {
                error_log("deleteRequest MySQL error: " . $e->getMessage());
            }
        }

        $data = self::ensureLocalData();
        $initialCount = count($data['requests']);
        $data['requests'] = array_values(array_filter($data['requests'], function($r) use ($id) {
            return (string)($r['id'] ?? '') !== (string)$id && ($r['tracking_code'] ?? '') !== $id;
        }));

        if (count($data['requests']) !== $initialCount) {
            self::saveLocalData($data);
            return true;
        }
        return false;
    }

    public static function getEquipmentCategories(): array {
        self::init();
        if (self::$isConnected && self::$pdo) {
            try {
                $stmt = self::$pdo->query("SELECT * FROM `equipment_categories` ORDER BY `id` ASC");
                $rows = $stmt->fetchAll();
                if (!empty($rows)) return $rows;
            } catch (Exception $e) {
                error_log("getEquipmentCategories error: " . $e->getMessage());
            }
        }

        $data = self::ensureLocalData();
        return $data['categories'] ?? self::$defaultCategories;
    }

    public static function getStatus(): array {
        self::init();
        return [
            'isMySQLConnected' => self::$isConnected,
            'databaseType' => self::$isConnected ? 'MySQL Server' : 'Local Storage (Fallback)',
            'errorMessage' => self::$errorMessage,
            'config' => [
                'host' => DB_HOST,
                'port' => DB_PORT,
                'user' => DB_USER,
                'database' => DB_NAME
            ]
        ];
    }
}
