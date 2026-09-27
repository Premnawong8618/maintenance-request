<?php
// NU Support Repair - ระบบแจ้งซ่อมและบริการสนับสนุนไอที
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NU Support Repair | ระบบแจ้งซ่อมและติดตามงาน</title>
  <meta name="description" content="NU Support Repair - ระบบแจ้งซ่อมและบริการสนับสนุนไอที แจ้งซ่อมคอมพิวเตอร์ โน้ตบุ๊ก เครื่องพิมพ์ และอุปกรณ์เครือข่าย พร้อมติดตามสถานะแบบเรียลไทม์">

  <link rel="icon" type="image/x-icon" href="img/logo.png">
  <!-- Google Fonts: Prompt & Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>

  <!-- Main Stylesheet (Orange Theme) -->
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>

  <!-- ================= TOP NAVIGATION BAR ================= -->
  <!-- Note: Strictly NO Admin tab/link on main page per requirement -->
  <header class="navbar" id="mainNavbar">
    <div class="nav-container">
      <div class="nav-brand" onclick="navigateTo('home')">
        <div class="brand-icon">
          <i data-lucide="wrench"></i>
        </div>
        <div class="brand-text">
          <span class="brand-title">NU Support Repair</span>
          <span class="brand-subtitle">ระบบแจ้งซ่อมและบริการสนับสนุนไอที</span>
        </div>
      </div>

      <nav class="nav-links">
        <button class="nav-item active" data-page="home" onclick="navigateTo('home')">
          <i data-lucide="layout-dashboard"></i>
          <span>หน้าหลัก</span>
        </button>
        <button class="nav-item" data-page="report" onclick="navigateTo('report')">
          <i data-lucide="plus-circle"></i>
          <span>แจ้งปัญหา</span>
        </button>
        <button class="nav-item" data-page="track" onclick="navigateTo('track')">
          <i data-lucide="search"></i>
          <span>ติดตามสถานะ</span>
        </button>
        <button class="nav-item" data-page="history" onclick="navigateTo('history')">
          <i data-lucide="history"></i>
          <span>ประวัติการแจ้งซ่อม</span>
        </button>
      </nav>

      <div class="nav-actions">
        <button class="btn btn-primary btn-sm mobile-report-btn" onclick="navigateTo('report')">
          <i data-lucide="plus"></i>
          <span>แจ้งซ่อม</span>
        </button>
      </div>
    </div>
  </header>

  <!-- ================= MAIN CONTAINER ================= -->
  <main class="main-content">

    <!-- ================= PAGE 1: หน้าหลัก (HOME) ================= -->
    <section id="page-home" class="page-section active">
      <!-- Hero Banner Section -->
      <div class="hero-card">
        <div class="hero-content">
          <div class="hero-badge">
            <i data-lucide="shield-check"></i>
            <span>ศูนย์บริการแจ้งซ่อมและดูแลอุปกรณ์ไอที</span>
          </div>
          <h1 class="hero-title">NU Support Repair</h1>
          <p class="hero-desc">
            ระบบแจ้งซ่อมบำรุงและติดตามงานสะดวกรวดเร็ว ดูแลทั้งคอม โน็ตบุ๊ค เครื่องพิม และอุปกรณ์เครือข่าย ติดตามความคืบหน้าได้ตลอด 24 ชั่วโมง
          </p>
          <div class="hero-actions">
            <button class="btn btn-primary btn-lg" onclick="navigateTo('report')">
              <i data-lucide="file-plus-2"></i>
              <span>แจ้งปัญหาใหม่</span>
            </button>
            <button class="btn btn-outline btn-lg" onclick="navigateTo('track')">
              <i data-lucide="search"></i>
              <span>ติดตามสถานะงาน</span>
            </button>
          </div>

          <!-- Quick Search Bar inside Hero -->
          <div class="hero-quick-search">
            <div class="search-input-wrap">
              <i data-lucide="search"></i>
              <input type="text" id="heroTrackingInput" placeholder="ค้นหาด้วยรหัสแจ้งซ่อม หรือชื่อผู้แจ้ง (เช่น สมชาย)..." onkeydown="if(event.key==='Enter') quickTrackFromHero()">
            </div>
            <button class="btn btn-primary" onclick="quickTrackFromHero()">
              <i data-lucide="arrow-right"></i>
              <span>ค้นหา</span>
            </button>
          </div>
        </div>

        <div class="hero-image-wrap">
          <img src="/images/hero_banner.jpg" alt="NU Support Repair Workstation" class="hero-banner-img">
        </div>
      </div>

      <!-- 4 Equipment Shortcuts Selection Cards -->
      <div class="equipment-shortcuts-section">
        <div class="section-head-simple">
          <h3>เลือกอุปกรณ์ที่ต้องการแจ้งซ่อม</h3>
          <p>คลิกเลือกประเภทอุปกรณ์เพื่อเปิดแบบฟอร์มแจ้งซ่อมได้ทันที</p>
        </div>
        <div class="equipment-cards-grid">
          <div class="eq-shortcut-card" onclick="selectEquipmentAndReport('คอม')">
            <div class="eq-icon-circle">
              <i data-lucide="monitor"></i>
            </div>
            <h4 class="eq-shortcut-title">คอม</h4>
            <p class="eq-shortcut-desc">คอมพิวเตอร์ตั้งโต๊ะ หน้าจอ เครื่อง PC</p>
            <span class="eq-shortcut-btn">แจ้งซ่อมคอม <i data-lucide="arrow-right"></i></span>
          </div>

          <div class="eq-shortcut-card" onclick="selectEquipmentAndReport('โน็ตบุ๊ค')">
            <div class="eq-icon-circle">
              <i data-lucide="laptop"></i>
            </div>
            <h4 class="eq-shortcut-title">โน็ตบุ๊ค</h4>
            <p class="eq-shortcut-desc">แล็ปท็อป โน้ตบุ๊กทำงาน คีย์บอร์ด แบตเตอรี่</p>
            <span class="eq-shortcut-btn">แจ้งซ่อมโน็ตบุ๊ค <i data-lucide="arrow-right"></i></span>
          </div>

          <div class="eq-shortcut-card" onclick="selectEquipmentAndReport('เครื่องพิม')">
            <div class="eq-icon-circle">
              <i data-lucide="printer"></i>
            </div>
            <h4 class="eq-shortcut-title">เครื่องพิม</h4>
            <p class="eq-shortcut-desc">เครื่องพิมพ์ ปริ้นเตอร์ หมึกพิมพ์ สแกนเนอร์</p>
            <span class="eq-shortcut-btn">แจ้งซ่อมเครื่องพิม <i data-lucide="arrow-right"></i></span>
          </div>

          <div class="eq-shortcut-card" onclick="selectEquipmentAndReport('อุปกรณ์เครือข่าย')">
            <div class="eq-icon-circle">
              <i data-lucide="wifi"></i>
            </div>
            <h4 class="eq-shortcut-title">อุปกรณ์เครือข่าย</h4>
            <p class="eq-shortcut-desc">เราเตอร์ สวิตช์ สัญญาณ Wi-Fi สาย LAN</p>
            <span class="eq-shortcut-btn">แจ้งซ่อมเครือข่าย <i data-lucide="arrow-right"></i></span>
          </div>
        </div>
      </div>

      <!-- KPI Statistics Section -->
      <div class="stats-grid">
        <div class="stat-card stat-total" onclick="filterHistoryBy('all')">
          <div class="stat-icon">
            <i data-lucide="clipboard-list"></i>
          </div>
          <div class="stat-info">
            <span class="stat-label">งานแจ้งซ่อมทั้งหมด</span>
            <h3 class="stat-value" id="statTotal">0</h3>
            <span class="stat-sub">รายการในระบบ</span>
          </div>
        </div>

        <div class="stat-card stat-pending" onclick="filterHistoryBy('pending')">
          <div class="stat-icon">
            <i data-lucide="clock"></i>
          </div>
          <div class="stat-info">
            <span class="stat-label">รอดำเนินการ</span>
            <h3 class="stat-value" id="statPending">0</h3>
            <span class="stat-sub">รอช่างเข้าตรวจสอบ</span>
          </div>
        </div>

        <div class="stat-card stat-progress" onclick="filterHistoryBy('in_progress')">
          <div class="stat-icon">
            <i data-lucide="wrench"></i>
          </div>
          <div class="stat-info">
            <span class="stat-label">กำลังดำเนินการซ่อม</span>
            <h3 class="stat-value" id="statProgress">0</h3>
            <span class="stat-sub">ช่างกำลังตรวจเช็ค/ซ่อม</span>
          </div>
        </div>

        <div class="stat-card stat-completed" onclick="filterHistoryBy('completed')">
          <div class="stat-icon">
            <i data-lucide="check-circle-2"></i>
          </div>
          <div class="stat-info">
            <span class="stat-label">ซ่อมเสร็จสิ้นแล้ว</span>
            <h3 class="stat-value" id="statCompleted">0</h3>
            <span class="stat-sub">พร้อมส่งมอบงาน</span>
          </div>
        </div>
      </div>

      <!-- Quick Action Cards & Recent Requests Feed -->
      <div class="dashboard-grid">
        <!-- Left: Recent Requests Feed -->
        <div class="card recent-requests-card">
          <div class="card-header">
            <div class="card-title-group">
              <i data-lucide="activity" class="card-header-icon text-primary"></i>
              <div>
                <h2 class="card-title">รายการแจ้งซ่อมล่าสุด</h2>
                <span class="card-subtitle">รายการที่มีการแจ้งเข้ามาในระบบ</span>
              </div>
            </div>
            <button class="btn btn-ghost btn-sm" onclick="navigateTo('history')">
              <span>ดูประวัติทั้งหมด</span>
              <i data-lucide="chevron-right"></i>
            </button>
          </div>

          <div class="recent-list" id="recentRequestsList">
            <div class="loading-spinner text-center py-4">
              <i data-lucide="loader" class="spin"></i>
              <span>กำลังโหลดข้อมูล...</span>
            </div>
          </div>
        </div>

        <!-- Right: 4-Step Maintenance Workflow -->
        <div class="card workflow-card">
          <div class="card-header">
            <div class="card-title-group">
              <i data-lucide="git-branch" class="card-header-icon text-primary"></i>
              <div>
                <h2 class="card-title">ขั้นตอนการบริการซ่อมบำรุง</h2>
                <span class="card-subtitle">มาตรฐานการดำเนินงาน 4 ขั้นตอน</span>
              </div>
            </div>
          </div>

          <div class="workflow-steps">
            <div class="step-item">
              <div class="step-num">1</div>
              <div class="step-content">
                <h4>แจ้งปัญหาเข้าระบบ</h4>
                <p>เลือกประเภทอุปกรณ์ กรอกรุ่น และระบุปัญหาที่เกิดขึ้น</p>
              </div>
            </div>
            <div class="step-line"></div>

            <div class="step-item">
              <div class="step-num">2</div>
              <div class="step-content">
                <h4>รับเรื่องและจัดสรรช่าง</h4>
                <p>ทีมสนับสนุนรับเรื่องและมอบหมายช่างผู้รับผิดชอบงาน</p>
              </div>
            </div>
            <div class="step-line"></div>

            <div class="step-item">
              <div class="step-num">3</div>
              <div class="step-content">
                <h4>เข้าดำเนินการซ่อมแซม</h4>
                <p>ช่างตรวจเช็คอาการ เปลี่ยนอะไหล่ และอัปเดตบันทึกงาน</p>
              </div>
            </div>
            <div class="step-line"></div>

            <div class="step-item">
              <div class="step-num">4</div>
              <div class="step-content">
                <h4>ทดสอบและส่งมอบงาน</h4>
                <p>ทดสอบความเรียบร้อย ปิดงาน และแจ้งผู้ใช้งานตรวจรับ</p>
              </div>
            </div>
          </div>

          <div class="helpdesk-box">
            <div class="helpdesk-icon">
              <i data-lucide="headset"></i>
            </div>
            <div class="helpdesk-text">
              <strong>ต้องการความช่วยเหลือเร่งด่วน?</strong>
              <span>ฝ่ายบริการสนับสนุน NU IT Support ติดต่อเบอร์ภายใน: 1122</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= PAGE 2: หน้าแจ้งปัญหา (REPORT FORM) ================= -->
    <section id="page-report" class="page-section">
      <div class="page-header">
        <div class="page-title-group">
          <div class="header-icon-badge">
            <i data-lucide="file-plus-2"></i>
          </div>
          <div>
            <h1 class="page-title">แบบฟอร์มแจ้งปัญหาการซ่อมบำรุง</h1>
            <p class="page-desc">กรุณากรอกข้อมูลอุปกรณ์และระบุปัญหาที่เกิด เพื่อให้ทีมช่างเข้าแก้ไขได้อย่างรวดเร็ว</p>
          </div>
        </div>
      </div>

      <div class="form-container-card">
        <form id="maintenanceReportForm" onsubmit="handleReportSubmit(event)">
          
          <!-- Section 1: ข้อมูลผู้แจ้งและสถานที่ -->
          <div class="form-section">
            <div class="form-section-title">
              <i data-lucide="user-check"></i>
              <span>1. ข้อมูลผู้แจ้งปัญหา</span>
            </div>

            <div class="form-grid-2">
              <div class="form-group">
                <label for="reporterName" class="form-label required">ชื่อผู้แจ้งปัญหา</label>
                <div class="input-with-icon">
                  <i data-lucide="user"></i>
                  <input type="text" id="reporterName" name="reporter_name" class="form-control" placeholder="เช่น สมชาย ใจดี" required>
                </div>
              </div>

              <div class="form-group">
                <label for="reporterPhone" class="form-label">เบอร์โทรศัพท์ติดต่อ</label>
                <div class="input-with-icon">
                  <i data-lucide="phone"></i>
                  <input type="tel" id="reporterPhone" name="phone" class="form-control" placeholder="เช่น 081-234-5678 หรือเบอร์ต่อภายใน">
                </div>
              </div>

              <div class="form-group">
                <label for="reporterDept" class="form-label">แผนก / ฝ่ายงาน</label>
                <div class="input-with-icon">
                  <i data-lucide="building"></i>
                  <input type="text" id="reporterDept" name="department" class="form-control" placeholder="เช่น ฝ่ายบัญชี, ฝ่ายการตลาด, ฝ่าย IT">
                </div>
              </div>

              <div class="form-group">
                <label for="equipmentLocation" class="form-label">สถานที่ตั้ง / ห้อง / ชั้น</label>
                <div class="input-with-icon">
                  <i data-lucide="map-pin"></i>
                  <input type="text" id="equipmentLocation" name="location" class="form-control" placeholder="เช่น อาคาร A ชั้น 2 ห้อง 204">
                </div>
              </div>
            </div>
          </div>

          <!-- Section 2: ข้อมูลอุปกรณ์และรุ่น -->
          <div class="form-section">
            <div class="form-section-title">
              <i data-lucide="cpu"></i>
              <span>2. ข้อมูลอุปกรณ์และรุ่น</span>
            </div>

            <div class="form-grid-2">
              <!-- Dropdown ชื่ออุปกรณ์ (เฉพาะ 4 รายการ) -->
              <div class="form-group">
                <label for="equipmentName" class="form-label required">ประเภทอุปกรณ์</label>
                <div class="input-with-icon">
                  <i data-lucide="layers"></i>
                  <select id="equipmentName" name="equipment_name" class="form-control" required>
                    <option value="" disabled selected>-- เลือกประเภทอุปกรณ์ --</option>
                    <option value="คอม">คอม (เครื่องคอมพิวเตอร์ / PC)</option>
                    <option value="โน็ตบุ๊ค">โน็ตบุ๊ค (Laptop / Notebook)</option>
                    <option value="เครื่องพิม">เครื่องพิม (Printer / Scanner)</option>
                    <option value="อุปกรณ์เครือข่าย">อุปกรณ์เครือข่าย (Network / Wi-Fi / Router)</option>
                  </select>
                </div>
              </div>

              <!-- ชื่อรุ่น -->
              <div class="form-group">
                <label for="equipmentModel" class="form-label required">ชื่อรุ่น / ยี่ห้อ / รหัสทรัพย์สิน</label>
                <div class="input-with-icon">
                  <i data-lucide="tag"></i>
                  <input type="text" id="equipmentModel" name="equipment_model" class="form-control" placeholder="เช่น Dell OptiPlex 7090, HP LaserJet M428, IT-0482" required>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 3: รายละเอียดปัญหาที่เกิดและระดับความเร่งด่วน -->
          <div class="form-section">
            <div class="form-section-title">
              <i data-lucide="alert-circle"></i>
              <span>3. ปัญหาที่เกิดและรายละเอียด</span>
            </div>

            <div class="form-group">
              <label for="issueDescription" class="form-label required">ปัญหาที่เกิด / รายละเอียดอาการชำรุด</label>
              <textarea id="issueDescription" name="issue_description" class="form-control textarea" rows="4" placeholder="กรุณาระบุอาการชำรุดให้ละเอียด เช่น เปิดเครื่องไม่ติด พิมพ์เอกสารไม่ได้ มีไฟกระพริบสีส้ม สัญญาณเน็ตหลุด ฯลฯ" required></textarea>
              <div class="quick-tags">
                <span class="tag-hint">ตัวอย่างอาการคลิกได้:</span>
                <button type="button" class="btn-tag" onclick="appendIssueTag('เครื่องเปิดไม่ติด ไฟไม่เข้า')">เปิดไม่ติด</button>
                <button type="button" class="btn-tag" onclick="appendIssueTag('กระดาษติดบ่อย หมึกพิมพ์ไม่คมชัด')">กระดาษติด/หมึกจาง</button>
                <button type="button" class="btn-tag" onclick="appendIssueTag('เชื่อมต่ออินเทอร์เน็ตไม่ได้ สัญญาณ Wi-Fi หลุดบ่อย')">เน็ตหลุด</button>
                <button type="button" class="btn-tag" onclick="appendIssueTag('หน้าจอฟ้า มีเสียงเตือน Beep')">จอฟ้า/เสียงเตือน</button>
              </div>
            </div>

            <!-- ระดับความเร่งด่วน -->
            <div class="form-group">
              <label class="form-label">ระดับความเร่งด่วน</label>
              <div class="urgency-selector">
                <label class="urgency-card urgency-normal">
                  <input type="radio" name="urgency" value="normal" checked>
                  <div class="urgency-box">
                    <div class="urgency-icon"><i data-lucide="check"></i></div>
                    <div class="urgency-text">
                      <strong>ระดับปกติ (Normal)</strong>
                      <span>ซ่อมตามคิวงานปกติ (1-3 วัน)</span>
                    </div>
                  </div>
                </label>

                <label class="urgency-card urgency-urgent">
                  <input type="radio" name="urgency" value="urgent">
                  <div class="urgency-box">
                    <div class="urgency-icon"><i data-lucide="alert-circle"></i></div>
                    <div class="urgency-text">
                      <strong>เร่งด่วน (Urgent)</strong>
                      <span>ส่งผลต่องานทั่วไป (ภายใน 24 ชม.)</span>
                    </div>
                  </div>
                </label>

                <label class="urgency-card urgency-emergency">
                  <input type="radio" name="urgency" value="emergency">
                  <div class="urgency-box">
                    <div class="urgency-icon"><i data-lucide="flame"></i></div>
                    <div class="urgency-text">
                      <strong>ด่วนที่สุด (Emergency)</strong>
                      <span>งานหยุดชะงัก (ทันที)</span>
                    </div>
                  </div>
                </label>
              </div>
            </div>

            <!-- แนบรูปภาพประกอบ -->
            <div class="form-group">
              <label class="form-label">แนบรูปภาพปัญหาชำรุด (ไม่บังคับ)</label>
              <div class="file-drop-zone" id="fileDropZone" onclick="document.getElementById('issueImageInput').click()">
                <input type="file" id="issueImageInput" name="image" accept="image/*" style="display: none;" onchange="handleImagePreview(this)">
                <div class="drop-content" id="dropContent">
                  <i data-lucide="image-plus" class="drop-icon"></i>
                  <span class="drop-title">คลิกเพื่อเลือกรูปภาพ หรือลากไฟล์มาวางที่นี่</span>
                  <span class="drop-sub">รองรับ JPG, PNG, WEBP (ขนาดไม่เกิน 10MB)</span>
                </div>
                <div class="drop-preview" id="dropPreview" style="display: none;">
                  <img id="imagePreviewImg" src="" alt="รูปตัวอย่าง">
                  <button type="button" class="btn-remove-img" onclick="removeImagePreview(event)">
                    <i data-lucide="x"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Submit Buttons -->
          <div class="form-actions">
            <button type="reset" class="btn btn-outline" onclick="resetReportForm()">
              <i data-lucide="rotate-ccw"></i>
              <span>ล้างข้อมูล</span>
            </button>
            <button type="submit" class="btn btn-primary btn-lg" id="submitReportBtn">
              <i data-lucide="send"></i>
              <span>ส่งข้อมูลแจ้งซ่อม</span>
            </button>
          </div>
        </form>
      </div>
    </section>

    <!-- ================= PAGE 3: หน้าติดตามสถานะ (TRACK STATUS) ================= -->
    <section id="page-track" class="page-section">
      <div class="page-header">
        <div class="page-title-group">
          <div class="header-icon-badge">
            <i data-lucide="search"></i>
          </div>
          <div>
            <h1 class="page-title">ติดตามสถานะงานแจ้งซ่อม</h1>
            <p class="page-desc">ค้นหาด้วยรหัสติดตามงานซ่อม (Tracking Code) หรือชื่อผู้แจ้งปัญหา เพื่อตรวจสอบความคืบหน้าแบบเรียลไทม์</p>
          </div>
        </div>
      </div>

      <!-- Search Box Card -->
      <div class="card track-search-card">
        <div class="search-form-wrap">
          <div class="search-input-field">
            <i data-lucide="search"></i>
            <input type="text" id="trackSearchInput" placeholder="กรอกรหัสแจ้งซ่อม (เช่น REQ-2026-0001) หรือชื่อผู้แจ้ง (เช่น สมชาย)..." onkeydown="if(event.key==='Enter') executeTrackSearch()">
          </div>
          <button class="btn btn-primary btn-lg" onclick="executeTrackSearch()" id="trackSearchBtn">
            <i data-lucide="search"></i>
            <span>ค้นหาทันที</span>
          </button>
        </div>

        <!-- Sample chips for instant testing -->
        <div class="sample-chips-bar">
          <span class="chips-title">ตัวอย่างรหัสหรือชื่อ:</span>
          <div class="chips-list" id="sampleChipsList">
            <button type="button" class="chip-item" onclick="loadSampleTicket('REQ-2026-0001')">
              <i data-lucide="hash"></i> REQ-2026-0001
            </button>
            <button type="button" class="chip-item" onclick="loadSampleTicket('REQ-2026-0002')">
              <i data-lucide="hash"></i> REQ-2026-0002
            </button>
            <button type="button" class="chip-item" onclick="loadSampleTicket('สมชาย')">
              <i data-lucide="user"></i> สมชาย
            </button>
            <button type="button" class="chip-item" onclick="loadSampleTicket('ศิริพร')">
              <i data-lucide="user"></i> ศิริพร
            </button>
            <button type="button" class="chip-item" onclick="loadSampleTicket('Prem')">
              <i data-lucide="user"></i> Prem
            </button>
          </div>
        </div>
      </div>

      <!-- Multiple Search Results List Container (when searching by name or partial) -->
      <div id="multiResultsContainer" class="search-results-box" style="display: none;">
        <div class="search-results-header">
          <h3><i data-lucide="list"></i> ผลการค้นหารายการแจ้งซ่อม (<span id="multiResultsCount">0</span> รายการ)</h3>
          <span class="text-sm text-gray-500">คลิกที่รายการเพื่อดูรายละเอียดและความคืบหน้า</span>
        </div>
        <div class="search-results-list" id="multiResultsList">
          <!-- Rendered dynamically -->
        </div>
      </div>

      <!-- Single Ticket Result Container -->
      <div id="trackResultContainer" style="display: none;">
        <!-- Back to search results button (shown if user came from a multi-result search) -->
        <div id="backToResultsBar" style="display: none; margin-bottom: 1rem;">
          <button class="btn btn-outline btn-sm" onclick="backToSearchResults()">
            <i data-lucide="arrow-left"></i>
            <span>กลับไปรายการที่ค้นพบ (<span id="backToResultsCount">0</span> รายการ)</span>
          </button>
        </div>

        <!-- Ticket Summary Banner -->
        <div class="ticket-header-card">
          <div class="ticket-code-group">
            <span class="ticket-tag">รหัสติดตามงานซ่อม</span>
            <h2 class="ticket-code" id="resTrackingCode">REQ-2026-XXXX</h2>
            <button class="btn-copy-code" onclick="copyTrackingCode()" title="คัดลอกรหัส">
              <i data-lucide="copy"></i>
            </button>
          </div>

          <div class="ticket-status-badges">
            <span class="status-pill" id="resStatusPill">รอดำเนินการ</span>
            <span class="urgency-pill" id="resUrgencyPill">ปกติ</span>
          </div>
        </div>

        <!-- 4-Step Visual Timeline Progress Bar -->
        <div class="card timeline-card">
          <h3 class="card-inner-title">
            <i data-lucide="git-commit"></i>
            <span>เส้นทางความคืบหน้างวดงาน (Workflow Timeline)</span>
          </h3>

          <div class="timeline-stepper" id="timelineStepper">
            <!-- Step 1 -->
            <div class="t-step" id="step-pending">
              <div class="t-circle">
                <i data-lucide="file-text"></i>
              </div>
              <div class="t-content">
                <strong>1. ได้รับแจ้งซ่อม</strong>
                <span id="stepPendingDate">บันทึกข้อมูลเรียบร้อย</span>
              </div>
            </div>

            <div class="t-bar" id="bar-1"></div>

            <!-- Step 2 -->
            <div class="t-step" id="step-assigned">
              <div class="t-circle">
                <i data-lucide="user-check"></i>
              </div>
              <div class="t-content">
                <strong>2. มอบหมายช่าง</strong>
                <span id="stepAssignedName">กำลังจัดสรร</span>
              </div>
            </div>

            <div class="t-bar" id="bar-2"></div>

            <!-- Step 3 -->
            <div class="t-step" id="step-in_progress">
              <div class="t-circle">
                <i data-lucide="wrench"></i>
              </div>
              <div class="t-content">
                <strong>3. กำลังซ่อมบำรุง</strong>
                <span id="stepProgressNote">กำลังตรวจเช็ค</span>
              </div>
            </div>

            <div class="t-bar" id="bar-3"></div>

            <!-- Step 4 -->
            <div class="t-step" id="step-completed">
              <div class="t-circle">
                <i data-lucide="check-circle-2"></i>
              </div>
              <div class="t-content">
                <strong>4. ซ่อมเสร็จสิ้น</strong>
                <span id="stepCompletedDate">พร้อมส่งมอบ</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Detail Breakdown Columns -->
        <div class="track-details-grid">
          <!-- Left: Equipment & Issue Info -->
          <div class="card detail-info-card">
            <h3 class="card-inner-title">
              <i data-lucide="info"></i>
              <span>ข้อมูลการแจ้งซ่อมและอุปกรณ์</span>
            </h3>

            <div class="info-list">
              <div class="info-row">
                <span class="info-label">ชื่ออุปกรณ์:</span>
                <span class="info-value highlight" id="resEquipmentName">-</span>
              </div>
              <div class="info-row">
                <span class="info-label">ชื่อรุ่น / รหัส:</span>
                <span class="info-value" id="resEquipmentModel">-</span>
              </div>
              <div class="info-row">
                <span class="info-label">ปัญหาที่เกิด:</span>
                <div class="info-box-desc" id="resIssueDesc">-</div>
              </div>
              <div class="info-row">
                <span class="info-label">ผู้แจ้งปัญหา:</span>
                <span class="info-value" id="resReporterName">-</span>
              </div>
              <div class="info-row">
                <span class="info-label">แผนก / สถานที่:</span>
                <span class="info-value" id="resDepartmentLocation">-</span>
              </div>
              <div class="info-row">
                <span class="info-label">เบอร์โทรติดต่อ:</span>
                <span class="info-value" id="resPhone">-</span>
              </div>
              <div class="info-row">
                <span class="info-label">วันที่ส่งแจ้ง:</span>
                <span class="info-value" id="resCreatedAt">-</span>
              </div>
            </div>

            <!-- Attached photo if available -->
            <div id="resImageContainer" style="display: none; margin-top: 15px;">
              <span class="info-label">รูปภาพประกอบปัญหา:</span>
              <div class="attached-img-wrap">
                <img id="resAttachedImage" src="" alt="ภาพปัญหา">
              </div>
            </div>
          </div>

          <!-- Right: Technician & Repair Progress Card -->
          <div class="card technician-info-card">
            <h3 class="card-inner-title">
              <i data-lucide="user-check"></i>
              <span>สถานะการดำเนินงานของฝ่ายช่าง</span>
            </h3>

            <div class="tech-card-body">
              <div class="tech-profile">
                <div class="tech-avatar">
                  <i data-lucide="wrench"></i>
                </div>
                <div>
                  <span class="tech-sub">ช่างผู้รับผิดชอบงาน</span>
                  <h4 class="tech-name" id="resTechName">ยังไม่ได้มอบหมาย</h4>
                </div>
              </div>

              <div class="tech-notes-box">
                <span class="notes-title"><i data-lucide="message-square"></i> บันทึกงานซ่อม / รายงานความคืบหน้า</span>
                <p id="resTechNotes" class="notes-content">ยังไม่มีบันทึกเพิ่มเติมจากช่าง</p>
              </div>

              <div class="tech-metrics-grid">
                <div class="metric-item">
                  <span class="m-label">วันที่คาดว่าจะเสร็จสิ้น</span>
                  <strong class="m-val" id="resEstDate">-</strong>
                </div>
              </div>

              <!-- Print Slip Action -->
              <div class="track-actions-row">
                <button class="btn btn-outline btn-block" onclick="printCurrentSlip()">
                  <i data-lucide="printer"></i>
                  <span>พิมพ์ใบแจ้งซ่อม (Print Slip)</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- No Result Placeholder -->
      <div id="trackEmptyState" class="empty-state-box">
        <i data-lucide="clipboard-x" class="empty-icon"></i>
        <h3>ยังไม่มีข้อมูลการค้นหา</h3>
        <p>กรุณากรอกรหัสแจ้งซ่อม หรือชื่อผู้แจ้ง เพื่อตรวจสอบความคืบหน้า</p>
      </div>
    </section>

    <!-- ================= PAGE 4: หน้าประวัติการแจ้งปัญหา (MAINTENANCE HISTORY) ================= -->
    <section id="page-history" class="page-section">
      <div class="page-header">
        <div class="page-title-group">
          <div class="header-icon-badge">
            <i data-lucide="history"></i>
          </div>
          <div>
            <h1 class="page-title">ประวัติการแจ้งปัญหาและงานซ่อมบำรุง</h1>
            <p class="page-desc">ตารางรวบรวมประวัติการแจ้งซ่อมทั้งหมด สามารถค้นหาและกรองสถานะได้ทันที</p>
          </div>
        </div>

        <div class="header-actions">
          <button class="btn btn-outline" onclick="exportHistoryCSV()">
            <i data-lucide="download"></i>
            <span>ส่งออก CSV</span>
          </button>
          <button class="btn btn-primary" onclick="navigateTo('report')">
            <i data-lucide="plus"></i>
            <span>แจ้งปัญหาใหม่</span>
          </button>
        </div>
      </div>

      <!-- Filter & Search Toolbar Card -->
      <div class="card filter-toolbar-card">
        <div class="filter-grid">
          <!-- Search box -->
          <div class="filter-col search-col">
            <label class="filter-label">ค้นหารายการ</label>
            <div class="input-with-icon">
              <i data-lucide="search"></i>
              <input type="text" id="historySearchInput" class="form-control" placeholder="รหัส, ผู้แจ้ง, อุปกรณ์, รุ่น, อาการ..." oninput="debounceHistorySearch()">
            </div>
          </div>

          <!-- Status Filter -->
          <div class="filter-col">
            <label class="filter-label">สถานะงาน</label>
            <select id="historyStatusFilter" class="form-control" onchange="fetchHistory()">
              <option value="all">ทั้งหมด ทุกสถานะ</option>
              <option value="pending">รอดำเนินการ</option>
              <option value="assigned">มอบหมายช่างแล้ว</option>
              <option value="in_progress">กำลังดำเนินการซ่อม</option>
              <option value="completed">ซ่อมเสร็จสิ้น</option>
              <option value="cancelled">ยกเลิก</option>
            </select>
          </div>

          <!-- Equipment Filter (4 Types) -->
          <div class="filter-col">
            <label class="filter-label">ประเภทอุปกรณ์</label>
            <select id="historyEquipmentFilter" class="form-control" onchange="fetchHistory()">
              <option value="all">อุปกรณ์ทั้งหมด</option>
              <option value="คอม">คอม</option>
              <option value="โน็ตบุ๊ค">โน็ตบุ๊ค</option>
              <option value="เครื่องพิม">เครื่องพิม</option>
              <option value="อุปกรณ์เครือข่าย">อุปกรณ์เครือข่าย</option>
            </select>
          </div>

          <!-- Urgency Filter -->
          <div class="filter-col">
            <label class="filter-label">ระดับความเร่งด่วน</label>
            <select id="historyUrgencyFilter" class="form-control" onchange="fetchHistory()">
              <option value="all">ความเร่งด่วนทั้งหมด</option>
              <option value="normal">ปกติ (Normal)</option>
              <option value="urgent">เร่งด่วน (Urgent)</option>
              <option value="emergency">ด่วนที่สุด (Emergency)</option>
            </select>
          </div>

          <!-- Reset Filter -->
          <div class="filter-col btn-col">
            <button class="btn btn-ghost" onclick="resetHistoryFilters()" title="รีเซ็ตตัวกรอง">
              <i data-lucide="rotate-ccw"></i>
              <span>รีเซ็ต</span>
            </button>
          </div>
        </div>
      </div>

      <!-- History Table Card -->
      <div class="card history-table-card">
        <div class="table-meta-bar">
          <div class="meta-count">
            <span>พบรายการแจ้งซ่อมทั้งหมด: <strong id="historyCountBadge">0</strong> รายการ</span>
          </div>
          <div class="meta-tools">
            <button class="btn btn-ghost btn-sm" onclick="printHistoryTable()">
              <i data-lucide="printer"></i>
              <span>พิมพ์ตาราง</span>
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="data-table" id="historyDataTable">
            <thead>
              <tr>
                <th>รหัสติดตาม</th>
                <th>วันที่แจ้ง</th>
                <th>ผู้แจ้ง / แผนก</th>
                <th>อุปกรณ์ & รุ่น</th>
                <th>ปัญหาที่เกิด</th>
                <th>ความเร่งด่วน</th>
                <th>สถานะ</th>
                <th>ช่างผู้ดูแล</th>
                <th class="text-center">ดูข้อมูล</th>
              </tr>
            </thead>
            <tbody id="historyTableBody">
              <tr>
                <td colspan="9" class="text-center py-4">
                  <div class="loading-spinner">
                    <i data-lucide="loader" class="spin"></i>
                    <span>กำลังโหลดประวัติการแจ้งซ่อม...</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

  </main>

  <!-- ================= SUCCESS MODAL (POST SUBMISSION SLIP) ================= -->
  <div class="modal-overlay" id="successModal">
    <div class="modal-dialog">
      <div class="modal-content success-slip-modal">
        <div class="slip-header">
          <div class="slip-success-icon">
            <i data-lucide="check-circle-2"></i>
          </div>
          <h3>แจ้งซ่อมบำรุงสำเร็จ!</h3>
          <p>ระบบได้บันทึกข้อมูลเข้าระบบ NU Support Repair เรียบร้อยแล้ว</p>
        </div>

        <div class="slip-body">
          <div class="slip-code-box">
            <span class="slip-code-label">รหัสติดตามงานซ่อมของคุณ</span>
            <div class="slip-code-display">
              <h2 id="slipTrackingCode">REQ-2026-XXXX</h2>
              <button class="btn-copy-code" onclick="copySlipCode()" title="คัดลอกรหัส">
                <i data-lucide="copy"></i>
              </button>
            </div>
            <p class="slip-hint">กรุณาบันทึกรหัสนี้ไว้เพื่อใช้ตรวจสอบสถานะงานซ่อม</p>
          </div>

          <div class="slip-details-list">
            <div class="slip-row">
              <span>ชื่อผู้แจ้ง:</span>
              <strong id="slipReporter">-</strong>
            </div>
            <div class="slip-row">
              <span>อุปกรณ์:</span>
              <strong id="slipEquipment">-</strong>
            </div>
            <div class="slip-row">
              <span>รุ่น / รหัส:</span>
              <strong id="slipModel">-</strong>
            </div>
            <div class="slip-row">
              <span>ปัญหา:</span>
              <span id="slipIssue" class="text-truncate">-</span>
            </div>
            <div class="slip-row">
              <span>วันที่แจ้ง:</span>
              <span id="slipDate">-</span>
            </div>
          </div>
        </div>

        <div class="slip-actions">
          <button class="btn btn-outline" onclick="printSlipTicket()">
            <i data-lucide="printer"></i>
            <span>พิมพ์ใบรับเรื่อง</span>
          </button>
          <button class="btn btn-primary" onclick="goToTrackingFromSlip()">
            <i data-lucide="search"></i>
            <span>ไปที่หน้าติดตามสถานะ</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ================= DETAIL MODAL ================= -->
  <div class="modal-overlay" id="detailModal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <div class="modal-title-wrap">
            <i data-lucide="clipboard-list" class="text-primary"></i>
            <div>
              <h3 id="modalDetailTitle">รายละเอียดงานแจ้งซ่อม</h3>
              <span class="modal-sub" id="modalDetailCode">REQ-2026-XXXX</span>
            </div>
          </div>
          <button class="btn-modal-close" onclick="closeDetailModal()">
            <i data-lucide="x"></i>
          </button>
        </div>

        <div class="modal-body" id="modalDetailBody">
          <!-- Dynamically populated -->
        </div>

        <div class="modal-footer">
          <button class="btn btn-outline" onclick="closeDetailModal()">ปิดหน้าต่าง</button>
          <button class="btn btn-primary" id="modalGoToTrackBtn">
            <i data-lucide="search"></i>
            <span>เปิดหน้าติดตามสถานะ</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ================= PRINTABLE SLIP CONTAINER (Hidden on Screen) ================= -->
  <div id="printableSlipArea" class="print-only"></div>

  <!-- Toast Notification Container -->
  <div class="toast-container" id="toastContainer"></div>

  <!-- App Script -->
  <script src="/js/app.js"></script>
</body>
</html>
