const express = require('express');
const cors = require('cors');
const path = require('path');
const fs = require('fs');
const multer = require('multer');
require('dotenv').config();

const db = require('./config/db');

const app = express();
const PORT = process.env.PORT || 3000;

// Setup upload folder
const uploadDir = path.join(__dirname, 'uploads');
if (!fs.existsSync(uploadDir)) {
  fs.mkdirSync(uploadDir, { recursive: true });
}

// Multer storage for repair issue photos
const storage = multer.diskStorage({
  destination: function (req, file, cb) {
    cb(null, uploadDir);
  },
  filename: function (req, file, cb) {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1e9);
    const ext = path.extname(file.originalname);
    cb(null, 'issue-' + uniqueSuffix + ext);
  }
});
const upload = multer({
  storage: storage,
  limits: { fileSize: 10 * 1024 * 1024 } // 10MB
});

app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve static assets
app.use(express.static(path.join(__dirname, 'public')));
app.use('/uploads', express.static(uploadDir));

// Admin Page Route (Requirement: no admin link in main page, type in URL directly)
app.get('/admin', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'admin.html'));
});

// Admin Login API (Username: abcd, Password: 1234)
app.post('/api/admin/login', (req, res) => {
  const { username, password } = req.body;
  if (username === 'abcd' && password === '1234') {
    res.json({
      success: true,
      message: 'เข้าสู่ระบบสำเร็จ',
      token: 'nu-support-admin-token-2026',
      user: { username: 'abcd', role: 'admin', name: 'ผู้ดูแลระบบ NU Support' }
    });
  } else {
    res.status(401).json({
      success: false,
      message: 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง (Username หรือ Password ผิดพลาด)'
    });
  }
});

// ==========================================
// REST API ENDPOINTS
// ==========================================

// 1. Get statistics overview
app.get('/api/stats', async (req, res) => {
  try {
    const stats = await db.getStats();
    res.json({ success: true, data: stats });
  } catch (error) {
    res.status(500).json({ success: false, message: error.message });
  }
});

// 2. Get list of maintenance requests with filters
app.get('/api/requests', async (req, res) => {
  try {
    const { status, equipment, urgency, search, limit, offset } = req.query;
    const requests = await db.getRequests({ status, equipment, urgency, search, limit, offset });
    res.json({ success: true, data: requests });
  } catch (error) {
    res.status(500).json({ success: false, message: error.message });
  }
});

// 3. Search tracking status by Tracking Code OR Reporter Name OR Phone
app.get('/api/track/search', async (req, res) => {
  try {
    const q = req.query.q || req.query.query || '';
    if (!q.trim()) {
      return res.status(400).json({ success: false, message: 'กรุณากรอกรหัสแจ้งซ่อมหรือชื่อผู้แจ้ง' });
    }
    const results = await db.searchTrackRequests(q.trim());
    res.json({ success: true, data: results });
  } catch (error) {
    res.status(500).json({ success: false, message: error.message });
  }
});

// 3.1 Get single request by tracking code or name or phone
app.get('/api/requests/:code', async (req, res) => {
  try {
    const code = req.params.code;
    const item = await db.getRequestByTrackingCode(code);
    if (!item) {
      return res.status(404).json({ success: false, message: 'ไม่พบรายการแจ้งซ่อมที่ระบุ' });
    }
    res.json({ success: true, data: item });
  } catch (error) {
    res.status(500).json({ success: false, message: error.message });
  }
});

// 4. Create new maintenance request (หน้าแจ้งปัญหา)
// Fields: reporter_name, equipment_name, equipment_model, issue_description, department, location, phone, urgency
app.post('/api/requests', upload.single('image'), async (req, res) => {
  try {
    const { reporter_name, equipment_name, equipment_model, issue_description, department, location, phone, urgency } = req.body;

    // Validate required fields
    if (!reporter_name || !equipment_name || !equipment_model || !issue_description) {
      return res.status(400).json({
        success: false,
        message: 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน: ชื่อผู้แจ้ง, ชื่ออุปกรณ์, ชื่อรุ่น, ปัญหาที่เกิด'
      });
    }

    let image_url = null;
    if (req.file) {
      image_url = '/uploads/' + req.file.filename;
    }

    const newRequest = await db.createRequest({
      reporter_name,
      equipment_name,
      equipment_model,
      issue_description,
      department,
      location,
      phone,
      urgency,
      image_url
    });

    res.status(201).json({
      success: true,
      message: 'บันทึกการแจ้งซ่อมเรียบร้อยแล้ว',
      data: newRequest
    });
  } catch (error) {
    res.status(500).json({ success: false, message: error.message });
  }
});

// 5. Update request status & technician notes (Admin/Technician action)
app.put('/api/requests/:id/status', async (req, res) => {
  try {
    const id = req.params.id;
    const { status, technician_name, technician_notes, estimated_completion, repair_cost } = req.body;

    const validStatuses = ['pending', 'assigned', 'in_progress', 'completed', 'cancelled'];
    if (status && !validStatuses.includes(status)) {
      return res.status(400).json({ success: false, message: 'สถานะไม่ถูกต้อง' });
    }

    const updated = await db.updateRequestStatus(id, {
      status,
      technician_name,
      technician_notes,
      estimated_completion,
      repair_cost
    });

    if (!updated) {
      return res.status(404).json({ success: false, message: 'ไม่พบรายการที่ต้องการอัปเดต' });
    }

    res.json({
      success: true,
      message: 'อัปเดตสถานะงานแจ้งซ่อมเรียบร้อยแล้ว',
      data: updated
    });
  } catch (error) {
    res.status(500).json({ success: false, message: error.message });
  }
});

// 6. Delete a request
app.delete('/api/requests/:id', async (req, res) => {
  try {
    const id = req.params.id;
    const ok = await db.deleteRequest(id);
    if (!ok) {
      return res.status(404).json({ success: false, message: 'ไม่พบรายการที่ต้องการลบ' });
    }
    res.json({ success: true, message: 'ลบรายการแจ้งซ่อมเรียบร้อยแล้ว' });
  } catch (error) {
    res.status(500).json({ success: false, message: error.message });
  }
});

// 7. Get equipment categories
app.get('/api/equipment', async (req, res) => {
  try {
    const categories = await db.getEquipmentCategories();
    res.json({ success: true, data: categories });
  } catch (error) {
    res.status(500).json({ success: false, message: error.message });
  }
});

// 8. Get Database Status
app.get('/api/db/status', (req, res) => {
  const status = db.getDbStatus();
  res.json({ success: true, data: status });
});

// 9. Reconnect or test MySQL connection
app.post('/api/db/reconnect', async (req, res) => {
  try {
    const { host, port, user, password, database } = req.body;
    const result = await db.initMySQL({
      host: host || 'localhost',
      port: parseInt(port || '3306', 10),
      user: user || 'root',
      password: password !== undefined ? password : '',
      database: database || 'maintenance_db'
    });

    if (result.success) {
      // update .env file
      try {
        const envContent = `# Server Configuration\nPORT=${PORT}\n\n# MySQL Database Configuration\nDB_HOST=${host || 'localhost'}\nDB_PORT=${port || 3306}\nDB_USER=${user || 'root'}\nDB_PASSWORD=${password !== undefined ? password : ''}\nDB_NAME=${database || 'maintenance_db'}\n`;
        fs.writeFileSync(path.join(__dirname, '.env'), envContent, 'utf8');
      } catch (e) {
        console.error('Could not update .env', e);
      }
      res.json({ success: true, message: 'เชื่อมต่อ MySQL สำเร็จและอัปเดตตารางเรียบร้อยแล้ว!' });
    } else {
      res.status(400).json({ success: false, message: 'เชื่อมต่อ MySQL ไม่สำเร็จ: ' + result.error });
    }
  } catch (error) {
    res.status(500).json({ success: false, message: error.message });
  }
});

// Fallback to index.html for SPA routes (Express 5 compatible)
app.use((req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

// Start server
app.listen(PORT, () => {
  console.log('========================================================');
  console.log(`[NU Support Repair] ระบบแจ้งซ่อมและบริการสนับสนุนไอที พร้อมใช้งาน`);
  console.log(`[Main Web]: http://localhost:${PORT}`);
  console.log(`[Admin Portal]: http://localhost:${PORT}/admin (User: abcd / Pass: 1234)`);
  console.log('========================================================');
});
