<?php
/**
 * api/index.php
 * REST API Router สำหรับระบบแจ้งซ่อมบำรุง
 * รองรับ Web Hosting ทุกรูปแบบทั้ง Apache, Nginx, LiteSpeed, DirectAdmin, cPanel
 */

// ตั้งค่า CORS Header และ Content-Type
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// ตอบรับ Preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

// ดึง Method
$method = $_SERVER['REQUEST_METHOD'];

// ถอดรหัส JSON Body (กรณี PUT/POST ที่ส่งเป็น JSON)
$inputJSON = json_decode(file_get_contents('php://input'), true) ?? [];
$body = array_merge($_POST, $inputJSON);

// ดึง Path/Route
$route = '';
if (isset($_GET['route'])) {
    $route = trim($_GET['route'], '/');
} elseif (!empty($_SERVER['PATH_INFO'])) {
    $route = trim($_SERVER['PATH_INFO'], '/');
} else {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    // ตัด /api ออกจาก URI เพื่อหา endpoint
    $pos = strpos($uri, '/api');
    if ($pos !== false) {
        $sub = substr($uri, $pos + 4);
        $route = trim($sub, '/');
    }
}

// Helper สำหรับตอบกลับ JSON
function jsonResponse($data, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// Helper สำหรับดึง URL รูปภาพ
function getBaseUrl(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $dir = dirname(dirname($script));
    $dir = str_replace('\\', '/', $dir);
    if ($dir === '/') $dir = '';
    return $protocol . $host . $dir;
}

// ==========================================
// API Routing
// ==========================================

try {
    // 1. Admin Login API
    if ($route === 'admin/login' && $method === 'POST') {
        $username = $body['username'] ?? '';
        $password = $body['password'] ?? '';

        if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
            jsonResponse([
                'success' => true,
                'message' => 'เข้าสู่ระบบสำเร็จ',
                'token' => ADMIN_TOKEN,
                'user' => [
                    'username' => ADMIN_USERNAME,
                    'role' => 'admin',
                    'name' => 'ผู้ดูแลระบบ NU Support'
                ]
            ]);
        } else {
            jsonResponse([
                'success' => false,
                'message' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง (Username หรือ Password ผิดพลาด)'
            ], 401);
        }
    }

    // 2. Statistics Overview
    if ($route === 'stats' && $method === 'GET') {
        $stats = DB::getStats();
        jsonResponse([
            'success' => true,
            'data' => $stats
        ]);
    }

    // 3. Search Tracking Status
    if ($route === 'track/search' && $method === 'GET') {
        $q = $_GET['q'] ?? $_GET['query'] ?? '';
        if (trim($q) === '') {
            jsonResponse([
                'success' => false,
                'message' => 'กรุณากรอกรหัสแจ้งซ่อมหรือชื่อผู้แจ้ง'
            ], 400);
        }
        $results = DB::searchTrackRequests(trim($q));
        jsonResponse([
            'success' => true,
            'data' => $results
        ]);
    }

    // 4. Equipment Categories
    if ($route === 'equipment' && $method === 'GET') {
        $cats = DB::getEquipmentCategories();
        jsonResponse([
            'success' => true,
            'data' => $cats
        ]);
    }

    // 5. Database Status
    if ($route === 'db/status' && $method === 'GET') {
        $status = DB::getStatus();
        jsonResponse([
            'success' => true,
            'data' => $status
        ]);
    }

    // 6. Database Reconnect
    if ($route === 'db/reconnect' && $method === 'POST') {
        $host = $body['host'] ?? 'localhost';
        $port = $body['port'] ?? '3306';
        $user = $body['user'] ?? 'root';
        $pass = $body['password'] ?? '';
        $database = $body['database'] ?? 'maintenance_db';

        // ลองเขียนค่าลง .env (ถ้ามีสิทธิ์)
        $envPath = dirname(__DIR__) . '/.env';
        $envContent = "# Server Configuration\nPORT=3000\n\n# MySQL Database Configuration\nDB_HOST={$host}\nDB_PORT={$port}\nDB_USER={$user}\nDB_PASSWORD={$pass}\nDB_NAME={$database}\n";
        @file_put_contents($envPath, $envContent);

        jsonResponse([
            'success' => true,
            'message' => 'บันทึกการตั้งค่าฐานข้อมูลเรียบร้อยแล้ว กรุณารีเฟรชหน้าเว็บ'
        ]);
    }

    // 7. Request Operations (/requests, /requests/{id}, /requests/{id}/status)
    if (strpos($route, 'requests') === 0) {
        $parts = explode('/', $route);
        // $parts[0] = 'requests'
        $idOrCode = $parts[1] ?? null;
        $subAction = $parts[2] ?? null;

        // 7.1 POST /api/requests (สร้างใบแจ้งซ่อมใหม่ พร้อมรูปภาพ)
        if ($method === 'POST' && !$idOrCode) {
            $reporter_name = $body['reporter_name'] ?? '';
            $equipment_name = $body['equipment_name'] ?? '';
            $equipment_model = $body['equipment_model'] ?? '';
            $issue_description = $body['issue_description'] ?? '';

            if (empty($reporter_name) || empty($equipment_name) || empty($equipment_model) || empty($issue_description)) {
                jsonResponse([
                    'success' => false,
                    'message' => 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน: ชื่อผู้แจ้ง, ชื่ออุปกรณ์, ชื่อรุ่น, ปัญหาที่เกิด'
                ], 400);
            }

            // จัดการอัปโหลดรูปภาพ
            $image_url = null;
            if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (in_array($ext, $allowed)) {
                    $uniqueName = time() . '-' . rand(100000000, 999999999) . '.' . $ext;
                    $targetPath = UPLOAD_DIR . $uniqueName;
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $image_url = 'uploads/' . $uniqueName;
                    }
                }
            }

            $newRecord = DB::createRequest([
                'reporter_name' => $reporter_name,
                'equipment_name' => $equipment_name,
                'equipment_model' => $equipment_model,
                'issue_description' => $issue_description,
                'department' => $body['department'] ?? 'ทั่วไป',
                'location' => $body['location'] ?? 'สำนักงาน',
                'phone' => $body['phone'] ?? '',
                'urgency' => $body['urgency'] ?? 'normal',
                'image_url' => $image_url
            ]);

            jsonResponse([
                'success' => true,
                'message' => 'บันทึกการแจ้งซ่อมเรียบร้อยแล้ว',
                'data' => $newRecord
            ], 201);
        }

        // 7.2 GET /api/requests (ดึงรายการแจ้งซ่อมทั้งหมดพร้อม filter)
        if ($method === 'GET' && !$idOrCode) {
            $requests = DB::getRequests($_GET);
            jsonResponse([
                'success' => true,
                'data' => $requests
            ]);
        }

        // 7.3 PUT /api/requests/{id}/status (อัปเดตสถานะงานโดยแอดมิน)
        if (($method === 'PUT' || $method === 'POST') && $idOrCode && $subAction === 'status') {
            $updated = DB::updateRequestStatus($idOrCode, $body);
            if (!$updated) {
                jsonResponse([
                    'success' => false,
                    'message' => 'ไม่พบรายการที่ต้องการอัปเดต'
                ], 404);
            }
            jsonResponse([
                'success' => true,
                'message' => 'อัปเดตสถานะงานแจ้งซ่อมเรียบร้อยแล้ว',
                'data' => $updated
            ]);
        }

        // 7.4 DELETE /api/requests/{id} (ลบรายการแจ้งซ่อม)
        if ($method === 'DELETE' && $idOrCode) {
            $ok = DB::deleteRequest($idOrCode);
            if (!$ok) {
                jsonResponse([
                    'success' => false,
                    'message' => 'ไม่พบรายการที่ต้องการลบ'
                ], 404);
            }
            jsonResponse([
                'success' => true,
                'message' => 'ลบรายการแจ้งซ่อมเรียบร้อยแล้ว'
            ]);
        }

        // 7.5 GET /api/requests/{id_or_code} (ดึงข้อมูลรายการเดียว)
        if ($method === 'GET' && $idOrCode) {
            $item = DB::getRequestByTrackingCode($idOrCode);
            if (!$item) {
                jsonResponse([
                    'success' => false,
                    'message' => 'ไม่พบรายการแจ้งซ่อมที่ระบุ'
                ], 404);
            }
            jsonResponse([
                'success' => true,
                'data' => $item
            ]);
        }
    }

    // Route ไม่ตรงกับที่กำหนด
    jsonResponse([
        'success' => false,
        'message' => 'Endpoint not found: ' . $route
    ], 404);

} catch (Throwable $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Server Error: ' . $e->getMessage()
    ], 500);
}
