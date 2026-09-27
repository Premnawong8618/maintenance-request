<?php
/**
 * config.php
 * ไฟล์กำหนดค่าระบบแจ้งซ่อมบำรุง (Maintenance Request System)
 * NU Support — Configuration File สำหรับ Web Hosting และเซิร์ฟเวอร์ทั่วไป
 */

// โหลดค่าจากไฟล์ .env (ถ้ามี)
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val, " \t\n\r\0\x0B\"'");
            if (getenv($key) === false) {
                putenv("$key=$val");
                $_ENV[$key] = $val;
            }
        }
    }
}

// ============================================================
// Database Configuration (MySQL)
// * สามารถแก้ไขข้อมูลตรงนี้ให้ตรงกับ Web Hosting / cPanel / DirectAdmin ได้ทันที
// ============================================================
define('DB_HOST',     getenv('DB_HOST')     ?: 'localhost');
define('DB_PORT',     getenv('DB_PORT')     ?: '3306');
define('DB_USER',     getenv('DB_USER')     ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
define('DB_NAME',     getenv('DB_NAME')     ?: 'maintenance_db');
define('DB_CHARSET',  'utf8mb4');

// ============================================================
// Server / Application Settings
// ============================================================
define('APP_NAME',    'NU Support — ระบบแจ้งซ่อมบำรุง');
define('APP_ENV',     getenv('APP_ENV') ?: 'production'); // development | production

// ============================================================
// Admin Credentials (สำหรับเข้าสู่ระบบแอดมิน)
// ============================================================
define('ADMIN_USERNAME', getenv('ADMIN_USERNAME') ?: 'abcd');
define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: '1234');
define('ADMIN_TOKEN',    getenv('ADMIN_TOKEN')    ?: 'nu-support-admin-token-2026');

// ============================================================
// File Upload Settings
// ============================================================
define('ROOT_DIR',          dirname(__DIR__));
define('UPLOAD_DIR',        ROOT_DIR . '/uploads/');
define('UPLOAD_MAX_SIZE',   10 * 1024 * 1024); // 10 MB
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('UPLOAD_URL_PREFIX', 'uploads/');

// ตรวจสอบและสร้างโฟลเดอร์ uploads อัตโนมัติหากยังไม่มี
if (!is_dir(UPLOAD_DIR)) {
    @mkdir(UPLOAD_DIR, 0777, true);
}

// โฟลเดอร์สำรองข้อมูลกรณีเชื่อมต่อ MySQL ไม่ได้
define('DATA_DIR', ROOT_DIR . '/data/');
define('LOCAL_STORAGE_FILE', DATA_DIR . 'local_storage.json');
if (!is_dir(DATA_DIR)) {
    @mkdir(DATA_DIR, 0777, true);
}

// ============================================================
// Tracking Code Format
// ============================================================
define('TRACKING_PREFIX', 'REQ');
define('TRACKING_YEAR',   date('Y'));

// ============================================================
// Helper: DSN String for PDO (MySQL)
// ============================================================
function getDSN(bool $includeDb = true): string {
    if ($includeDb) {
        return sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );
    }
    return sprintf(
        'mysql:host=%s;port=%s;charset=%s',
        DB_HOST, DB_PORT, DB_CHARSET
    );
}

// ============================================================
// Helper: PDO Options
// ============================================================
function getPDOOptions(): array {
    return [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];
}
