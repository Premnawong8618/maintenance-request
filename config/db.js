const mysql = require('mysql2/promise');
const fs = require('fs');
const path = require('path');
require('dotenv').config();

let pool = null;
let isMySQLConnected = false;
let mysqlErrorMessage = null;

const localDataPath = path.join(__dirname, '..', 'data', 'local_storage.json');

// Initial seed data for fallback or fresh database (4 Categories as requested)
const defaultCategories = [
  { id: 1, name: 'คอม', icon: 'monitor' },
  { id: 2, name: 'โน็ตบุ๊ค', icon: 'laptop' },
  { id: 3, name: 'เครื่องพิม', icon: 'printer' },
  { id: 4, name: 'อุปกรณ์เครือข่าย', icon: 'wifi' }
];

const defaultRequests = [
  {
    id: 1,
    tracking_code: 'REQ-2026-0001',
    reporter_name: 'สมชาย ใจดี',
    equipment_name: 'คอม',
    equipment_model: 'Dell OptiPlex 7090',
    issue_description: 'เปิดเครื่องไม่ติด มีไฟกระพริบสีส้มที่ปุ่ม Power และมีเสียง Beep 3 ครั้ง',
    department: 'ฝ่ายบัญชีและการเงิน',
    location: 'อาคาร A ชั้น 2 ห้อง 204',
    phone: '081-234-5678',
    urgency: 'urgent',
    status: 'in_progress',
    technician_name: 'ช่างวิชัย แสงทอง',
    technician_notes: 'ตรวจเช็คเบื้องต้นพบปัญหา RAM หลวมและ Power Supply จ่ายไฟไม่นิ่ง กำลังเปลี่ยนอะไหล่',
    estimated_completion: '2026-09-26',
    repair_cost: 1200.00,
    image_url: null,
    created_at: '2026-09-24 09:30:00',
    updated_at: '2026-09-24 14:15:00'
  },
  {
    id: 2,
    tracking_code: 'REQ-2026-0002',
    reporter_name: 'ศิริพร วงศ์สวัสดิ์',
    equipment_name: 'เครื่องพิม',
    equipment_model: 'HP LaserJet Pro MFP M428fdw',
    issue_description: 'พิมพ์เอกสารแล้วกระดาษติดบ่อย และมีคราบหมึกเปื้อนเป็นแถบดำด้านข้างกระดาษ',
    department: 'ฝ่ายทรัพยากรบุคคล (HR)',
    location: 'อาคาร B ชั้น 3 ห้อง 301',
    phone: '089-876-5432',
    urgency: 'normal',
    status: 'completed',
    technician_name: 'ช่างกิตติพงษ์ ซ่อมดี',
    technician_notes: 'ทำความสะอาดชุดลูกยางดึงกระดาษ (Roller) และเปลี่ยนชุดดรัมหมึกใหม่ ทดสอบพิมพ์ 50 แผ่นเรียบร้อย',
    estimated_completion: '2026-09-24',
    repair_cost: 850.00,
    image_url: null,
    created_at: '2026-09-23 11:00:00',
    updated_at: '2026-09-24 16:30:00'
  },
  {
    id: 3,
    tracking_code: 'REQ-2026-0003',
    reporter_name: 'ประสิทธิ์ รุ่งโรจน์',
    equipment_name: 'อุปกรณ์เครือข่าย',
    equipment_model: 'Cisco Catalyst 2960 Switch 24-Port',
    issue_description: 'สัญญาณเครือข่ายขัดข้อง พอร์ตเชื่อมต่อ LAN มีไฟสีส้มกระพริบ อินเทอร์เน็ตหลุดทั้งชั้น',
    department: 'ฝ่ายขายและการตลาด',
    location: 'อาคาร A ชั้น 4 ห้อง Server ประจำชั้น',
    phone: '086-555-1234',
    urgency: 'emergency',
    status: 'assigned',
    technician_name: 'ช่างสมหมาย เย็นฉ่ำ',
    technician_notes: 'ประสานงานทีมเน็ตเวิร์กเข้าตรวจเช็คคอนฟิกพอร์ตและสายแพทช์พาแนล',
    estimated_completion: '2026-09-25',
    repair_cost: 1500.00,
    image_url: null,
    created_at: '2026-09-25 08:15:00',
    updated_at: '2026-09-25 09:00:00'
  },
  {
    id: 4,
    tracking_code: 'REQ-2026-0004',
    reporter_name: 'กานดา แก้วมณี',
    equipment_name: 'โน็ตบุ๊ค',
    equipment_model: 'Lenovo ThinkPad E14 Gen 4',
    issue_description: 'แป้นพิมพ์กดปุ่ม Spacebar และ Enter ไม่ตอบสนอง แบตเตอรี่เริ่มบวม ชาร์จไฟไม่เข้า',
    department: 'ฝ่ายการตลาด',
    location: 'อาคาร A ชั้น 3 โซน Creative',
    phone: '082-345-6789',
    urgency: 'urgent',
    status: 'pending',
    technician_name: null,
    technician_notes: 'รอหัวหน้าช่างตรวจสอบและมอบหมายงาน',
    estimated_completion: '2026-09-27',
    repair_cost: 0.00,
    image_url: null,
    created_at: '2026-09-25 10:45:00',
    updated_at: '2026-09-25 10:45:00'
  },
  {
    id: 5,
    tracking_code: 'REQ-2026-0005',
    reporter_name: 'ธนพล มั่นคง',
    equipment_name: 'คอม',
    equipment_model: 'Dell OptiPlex 7080 Micro',
    issue_description: 'เปิดเครื่องแล้วภาพไม่ขึ้นบนจอ พัดลมหมุนเสียงดัง มีเสียงเตือนต่อเนื่อง',
    department: 'ฝ่ายฝึกอบรม',
    location: 'อาคาร C ชั้น 1 ห้องสัมมนา',
    phone: '083-999-8877',
    urgency: 'normal',
    status: 'completed',
    technician_name: 'ช่างวิชัย แสงทอง',
    technician_notes: 'เปลี่ยนหน่วยความจำ RAM และทำความสะอาดคราบฝุ่น ทดสอบบูต Windows ปกติ',
    estimated_completion: '2026-09-22',
    repair_cost: 1800.00,
    image_url: null,
    created_at: '2026-09-21 14:00:00',
    updated_at: '2026-09-22 17:00:00'
  }
];

// Helper to read local json data
function readLocalData() {
  if (!fs.existsSync(localDataPath)) {
    const initial = {
      categories: defaultCategories,
      requests: defaultRequests,
      nextId: 6
    };
    fs.writeFileSync(localDataPath, JSON.stringify(initial, null, 2), 'utf8');
    return initial;
  }
  try {
    const raw = fs.readFileSync(localDataPath, 'utf8');
    return JSON.parse(raw);
  } catch (err) {
    console.error('Error reading local data:', err);
    return { categories: defaultCategories, requests: defaultRequests, nextId: 6 };
  }
}

// Helper to write local json data
function writeLocalData(data) {
  fs.writeFileSync(localDataPath, JSON.stringify(data, null, 2), 'utf8');
}

// Initialize MySQL Connection
async function initMySQL(customConfig = null) {
  const config = customConfig || {
    host: process.env.DB_HOST || 'localhost',
    port: parseInt(process.env.DB_PORT || '3306', 10),
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'maintenance_db'
  };

  try {
    console.log(`Connecting to MySQL on ${config.host}:${config.port} as ${config.user}...`);
    // Connect without database first to ensure database exists
    const rootConn = await mysql.createConnection({
      host: config.host,
      port: config.port,
      user: config.user,
      password: config.password
    });

    await rootConn.query(`CREATE DATABASE IF NOT EXISTS \`${config.database}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`);
    await rootConn.end();

    // Now create pool with target database
    pool = mysql.createPool({
      host: config.host,
      port: config.port,
      user: config.user,
      password: config.password,
      database: config.database,
      waitForConnections: true,
      connectionLimit: 10,
      queueLimit: 0,
      dateStrings: true
    });

    // Create tables if not exist
    await pool.query(`
      CREATE TABLE IF NOT EXISTS equipment_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        icon VARCHAR(50) DEFAULT 'wrench',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    `);

    await pool.query(`
      CREATE TABLE IF NOT EXISTS maintenance_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tracking_code VARCHAR(30) NOT NULL UNIQUE,
        reporter_name VARCHAR(150) NOT NULL,
        equipment_name VARCHAR(150) NOT NULL,
        equipment_model VARCHAR(150) NOT NULL,
        issue_description TEXT NOT NULL,
        department VARCHAR(100) DEFAULT 'ทั่วไป',
        location VARCHAR(150) DEFAULT 'สำนักงาน',
        phone VARCHAR(30) DEFAULT '',
        urgency ENUM('normal', 'urgent', 'emergency') DEFAULT 'normal',
        status ENUM('pending', 'assigned', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
        technician_name VARCHAR(150) DEFAULT NULL,
        technician_notes TEXT DEFAULT NULL,
        estimated_completion DATE DEFAULT NULL,
        repair_cost DECIMAL(10,2) DEFAULT 0.00,
        image_url VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tracking (tracking_code),
        INDEX idx_status (status),
        INDEX idx_equipment (equipment_name),
        INDEX idx_created (created_at)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    `);

    // Seed equipment categories if empty
    const [catRows] = await pool.query('SELECT COUNT(*) as count FROM equipment_categories');
    if (catRows[0].count === 0) {
      for (const cat of defaultCategories) {
        await pool.query('INSERT IGNORE INTO equipment_categories (name, icon) VALUES (?, ?)', [cat.name, cat.icon]);
      }
    }

    // Seed sample requests if empty
    const [reqRows] = await pool.query('SELECT COUNT(*) as count FROM maintenance_requests');
    if (reqRows[0].count === 0) {
      for (const r of defaultRequests) {
        await pool.query(
          `INSERT INTO maintenance_requests 
          (tracking_code, reporter_name, equipment_name, equipment_model, issue_description, department, location, phone, urgency, status, technician_name, technician_notes, estimated_completion, repair_cost)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
          [r.tracking_code, r.reporter_name, r.equipment_name, r.equipment_model, r.issue_description, r.department, r.location, r.phone, r.urgency, r.status, r.technician_name, r.technician_notes, r.estimated_completion, r.repair_cost]
        );
      }
    }

    isMySQLConnected = true;
    mysqlErrorMessage = null;
    console.log('✅ Connected to MySQL successfully! Database & Tables ready.');
    return { success: true };
  } catch (err) {
    isMySQLConnected = false;
    mysqlErrorMessage = err.message;
    console.warn('⚠️ MySQL connection note:', err.message);
    console.log('📦 Using high-performance Local Storage mode as fallback so the app runs smoothly.');
    readLocalData(); // Ensure local file is initialized
    return { success: false, error: err.message };
  }
}

// Initial connection attempt
initMySQL();

// ==========================================
// Database Operations (MySQL with fallback)
// ==========================================

async function getStats() {
  if (isMySQLConnected && pool) {
    try {
      const [rows] = await pool.query(`
        SELECT 
          COUNT(*) AS total,
          SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
          SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) AS assigned,
          SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress,
          SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
          SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled,
          SUM(CASE WHEN urgency = 'emergency' THEN 1 ELSE 0 END) AS emergency
        FROM maintenance_requests
      `);
      return rows[0] || { total: 0, pending: 0, assigned: 0, in_progress: 0, completed: 0, cancelled: 0, emergency: 0 };
    } catch (e) {
      console.error('MySQL query error in getStats, fallback to local', e.message);
    }
  }

  // Local fallback
  const data = readLocalData();
  const list = data.requests || [];
  return {
    total: list.length,
    pending: list.filter(r => r.status === 'pending').length,
    assigned: list.filter(r => r.status === 'assigned').length,
    in_progress: list.filter(r => r.status === 'in_progress').length,
    completed: list.filter(r => r.status === 'completed').length,
    cancelled: list.filter(r => r.status === 'cancelled').length,
    emergency: list.filter(r => r.urgency === 'emergency').length
  };
}

async function getRequests(filters = {}) {
  const { status, equipment, urgency, search, limit = 50, offset = 0 } = filters;

  if (isMySQLConnected && pool) {
    try {
      let sql = 'SELECT * FROM maintenance_requests WHERE 1=1';
      const params = [];

      if (status && status !== 'all') {
        sql += ' AND status = ?';
        params.push(status);
      }
      if (equipment && equipment !== 'all') {
        sql += ' AND equipment_name = ?';
        params.push(equipment);
      }
      if (urgency && urgency !== 'all') {
        sql += ' AND urgency = ?';
        params.push(urgency);
      }
      if (search) {
        sql += ' AND (tracking_code LIKE ? OR reporter_name LIKE ? OR equipment_model LIKE ? OR issue_description LIKE ? OR phone LIKE ?)';
        const term = `%${search}%`;
        params.push(term, term, term, term, term);
      }

      sql += ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
      params.push(parseInt(limit, 10), parseInt(offset, 10));

      const [rows] = await pool.query(sql, params);
      return rows;
    } catch (e) {
      console.error('MySQL query error in getRequests, fallback to local', e.message);
    }
  }

  // Local fallback
  const data = readLocalData();
  let list = [...(data.requests || [])];

  if (status && status !== 'all') {
    list = list.filter(r => r.status === status);
  }
  if (equipment && equipment !== 'all') {
    list = list.filter(r => r.equipment_name === equipment);
  }
  if (urgency && urgency !== 'all') {
    list = list.filter(r => r.urgency === urgency);
  }
  if (search) {
    const s = search.toLowerCase();
    list = list.filter(r => 
      (r.tracking_code && r.tracking_code.toLowerCase().includes(s)) ||
      (r.reporter_name && r.reporter_name.toLowerCase().includes(s)) ||
      (r.equipment_model && r.equipment_model.toLowerCase().includes(s)) ||
      (r.issue_description && r.issue_description.toLowerCase().includes(s)) ||
      (r.phone && r.phone.includes(s))
    );
  }

  list.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0));
  return list.slice(offset, offset + limit);
}

async function searchTrackRequests(query) {
  if (!query) return [];
  const clean = query.trim();

  if (isMySQLConnected && pool) {
    try {
      const [rows] = await pool.query(
        `SELECT * FROM maintenance_requests 
         WHERE tracking_code = ? 
            OR tracking_code LIKE ?
            OR reporter_name LIKE ? 
            OR phone = ? 
            OR phone LIKE ?
         ORDER BY 
           (CASE 
             WHEN tracking_code = ? THEN 1 
             WHEN tracking_code LIKE ? THEN 2
             WHEN reporter_name = ? THEN 3
             WHEN reporter_name LIKE ? THEN 4
             ELSE 5 
           END), 
           created_at DESC 
         LIMIT 25`,
        [clean, `%${clean}%`, `%${clean}%`, clean, `%${clean}%`, clean, `%${clean}%`, clean, `%${clean}%`]
      );
      return rows;
    } catch (e) {
      console.error('MySQL query error in searchTrackRequests', e.message);
    }
  }

  // Local fallback
  const data = readLocalData();
  const qLower = clean.toLowerCase();
  const qCleanPhone = clean.replace(/-/g, '');

  const matches = (data.requests || []).filter(r => {
    const codeExact = r.tracking_code && r.tracking_code.toLowerCase() === qLower;
    const codePartial = r.tracking_code && r.tracking_code.toLowerCase().includes(qLower);
    const nameMatch = r.reporter_name && r.reporter_name.toLowerCase().includes(qLower);
    const phoneMatch = r.phone && (r.phone.replace(/-/g, '').includes(qCleanPhone) || r.phone.includes(clean));
    return codeExact || codePartial || nameMatch || phoneMatch;
  });

  matches.sort((a, b) => {
    const aExactCode = a.tracking_code && a.tracking_code.toLowerCase() === qLower;
    const bExactCode = b.tracking_code && b.tracking_code.toLowerCase() === qLower;
    if (aExactCode && !bExactCode) return -1;
    if (!aExactCode && bExactCode) return 1;

    const aExactName = a.reporter_name && a.reporter_name.toLowerCase() === qLower;
    const bExactName = b.reporter_name && b.reporter_name.toLowerCase() === qLower;
    if (aExactName && !bExactName) return -1;
    if (!aExactName && bExactName) return 1;

    return new Date(b.created_at || 0) - new Date(a.created_at || 0);
  });

  return matches;
}

async function getRequestByTrackingCode(code) {
  if (!code) return null;
  const results = await searchTrackRequests(code);
  return results.length > 0 ? results[0] : null;
}

// Generate new unique tracking code: REQ-YYYY-XXXX
function generateTrackingCode() {
  const year = new Date().getFullYear();
  const randomSuffix = Math.floor(1000 + Math.random() * 9000);
  return `REQ-${year}-${randomSuffix}`;
}

async function createRequest(payload) {
  const trackingCode = generateTrackingCode();
  const now = new Date().toISOString().replace('T', ' ').substring(0, 19);

  const newRecord = {
    tracking_code: trackingCode,
    reporter_name: payload.reporter_name.trim(),
    equipment_name: payload.equipment_name.trim(),
    equipment_model: payload.equipment_model.trim(),
    issue_description: payload.issue_description.trim(),
    department: payload.department ? payload.department.trim() : 'ทั่วไป',
    location: payload.location ? payload.location.trim() : 'สำนักงาน',
    phone: payload.phone ? payload.phone.trim() : '',
    urgency: payload.urgency || 'normal',
    status: 'pending',
    technician_name: null,
    technician_notes: null,
    estimated_completion: null,
    repair_cost: 0.00,
    image_url: payload.image_url || null,
    created_at: now,
    updated_at: now
  };

  if (isMySQLConnected && pool) {
    try {
      const [result] = await pool.query(
        `INSERT INTO maintenance_requests 
        (tracking_code, reporter_name, equipment_name, equipment_model, issue_description, department, location, phone, urgency, status, image_url, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          newRecord.tracking_code,
          newRecord.reporter_name,
          newRecord.equipment_name,
          newRecord.equipment_model,
          newRecord.issue_description,
          newRecord.department,
          newRecord.location,
          newRecord.phone,
          newRecord.urgency,
          newRecord.status,
          newRecord.image_url,
          newRecord.created_at,
          newRecord.updated_at
        ]
      );
      newRecord.id = result.insertId;
      return newRecord;
    } catch (e) {
      console.error('MySQL insert error, using local fallback:', e.message);
    }
  }

  // Local fallback
  const data = readLocalData();
  newRecord.id = data.nextId++;
  data.requests.unshift(newRecord);
  writeLocalData(data);
  return newRecord;
}

async function updateRequestStatus(id, updateData) {
  const now = new Date().toISOString().replace('T', ' ').substring(0, 19);

  if (isMySQLConnected && pool) {
    try {
      const fields = [];
      const values = [];

      if (updateData.status) {
        fields.push('status = ?');
        values.push(updateData.status);
      }
      if (updateData.technician_name !== undefined) {
        fields.push('technician_name = ?');
        values.push(updateData.technician_name);
      }
      if (updateData.technician_notes !== undefined) {
        fields.push('technician_notes = ?');
        values.push(updateData.technician_notes);
      }
      if (updateData.estimated_completion !== undefined) {
        fields.push('estimated_completion = ?');
        values.push(updateData.estimated_completion || null);
      }
      if (updateData.repair_cost !== undefined) {
        fields.push('repair_cost = ?');
        values.push(parseFloat(updateData.repair_cost) || 0.00);
      }

      fields.push('updated_at = ?');
      values.push(now);

      values.push(id);

      await pool.query(`UPDATE maintenance_requests SET ${fields.join(', ')} WHERE id = ?`, values);
      const [rows] = await pool.query('SELECT * FROM maintenance_requests WHERE id = ?', [id]);
      return rows[0] || null;
    } catch (e) {
      console.error('MySQL update error, using local fallback:', e.message);
    }
  }

  // Local fallback
  const data = readLocalData();
  const index = data.requests.findIndex(r => r.id === parseInt(id, 10));
  if (index !== -1) {
    if (updateData.status) data.requests[index].status = updateData.status;
    if (updateData.technician_name !== undefined) data.requests[index].technician_name = updateData.technician_name;
    if (updateData.technician_notes !== undefined) data.requests[index].technician_notes = updateData.technician_notes;
    if (updateData.estimated_completion !== undefined) data.requests[index].estimated_completion = updateData.estimated_completion;
    if (updateData.repair_cost !== undefined) data.requests[index].repair_cost = parseFloat(updateData.repair_cost) || 0.00;
    data.requests[index].updated_at = now;
    writeLocalData(data);
    return data.requests[index];
  }
  return null;
}

async function deleteRequest(id) {
  if (isMySQLConnected && pool) {
    try {
      const [result] = await pool.query('DELETE FROM maintenance_requests WHERE id = ?', [id]);
      return result.affectedRows > 0;
    } catch (e) {
      console.error('MySQL delete error:', e.message);
    }
  }

  // Local fallback
  const data = readLocalData();
  const initialLen = data.requests.length;
  data.requests = data.requests.filter(r => r.id !== parseInt(id, 10));
  writeLocalData(data);
  return data.requests.length < initialLen;
}

async function getEquipmentCategories() {
  if (isMySQLConnected && pool) {
    try {
      const [rows] = await pool.query('SELECT * FROM equipment_categories ORDER BY id ASC');
      return rows;
    } catch (e) {
      console.error('MySQL categories error:', e.message);
    }
  }
  const data = readLocalData();
  return data.categories || defaultCategories;
}

function getDbStatus() {
  return {
    isMySQLConnected,
    databaseType: isMySQLConnected ? 'MySQL Server' : 'Local Storage (Fallback)',
    errorMessage: mysqlErrorMessage,
    config: {
      host: process.env.DB_HOST || 'localhost',
      port: process.env.DB_PORT || '3306',
      user: process.env.DB_USER || 'root',
      database: process.env.DB_NAME || 'maintenance_db'
    }
  };
}

module.exports = {
  initMySQL,
  getStats,
  getRequests,
  getRequestByTrackingCode,
  searchTrackRequests,
  createRequest,
  updateRequestStatus,
  deleteRequest,
  getEquipmentCategories,
  getDbStatus
};
