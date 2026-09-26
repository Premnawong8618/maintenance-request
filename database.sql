-- ========================================================
-- ระบบแจ้งซ่อมบำรุงและติดตามสถานะ NU Support Repair
-- Database: maintenance_db
-- ========================================================

CREATE DATABASE IF NOT EXISTS `maintenance_db` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `maintenance_db`;

-- --------------------------------------------------------
-- ตารางหมวดหมู่อุปกรณ์ (Equipment Categories - เฉพาะ 4 รายการ)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `equipment_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `icon` VARCHAR(50) DEFAULT 'wrench',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- ตารางข้อมูลการแจ้งซ่อม (Maintenance Requests)
-- --------------------------------------------------------
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

-- --------------------------------------------------------
-- ข้อมูลเริ่มต้น: หมวดหมู่อุปกรณ์ 4 รายการตามข้อกำหนด
-- --------------------------------------------------------
INSERT INTO `equipment_categories` (`name`, `icon`) VALUES
('คอม', 'monitor'),
('โน็ตบุ๊ค', 'laptop'),
('เครื่องพิม', 'printer'),
('อุปกรณ์เครือข่าย', 'wifi')
ON DUPLICATE KEY UPDATE `icon`=VALUES(`icon`);

-- --------------------------------------------------------
-- ข้อมูลตัวอย่างสำหรับทดสอบระบบ NU Support Repair
-- --------------------------------------------------------
INSERT INTO `maintenance_requests` 
(`tracking_code`, `reporter_name`, `equipment_name`, `equipment_model`, `issue_description`, `department`, `location`, `phone`, `urgency`, `status`, `technician_name`, `technician_notes`, `estimated_completion`, `repair_cost`)
VALUES
('REQ-2026-0001', 'สมชาย ใจดี', 'คอม', 'Dell OptiPlex 7090', 'เปิดเครื่องไม่ติด มีไฟกระพริบสีส้มที่ปุ่ม Power และมีเสียง Beep 3 ครั้ง', 'ฝ่ายบัญชีและการเงิน', 'อาคาร A ชั้น 2 ห้อง 204', '081-234-5678', 'urgent', 'in_progress', 'ช่างวิชัย แสงทอง', 'ตรวจเช็คเบื้องต้นพบปัญหา RAM หลวมและ Power Supply จ่ายไฟไม่นิ่ง กำลังเปลี่ยนอะไหล่', '2026-09-26', 1200.00),

('REQ-2026-0002', 'ศิริพร วงศ์สวัสดิ์', 'เครื่องพิม', 'HP LaserJet Pro MFP M428fdw', 'พิมพ์เอกสารแล้วกระดาษติดบ่อย และมีคราบหมึกเปื้อนเป็นแถบดำด้านข้างกระดาษ', 'ฝ่ายทรัพยากรบุคคล (HR)', 'อาคาร B ชั้น 3 ห้อง 301', '089-876-5432', 'normal', 'completed', 'ช่างกิตติพงษ์ ซ่อมดี', 'ทำความสะอาดชุดลูกยางดึงกระดาษ (Roller) และเปลี่ยนชุดดรัมหมึกใหม่ ทดสอบพิมพ์ 50 แผ่นเรียบร้อย', '2026-09-24', 850.00),

('REQ-2026-0003', 'สมชาย ใจดี', 'โน็ตบุ๊ค', 'Lenovo ThinkPad E14 Gen 4', 'แป้นพิมพ์กดปุ่ม Spacebar และ Enter ไม่ตอบสนอง แบตเตอรี่เริ่มบวม ชาร์จไฟไม่เข้า', 'ฝ่ายบัญชีและการเงิน', 'อาคาร A ชั้น 2 ห้อง 204', '081-234-5678', 'urgent', 'assigned', 'ช่างวิชัย แสงทอง', 'สั่งซื้อแป้นพิมพ์และแบตเตอรี่ใหม่ รออะไหล่จัดส่ง 1-2 วัน', '2026-09-27', 1850.00),

('REQ-2026-0004', 'ประสิทธิ์ รุ่งโรจน์', 'อุปกรณ์เครือข่าย', 'Cisco Catalyst 2960 / Wi-Fi AP', 'สัญญาณอินเทอร์เน็ตหลุดบ่อย ไฟสถานะพอร์ตกระพริบสีส้ม ไม่สามารถเข้า LAN กลางได้', 'ฝ่ายขายและการตลาด', 'อาคาร A ชั้น 4 ห้องประชุมใหญ่', '086-555-1234', 'emergency', 'in_progress', 'ช่างธนพล เครือข่าย', 'ตรวจสอบพบหัว RJ45 หลวมและ Port Switch มีปัญหา กำลังเข้าหัวสายใหม่และสลับ Port', '2026-09-25', 500.00),

('REQ-2026-0005', 'Prem Dev', 'คอม', 'Custom Workstation i7', 'เปิดเครื่องติดแต่ภาพไม่ขึ้นจอ มีเสียงพัดลมการ์ดจอดังมาก', 'ฝ่ายไอที / พัฒนาระบบ', 'อาคาร C ชั้น 3', '088-777-9999', 'normal', 'pending', NULL, 'รอดำเนินการจัดสรรช่าง', '2026-09-28', 0.00)
ON DUPLICATE KEY UPDATE `reporter_name`=VALUES(`reporter_name`);
