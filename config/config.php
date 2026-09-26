<?php
/**
 * config.php
 * ไฟล์กำหนดค่าระบบแจ้งซ่อมบำรุง (Maintenance Request System)
 * NU Support — Configuration File
 */

// ============================================================
// Database Configuration (MySQL)
// ============================================================
define('DB_HOST',     getenv('DB_HOST')     ?: 'localhost');
define('DB_PORT',     getenv('DB_PORT')     ?: '3306');
define('DB_USER',     getenv('DB_USER')     ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_NAME',     getenv('DB_NAME')     ?: 'maintenance_db');
define('DB_CHARSET',  'utf8mb4');

// ============================================================
// Server / Application Settings
// ============================================================
define('APP_NAME',    'NU Support — ระบบแจ้งซ่อมบำรุง');
define('APP_PORT',    getenv('PORT') ?: 3000);
define('APP_ENV',     getenv('APP_ENV') ?: 'development'); // development | production
define('APP_DEBUG',   APP_ENV === 'development');

// ============================================================
// Admin Credentials (เปลี่ยนก่อน deploy จริง)
// ============================================================
define('ADMIN_USERNAME', getenv('ADMIN_USERNAME') ?: 'abcd');
define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: '1234');
define('ADMIN_TOKEN',    getenv('ADMIN_TOKEN')    ?: 'nu-support-admin-token-2026');

// ============================================================
// File Upload Settings
// ============================================================
define('UPLOAD_DIR',        __DIR__ . '/uploads/');
define('UPLOAD_MAX_SIZE',   10 * 1024 * 1024); // 10 MB
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('UPLOAD_URL_PREFIX', '/uploads/');

// ============================================================
// Tracking Code Format
// ============================================================
define('TRACKING_PREFIX', 'REQ');         // ตัวอย่าง: REQ-2026-0001
define('TRACKING_YEAR',   date('Y'));

// ============================================================
// Equipment Categories (Default seed data)
// ============================================================
define('DEFAULT_CATEGORIES', json_encode([
    ['id' => 1, 'name' => 'คอม',              'icon' => 'monitor'],
    ['id' => 2, 'name' => 'โน็ตบุ๊ค',         'icon' => 'laptop'],
    ['id' => 3, 'name' => 'เครื่องพิม',        'icon' => 'printer'],
    ['id' => 4, 'name' => 'อุปกรณ์เครือข่าย', 'icon' => 'wifi'],
]));

// ============================================================
// Status & Urgency Options
// ============================================================
define('VALID_STATUSES', json_encode([
    'pending', 'assigned', 'in_progress', 'completed', 'cancelled'
]));

define('VALID_URGENCIES', json_encode([
    'normal', 'urgent', 'emergency'
]));

// ============================================================
// CORS Settings
// ============================================================
define('CORS_ORIGIN',  '*');      // เปลี่ยนเป็น domain จริงใน production
define('CORS_METHODS', 'GET, POST, PUT, DELETE, OPTIONS');
define('CORS_HEADERS', 'Content-Type, Authorization');

// ============================================================
// Helper: DSN String for PDO (MySQL)
// ============================================================
function getDSN(): string {
    return sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
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
    ];
}
