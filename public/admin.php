<?php
// NU Support Repair - Admin Portal
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Portal | NU Support Repair</title>
  <meta name="description" content="ระบบจัดการงานซ่อมและบริการสนับสนุนไอที สำหรับเจ้าหน้าที่ช่างและผู้ดูแลระบบ NU Support Repair">

  <link rel="icon" type="image/x-icon" href="img/logo.png">
  <!-- Google Fonts: Prompt & Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>

  <!-- Main Stylesheet -->
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>

  <!-- ================= 1. ADMIN LOGIN VIEW (Displayed when not logged in) ================= -->
  <div id="adminLoginSection" class="admin-login-wrapper">
    <div class="admin-login-card">
      <div class="admin-login-badge">
        <i data-lucide="shield-check"></i>
      </div>
      <h2 class="admin-login-title">NU Support Repair</h2>
      <p class="admin-login-desc">เข้าสู่ระบบสำหรับเจ้าหน้าที่ช่างและผู้ดูแลระบบ</p>

      <form id="adminLoginForm" onsubmit="handleAdminLogin(event)" class="admin-login-form">
        <div class="form-group">
          <label for="adminUsername" class="form-label required">ชื่อผู้ใช้ (Username)</label>
          <div class="input-with-icon">
            <i data-lucide="user"></i>
            <input type="text" id="adminUsername" class="form-control" placeholder="กรอก Username (เช่น abcd)" required autocomplete="username">
          </div>
        </div>

        <div class="form-group">
          <label for="adminPassword" class="form-label required">รหัสผ่าน (Password)</label>
          <div class="input-with-icon">
            <i data-lucide="lock"></i>
            <input type="password" id="adminPassword" class="form-control" placeholder="กรอก Password (เช่น 1234)" required autocomplete="current-password">
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" id="btnAdminLogin">
          <i data-lucide="log-in"></i>
          <span>เข้าสู่ระบบผู้ดูแล</span>
        </button>

        <div class="admin-login-hint">
          <i data-lucide="info"></i>
          <span>ระบบรักษาความปลอดภัย: เฉพาะเจ้าหน้าที่ช่างและแอดมินที่ได้รับอนุญาตเท่านั้น</span>
        </div>
      </form>
    </div>
  </div>

  <!-- ================= 2. ADMIN DASHBOARD VIEW (Displayed when authenticated) ================= -->
  <div id="adminDashboardSection" style="display: none;">

    <!-- Top Admin Header -->
    <header class="admin-nav-bar">
      <div class="nav-container">
        <div class="nav-brand" onclick="switchAdminTab('requests')">
          <div class="brand-icon">
            <i data-lucide="shield"></i>
          </div>
          <div class="brand-text">
            <span class="brand-title">NU Support Admin</span>
            <span class="brand-subtitle">แผงควบคุมและจัดการงานซ่อมบำรุง</span>
          </div>
        </div>

        <div class="nav-actions">
          <span class="admin-badge-pill">Admin Mode</span>
          <span class="admin-user-pill">
            <i data-lucide="user-check"></i>
            <span id="currentAdminName">ผู้ดูแลระบบ: abcd</span>
          </span>
          <a href="/" target="_blank" class="btn btn-outline btn-sm" title="เปิดหน้าเว็บหลักสำหรับผู้ใช้งาน">
            <i data-lucide="external-link"></i>
            <span>หน้าเว็บหลัก</span>
          </a>
          <button class="btn btn-danger btn-sm" onclick="handleAdminLogout()">
            <i data-lucide="log-out"></i>
            <span>ออกจากระบบ</span>
          </button>
        </div>
      </div>
    </header>

    <!-- Main Admin Container -->
    <main class="main-content">

      <!-- Admin Top KPI Stats Grid -->
      <div class="stats-grid">
        <div class="stat-card stat-total" onclick="filterAdminTableBy('all')">
          <div class="stat-icon">
            <i data-lucide="clipboard-list"></i>
          </div>
          <div class="stat-info">
            <span class="stat-label">รายการทั้งหมด</span>
            <h3 class="stat-value" id="adminStatTotal">0</h3>
            <span class="stat-sub">งานแจ้งซ่อมทั้งหมด</span>
          </div>
        </div>

        <div class="stat-card stat-pending" onclick="filterAdminTableBy('pending')">
          <div class="stat-icon">
            <i data-lucide="clock"></i>
          </div>
          <div class="stat-info">
            <span class="stat-label">รอดำเนินการ</span>
            <h3 class="stat-value" id="adminStatPending">0</h3>
            <span class="stat-sub">รอจัดสรรช่าง</span>
          </div>
        </div>

        <div class="stat-card stat-progress" onclick="filterAdminTableBy('in_progress')">
          <div class="stat-icon">
            <i data-lucide="wrench"></i>
          </div>
          <div class="stat-info">
            <span class="stat-label">กำลังดำเนินการซ่อม</span>
            <h3 class="stat-value" id="adminStatProgress">0</h3>
            <span class="stat-sub">ช่างกำลังซ่อมแซม</span>
          </div>
        </div>

        <div class="stat-card stat-completed" onclick="filterAdminTableBy('completed')">
          <div class="stat-icon">
            <i data-lucide="check-circle-2"></i>
          </div>
          <div class="stat-info">
            <span class="stat-label">ซ่อมเสร็จสิ้น</span>
            <h3 class="stat-value" id="adminStatCompleted">0</h3>
            <span class="stat-sub">พร้อมส่งมอบงาน</span>
          </div>
        </div>
      </div>

      <!-- Navigation Tabs for Admin Sections -->
      <div class="admin-tabs-nav">
        <button class="admin-tab-btn active" data-tab="requests" onclick="switchAdminTab('requests')">
          <i data-lucide="list"></i>
          <span>จัดการรายการแจ้งซ่อม</span>
        </button>
        <button class="admin-tab-btn" data-tab="update" onclick="switchAdminTab('update')">
          <i data-lucide="edit-3"></i>
          <span>อัปเดตสถานะงาน (ช่าง / ผู้ดูแล)</span>
        </button>
        <button class="admin-tab-btn" data-tab="database" onclick="switchAdminTab('database')">
          <i data-lucide="database"></i>
          <span>การตั้งค่าและฐานข้อมูล MySQL</span>
        </button>
      </div>

      <!-- TAB 1: ALL REQUESTS MANAGEMENT TABLE -->
      <section id="tab-requests" class="admin-tab-content active">
        <!-- Filter Toolbar -->
        <div class="card filter-toolbar-card">
          <div class="filter-grid">
            <div class="filter-col search-col">
              <label class="filter-label">ค้นหารายการ</label>
              <div class="input-with-icon">
                <i data-lucide="search"></i>
                <input type="text" id="adminSearchInput" class="form-control" placeholder="รหัส, ผู้แจ้ง, อุปกรณ์, รุ่น..." oninput="debounceAdminSearch()">
              </div>
            </div>

            <div class="filter-col">
              <label class="filter-label">สถานะงาน</label>
              <select id="adminStatusFilter" class="form-control" onchange="fetchAdminRequests()">
                <option value="all">ทั้งหมด ทุกสถานะ</option>
                <option value="pending">รอดำเนินการ</option>
                <option value="assigned">มอบหมายช่างแล้ว</option>
                <option value="in_progress">กำลังดำเนินการซ่อม</option>
                <option value="completed">ซ่อมเสร็จสิ้น</option>
                <option value="cancelled">ยกเลิก</option>
              </select>
            </div>

            <div class="filter-col">
              <label class="filter-label">ประเภทอุปกรณ์ (4 รายการ)</label>
              <select id="adminEquipmentFilter" class="form-control" onchange="fetchAdminRequests()">
                <option value="all">อุปกรณ์ทั้งหมด</option>
                <option value="คอม">คอม</option>
                <option value="โน็ตบุ๊ค">โน็ตบุ๊ค</option>
                <option value="เครื่องพิม">เครื่องพิม</option>
                <option value="อุปกรณ์เครือข่าย">อุปกรณ์เครือข่าย</option>
              </select>
            </div>

            <div class="filter-col">
              <label class="filter-label">ระดับความเร่งด่วน</label>
              <select id="adminUrgencyFilter" class="form-control" onchange="fetchAdminRequests()">
                <option value="all">ความเร่งด่วนทั้งหมด</option>
                <option value="normal">ปกติ (Normal)</option>
                <option value="urgent">เร่งด่วน (Urgent)</option>
                <option value="emergency">ด่วนที่สุด (Emergency)</option>
              </select>
            </div>

            <div class="filter-col btn-col">
              <button class="btn btn-ghost" onclick="resetAdminFilters()" title="รีเซ็ตตัวกรอง">
                <i data-lucide="rotate-ccw"></i>
                <span>รีเซ็ต</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Table Card -->
        <div class="card history-table-card">
          <div class="table-meta-bar">
            <div class="meta-count">
              <span>รายการแจ้งซ่อมทั้งหมด: <strong id="adminTableCount">0</strong> รายการ</span>
            </div>
            <div class="meta-tools">
              <button class="btn btn-outline btn-sm" onclick="exportAdminCSV()">
                <i data-lucide="download"></i>
                <span>ส่งออก CSV</span>
              </button>
              <button class="btn btn-ghost btn-sm" onclick="window.print()">
                <i data-lucide="printer"></i>
                <span>พิมพ์รายงาน</span>
              </button>
            </div>
          </div>

          <div class="table-responsive">
            <table class="data-table" id="adminTable">
              <thead>
                <tr>
                  <th style="width: 120px;">รหัสติดตาม</th>
                  <th style="width: 120px;">วันที่แจ้ง</th>
                  <th>ผู้แจ้ง / แผนก</th>
                  <th>อุปกรณ์ & รุ่น</th>
                  <th>ปัญหาที่เกิด</th>
                  <th style="width: 110px;">ความเร่งด่วน</th>
                  <th style="width: 130px;">สถานะ</th>
                  <th>ช่างผู้ดูแล</th>
                  <th class="text-center col-action" style="width: 96px; min-width: 96px;">จัดการ</th>
                </tr>
              </thead>
              <tbody id="adminTableBody">
                <tr>
                  <td colspan="9" class="text-center py-4">
                    <div class="loading-spinner">
                      <i data-lucide="loader" class="spin"></i>
                      <span>กำลังโหลดข้อมูล...</span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- TAB 2: UPDATE TASK STATUS (TECHNICIAN / ADMIN UPDATER) -->
      <section id="tab-update" class="admin-tab-content" style="display: none;">
        <div class="form-container-card">
          <div class="form-section-title">
            <i data-lucide="edit-3"></i>
            <span>อัปเดตสถานะงาน (สำหรับช่าง / ผู้ดูแลระบบ)</span>
          </div>

          <p class="page-desc" style="margin-bottom: 1.5rem;">
            เลือกรายการแจ้งซ่อมที่ต้องการอัปเดตสถานะ มอบหมายช่าง บันทึกงานซ่อม หรือระบุวันแล้วเสร็จ
          </p>

          <!-- Ticket Selector Bar -->
          <div class="form-group">
            <label for="quickSelectTicket" class="form-label required">เลือกรหัสแจ้งซ่อมเพื่ออัปเดต</label>
            <div class="input-with-icon">
              <i data-lucide="search"></i>
              <select id="quickSelectTicket" class="form-control" onchange="loadTicketForUpdate(this.value)">
                <option value="" disabled selected>-- เลือกรหัสรายการแจ้งซ่อม --</option>
                <!-- Populated dynamically -->
              </select>
            </div>
          </div>

          <form id="adminStatusUpdateForm" onsubmit="saveStatusUpdate(event)">
            <input type="hidden" id="updateTicketId">

            <!-- Selected Ticket Summary Preview Box -->
            <div id="updateTicketPreview" style="display: none; margin-bottom: 1.5rem; background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: var(--radius-lg); padding: 1.25rem;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <strong id="previewCode" style="font-family: var(--font-code); color: var(--primary); font-size: 1.1rem;">REQ-XXXX</strong>
                <span id="previewStatusBadge" class="status-pill">รอดำเนินการ</span>
              </div>
              <div style="font-size: 0.9rem; color: var(--dark-800);">
                ผู้แจ้ง: <strong id="previewReporter">-</strong> | อุปกรณ์: <strong id="previewEquipment">-</strong> (<span id="previewModel">-</span>)
              </div>
              <div style="font-size: 0.85rem; color: var(--dark-600); margin-top: 0.3rem;">
                ปัญหา: <span id="previewIssue">-</span>
              </div>
            </div>

            <div class="form-grid-2">
              <div class="form-group">
                <label for="updateStatusSelect" class="form-label required">สถานะงานซ่อม</label>
                <div class="input-with-icon">
                  <i data-lucide="check-circle-2"></i>
                  <select id="updateStatusSelect" class="form-control" required>
                    <option value="pending">รอดำเนินการ (Pending)</option>
                    <option value="assigned">มอบหมายช่างแล้ว (Assigned)</option>
                    <option value="in_progress">กำลังดำเนินการซ่อม (In Progress)</option>
                    <option value="completed">ซ่อมเสร็จสิ้น (Completed)</option>
                    <option value="cancelled">ยกเลิก (Cancelled)</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label for="updateTechName" class="form-label">ชื่อช่างผู้รับผิดชอบงาน</label>
                <div class="input-with-icon">
                  <i data-lucide="user"></i>
                  <input type="text" id="updateTechName" class="form-control" placeholder="เช่น ช่างวิชัย แสงทอง">
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="updateTechNotes" class="form-label">บันทึกของช่าง / อะไหล่ที่เปลี่ยน / ความคืบหน้า</label>
              <textarea id="updateTechNotes" class="form-control textarea" rows="3" placeholder="ระบุการดำเนินงาน อะไหล่ที่เปลี่ยน หรือสาเหตุที่พบ..."></textarea>
            </div>

            <div class="form-group">
              <label for="updateEstDate" class="form-label">วันที่คาดว่าจะเสร็จสิ้น</label>
              <div class="input-with-icon">
                <i data-lucide="calendar"></i>
                <input type="date" id="updateEstDate" class="form-control">
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary btn-lg" id="btnSaveStatus">
                <i data-lucide="save"></i>
                <span>บันทึกการอัปเดตลงระบบ</span>
              </button>
            </div>
          </form>
        </div>
      </section>

      <!-- TAB 3: MYSQL DATABASE SETTINGS & STATUS (Moved to Admin per requirement 7) -->
      <section id="tab-database" class="admin-tab-content" style="display: none;">
        <div class="form-container-card">
          <div class="form-section-title">
            <i data-lucide="database"></i>
            <span>การตั้งค่าและสถานะฐานข้อมูล MySQL</span>
          </div>

          <div class="db-status-banner" id="adminDbStatusBanner">
            <i data-lucide="refresh-cw" class="spin"></i>
            <div>กำลังตรวจสอบสถานะการเชื่อมต่อฐานข้อมูล...</div>
          </div>

          <form id="adminDbConfigForm" onsubmit="handleAdminDbReconnect(event)">
            <p class="page-desc" style="margin-bottom: 1.25rem;">
              ระบุข้อมูลการเชื่อมต่อ MySQL Server ของคุณ (เช่น รหัสผ่าน root) และกดเชื่อมต่อ ระบบจะสร้างฐานข้อมูล <code>maintenance_db</code> และสร้างตารางให้อัตโนมัติ:
            </p>

            <div class="form-grid-2">
              <div class="form-group">
                <label class="form-label">Database Host</label>
                <input type="text" id="adminDbHost" class="form-control" value="localhost">
              </div>
              <div class="form-group">
                <label class="form-label">Database Port</label>
                <input type="number" id="adminDbPort" class="form-control" value="3306">
              </div>
            </div>

            <div class="form-grid-2">
              <div class="form-group">
                <label class="form-label">MySQL Username</label>
                <input type="text" id="adminDbUser" class="form-control" value="root">
              </div>
              <div class="form-group">
                <label class="form-label">MySQL Password</label>
                <input type="password" id="adminDbPassword" class="form-control" placeholder="รหัสผ่าน MySQL root">
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Database Name</label>
              <input type="text" id="adminDbName" class="form-control" value="maintenance_db">
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary" id="btnAdminDbTest">
                <i data-lucide="refresh-cw"></i>
                <span>ทดสอบและเชื่อมต่อ MySQL</span>
              </button>
            </div>
          </form>

          <div class="db-sql-tip">
            <i data-lucide="file-code"></i>
            <span>คุณสามารถนำไฟล์ <code>database.sql</code> ในโฟลเดอร์โปรเจกต์ไป Import ใน phpMyAdmin หรือ MySQL Workbench ได้โดยตรง</span>
          </div>
        </div>
      </section>

    </main>
  </div>

  <!-- ================= EDIT MODAL FOR TABLE ROWS ================= -->
  <div class="modal-overlay" id="adminEditModal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <div class="modal-title-wrap">
            <i data-lucide="wrench" class="text-primary"></i>
            <div>
              <h3>อัปเดตสถานะงานซ่อม</h3>
              <span class="modal-sub" id="modalTicketCode">REQ-2026-XXXX</span>
            </div>
          </div>
          <button class="btn-modal-close" onclick="closeAdminEditModal()">
            <i data-lucide="x"></i>
          </button>
        </div>

        <form id="modalStatusForm" onsubmit="handleModalStatusSubmit(event)">
          <div class="modal-body">
            <input type="hidden" id="modalTicketId">

            <div class="form-grid-2">
              <div class="form-group">
                <label class="form-label required">เปลี่ยนสถานะงาน</label>
                <select id="modalStatusSelect" class="form-control" required>
                  <option value="pending">รอดำเนินการ</option>
                  <option value="assigned">มอบหมายช่างแล้ว</option>
                  <option value="in_progress">กำลังดำเนินการซ่อม</option>
                  <option value="completed">ซ่อมเสร็จสิ้น</option>
                  <option value="cancelled">ยกเลิก</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">ชื่อช่างผู้รับผิดชอบ</label>
                <input type="text" id="modalTechName" class="form-control" placeholder="ระบุชื่อช่าง">
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">บันทึกของช่าง / อะไหล่ที่เปลี่ยน</label>
              <textarea id="modalTechNotes" class="form-control textarea" rows="3" placeholder="ระบุการดำเนินงาน..."></textarea>
            </div>

            <div class="form-group">
              <label class="form-label">วันที่คาดว่าจะเสร็จสิ้น</label>
              <input type="date" id="modalEstDate" class="form-control">
            </div>
          </div>

          <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center;">
            <button type="button" class="btn btn-danger-subtle" onclick="deleteTicketFromCurrentModal()" title="ลบรายการแจ้งซ่อมนี้">
              <i data-lucide="trash-2"></i>
              <span>ลบรายการนี้</span>
            </button>
            <div style="display: flex; gap: 0.5rem;">
              <button type="button" class="btn btn-outline" onclick="closeAdminEditModal()">ยกเลิก</button>
              <button type="submit" class="btn btn-primary">
                <i data-lucide="save"></i>
                <span>บันทึกสถานะ</span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Toast Notification Container -->
  <div class="toast-container" id="toastContainer"></div>

  <!-- Admin Script -->
  <script src="/js/admin.js"></script>
</body>
</html>
