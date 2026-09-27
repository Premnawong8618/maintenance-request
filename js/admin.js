// =========================================================
// NU Support Repair - Admin & Technician Logic
// =========================================================

// Web Hosting Auto-URL Resolver for /api/
function getAppBase() {
  const pathname = window.location.pathname;
  let baseDir = pathname.substring(0, pathname.lastIndexOf('/') + 1);
  if (!baseDir.endsWith('/')) baseDir += '/';
  return baseDir;
}

// Auto-prefix /api calls for subfolder / shared hosting compatibility
(function() {
  const originalFetch = window.fetch;
  window.fetch = function(resource, init) {
    if (typeof resource === 'string' && resource.startsWith('/api')) {
      resource = getAppBase() + resource.substring(1);
    }
    return originalFetch(resource, init);
  };
})();

let allAdminRequests = [];
let adminSearchTimer = null;

document.addEventListener('DOMContentLoaded', () => {
  // Check auth
  checkAdminAuth();

  // Initialize Lucide icons
  if (window.lucide) lucide.createIcons();
});

// ================= AUTHENTICATION =================
function checkAdminAuth() {
  const isAuth = localStorage.getItem('nu_admin_auth') === 'true';
  const loginSection = document.getElementById('adminLoginSection');
  const dashboardSection = document.getElementById('adminDashboardSection');

  if (isAuth) {
    loginSection.style.display = 'none';
    dashboardSection.style.display = 'block';
    loadAdminDashboard();
  } else {
    loginSection.style.display = 'flex';
    dashboardSection.style.display = 'none';
  }

  setTimeout(() => {
    if (window.lucide) lucide.createIcons();
  }, 50);
}

async function handleAdminLogin(e) {
  e.preventDefault();
  const username = document.getElementById('adminUsername').value.trim();
  const password = document.getElementById('adminPassword').value.trim();
  const btn = document.getElementById('btnAdminLogin');

  btn.disabled = true;
  btn.innerHTML = '<i data-lucide="loader" class="spin"></i> กำลังตรวจสอบ...';
  if (window.lucide) lucide.createIcons();

  try {
    const res = await fetch('/api/admin/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username, password })
    });
    const result = await res.json();

    if (result.success) {
      localStorage.setItem('nu_admin_auth', 'true');
      localStorage.setItem('nu_admin_user', username);
      showToast('เข้าสู่ระบบสำเร็จ', 'ยินดีต้อนรับสู่ระบบจัดการ NU Support Repair', 'success');
      document.getElementById('adminLoginForm').reset();
      checkAdminAuth();
    } else {
      showToast('เข้าสู่ระบบไม่สำเร็จ', result.message || 'Username หรือ Password ไม่ถูกต้อง', 'error');
    }
  } catch (err) {
    showToast('ข้อผิดพลาด', err.message, 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i data-lucide="log-in"></i> <span>เข้าสู่ระบบผู้ดูแล</span>';
    if (window.lucide) lucide.createIcons();
  }
}

function handleAdminLogout() {
  if (confirm('คุณต้องการออกจากระบบผู้ดูแลใช่หรือไม่?')) {
    localStorage.removeItem('nu_admin_auth');
    localStorage.removeItem('nu_admin_user');
    showToast('ออกจากระบบแล้ว', 'ออกจากระบบผู้ดูแลเรียบร้อย', 'info');
    checkAdminAuth();
  }
}

// ================= LOAD DASHBOARD =================
function loadAdminDashboard() {
  fetchAdminStats();
  fetchAdminRequests();
  checkAdminDbStatus();
}

// Switch tabs
function switchAdminTab(tabName) {
  document.querySelectorAll('.admin-tab-btn').forEach(btn => {
    btn.classList.toggle('active', btn.getAttribute('data-tab') === tabName);
  });

  document.querySelectorAll('.admin-tab-content').forEach(content => {
    content.style.display = content.id === `tab-${tabName}` ? 'block' : 'none';
  });

  if (tabName === 'database') {
    checkAdminDbStatus();
  } else if (tabName === 'update') {
    populateTicketSelector();
  }

  setTimeout(() => {
    if (window.lucide) lucide.createIcons();
  }, 50);
}

// ================= STATS =================
async function fetchAdminStats() {
  try {
    const res = await fetch('/api/stats');
    const json = await res.json();
    if (json.success && json.data) {
      document.getElementById('adminStatTotal').innerText = json.data.total || 0;
      document.getElementById('adminStatPending').innerText = json.data.pending || 0;
      document.getElementById('adminStatProgress').innerText = json.data.in_progress || 0;
      document.getElementById('adminStatCompleted').innerText = json.data.completed || 0;
    }
  } catch (err) {
    console.error('Error fetching admin stats:', err);
  }
}

function filterAdminTableBy(status) {
  switchAdminTab('requests');
  document.getElementById('adminStatusFilter').value = status;
  fetchAdminRequests();
}

// ================= REQUESTS TABLE =================
function debounceAdminSearch() {
  clearTimeout(adminSearchTimer);
  adminSearchTimer = setTimeout(() => {
    fetchAdminRequests();
  }, 300);
}

function resetAdminFilters() {
  document.getElementById('adminSearchInput').value = '';
  document.getElementById('adminStatusFilter').value = 'all';
  document.getElementById('adminEquipmentFilter').value = 'all';
  document.getElementById('adminUrgencyFilter').value = 'all';
  fetchAdminRequests();
}

async function fetchAdminRequests() {
  const tableBody = document.getElementById('adminTableBody');
  const countBadge = document.getElementById('adminTableCount');

  const search = document.getElementById('adminSearchInput').value.trim();
  const status = document.getElementById('adminStatusFilter').value;
  const equipment = document.getElementById('adminEquipmentFilter').value;
  const urgency = document.getElementById('adminUrgencyFilter').value;

  const params = new URLSearchParams({
    search,
    status,
    equipment,
    urgency,
    limit: 200
  });

  tableBody.innerHTML = `
    <tr>
      <td colspan="10" class="text-center py-4">
        <div class="loading-spinner">
          <i data-lucide="loader" class="spin"></i>
          <span>กำลังค้นหาข้อมูล...</span>
        </div>
      </td>
    </tr>
  `;
  if (window.lucide) lucide.createIcons();

  try {
    const res = await fetch(`/api/requests?${params.toString()}`);
    const json = await res.json();

    if (json.success && json.data) {
      allAdminRequests = json.data;
      countBadge.innerText = allAdminRequests.length;
      populateTicketSelector();

      if (allAdminRequests.length === 0) {
        tableBody.innerHTML = `
          <tr>
            <td colspan="9" class="text-center py-4 text-gray-400">
              ไม่พบรายการแจ้งซ่อมตามเงื่อนไขที่เลือก
            </td>
          </tr>
        `;
        return;
      }

      let html = '';
      allAdminRequests.forEach(item => {
        html += `
          <tr>
            <td>
              <strong class="table-code" onclick="openAdminEditModal(${item.id})">
                ${escapeHtml(item.tracking_code)}
              </strong>
            </td>
            <td>${formatDate(item.created_at)}</td>
            <td class="table-reporter">
              <strong>${escapeHtml(item.reporter_name)}</strong>
              <span>${escapeHtml(item.department || '-')} ${item.phone ? `(${escapeHtml(item.phone)})` : ''}</span>
            </td>
            <td>
              <strong>${escapeHtml(item.equipment_name)}</strong>
              <div class="text-xs text-gray-500">${escapeHtml(item.equipment_model)}</div>
            </td>
            <td>
              <div class="text-truncate" style="max-width: 220px;" title="${escapeHtml(item.issue_description)}">
                ${escapeHtml(item.issue_description)}
              </div>
            </td>
            <td>
              ${renderUrgencyPill(item.urgency)}
            </td>
            <td>
              ${renderStatusPill(item.status)}
            </td>
            <td>${escapeHtml(item.technician_name || '-')}</td>
            <td class="text-center col-action">
              <div class="table-actions">
                <button type="button" class="btn-table-action edit" onclick="openAdminEditModal(${item.id})" title="อัปเดตสถานะและข้อมูลช่าง">
                  <i data-lucide="edit-3"></i>
                </button>
                <button type="button" class="btn-table-action delete" onclick="deleteTicketAdmin(${item.id}, '${item.tracking_code}')" title="ลบรายการแจ้งซ่อม">
                  <i data-lucide="trash-2"></i>
                </button>
              </div>
            </td>
          </tr>
        `;
      });

      tableBody.innerHTML = html;
      if (window.lucide) lucide.createIcons();
    }
  } catch (err) {
    tableBody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-rose-500">โหลดข้อมูลล้มเหลว: ${err.message}</td></tr>`;
  }
}

// ================= EDIT MODAL FOR TABLE ROWS =================
function openAdminEditModal(id) {
  const item = allAdminRequests.find(r => r.id === id);
  if (!item) return;

  document.getElementById('modalTicketId').value = item.id;
  document.getElementById('modalTicketCode').innerText = `${item.tracking_code} - ${item.equipment_name} (${item.reporter_name})`;
  document.getElementById('modalStatusSelect').value = item.status;
  document.getElementById('modalTechName').value = item.technician_name || '';
  document.getElementById('modalTechNotes').value = item.technician_notes || '';
  document.getElementById('modalEstDate').value = item.estimated_completion || '';

  document.getElementById('adminEditModal').classList.add('active');
  if (window.lucide) lucide.createIcons();
}

function closeAdminEditModal() {
  document.getElementById('adminEditModal').classList.remove('active');
}

function deleteTicketFromCurrentModal() {
  const id = document.getElementById('modalTicketId').value;
  if (!id) return;
  const item = allAdminRequests.find(r => r.id == id);
  const code = item ? item.tracking_code : `ID #${id}`;
  closeAdminEditModal();
  deleteTicketAdmin(id, code);
}

async function handleModalStatusSubmit(e) {
  e.preventDefault();
  const id = document.getElementById('modalTicketId').value;
  if (!id) return;

  const payload = {
    status: document.getElementById('modalStatusSelect').value,
    technician_name: document.getElementById('modalTechName').value.trim(),
    technician_notes: document.getElementById('modalTechNotes').value.trim(),
    estimated_completion: document.getElementById('modalEstDate').value || null
  };

  try {
    const res = await fetch(`/api/requests/${id}/status`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (result.success) {
      showToast('อัปเดตสำเร็จ', 'บันทึกสถานะงานแจ้งซ่อมเรียบร้อยแล้ว', 'success');
      closeAdminEditModal();
      fetchAdminRequests();
      fetchAdminStats();
    } else {
      showToast('ไม่สามารถอัปเดตได้', result.message, 'error');
    }
  } catch (err) {
    showToast('ข้อผิดพลาด', err.message, 'error');
  }
}

// Delete ticket
async function deleteTicketAdmin(id, code) {
  if (confirm(`คุณแน่ใจหรือไม่ว่าต้องการลบรายการแจ้งซ่อม "${code}" ออกจากระบบ?`)) {
    try {
      const res = await fetch(`/api/requests/${id}`, { method: 'DELETE' });
      const json = await res.json();
      if (json.success) {
        showToast('ลบรายการสำเร็จ', `ลบรายการ ${code} เรียบร้อยแล้ว`, 'success');
        fetchAdminRequests();
        fetchAdminStats();
      } else {
        showToast('ไม่สามารถลบได้', json.message, 'error');
      }
    } catch (err) {
      showToast('ข้อผิดพลาด', err.message, 'error');
    }
  }
}

// ================= QUICK UPDATE TAB =================
function populateTicketSelector() {
  const select = document.getElementById('quickSelectTicket');
  if (!select) return;

  let opts = '<option value="" disabled selected>-- เลือกรหัสรายการแจ้งซ่อม --</option>';
  allAdminRequests.forEach(r => {
    opts += `<option value="${r.id}">${escapeHtml(r.tracking_code)} - ${escapeHtml(r.equipment_name)} (${escapeHtml(r.reporter_name)})</option>`;
  });
  select.innerHTML = opts;
}

function loadTicketForUpdate(ticketId) {
  const id = parseInt(ticketId, 10);
  const item = allAdminRequests.find(r => r.id === id);
  if (!item) return;

  document.getElementById('updateTicketId').value = item.id;
  document.getElementById('previewCode').innerText = item.tracking_code;
  document.getElementById('previewReporter').innerText = item.reporter_name;
  document.getElementById('previewEquipment').innerText = item.equipment_name;
  document.getElementById('previewModel').innerText = item.equipment_model;
  document.getElementById('previewIssue').innerText = item.issue_description;

  const badge = document.getElementById('previewStatusBadge');
  badge.className = `status-pill ${item.status}`;
  badge.innerHTML = renderStatusPill(item.status);

  document.getElementById('updateTicketPreview').style.display = 'block';

  document.getElementById('updateStatusSelect').value = item.status;
  document.getElementById('updateTechName').value = item.technician_name || '';
  document.getElementById('updateTechNotes').value = item.technician_notes || '';
  document.getElementById('updateEstDate').value = item.estimated_completion || '';

  if (window.lucide) lucide.createIcons();
}

async function saveStatusUpdate(e) {
  e.preventDefault();
  const id = document.getElementById('updateTicketId').value;
  if (!id) {
    showToast('แจ้งเตือน', 'กรุณาเลือกรหัสแจ้งซ่อมที่ต้องการอัปเดตก่อน', 'info');
    return;
  }

  const payload = {
    status: document.getElementById('updateStatusSelect').value,
    technician_name: document.getElementById('updateTechName').value.trim(),
    technician_notes: document.getElementById('updateTechNotes').value.trim(),
    estimated_completion: document.getElementById('updateEstDate').value || null
  };

  const btn = document.getElementById('btnSaveStatus');
  const origHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i data-lucide="loader" class="spin"></i> กำลังบันทึก...';
  if (window.lucide) lucide.createIcons();

  try {
    const res = await fetch(`/api/requests/${id}/status`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (result.success) {
      showToast('อัปเดตสำเร็จ', 'บันทึกสถานะงานแจ้งซ่อมเรียบร้อยแล้ว', 'success');
      fetchAdminStats();
      fetchAdminRequests();
      loadTicketForUpdate(id);
    } else {
      showToast('ไม่สามารถอัปเดตได้', result.message, 'error');
    }
  } catch (err) {
    showToast('ข้อผิดพลาด', err.message, 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = origHtml;
    if (window.lucide) lucide.createIcons();
  }
}

// ================= MYSQL DATABASE SETTINGS =================
async function checkAdminDbStatus() {
  const banner = document.getElementById('adminDbStatusBanner');
  if (!banner) return;

  try {
    const res = await fetch('/api/db/status');
    const json = await res.json();
    if (json.success) {
      const { isMySQLConnected, databaseType, config, errorMessage } = json.data;

      if (isMySQLConnected) {
        banner.className = 'db-status-banner connected';
        banner.innerHTML = `
          <i data-lucide="check-circle-2"></i>
          <div>
            <strong>เชื่อมต่อ MySQL Server สำเร็จ</strong><br>
            กำลังใช้งานฐานข้อมูล: <code>${config.database}</code> บนโฮสต์ <code>${config.host}:${config.port}</code>
          </div>
        `;
      } else {
        banner.className = 'db-status-banner fallback';
        banner.innerHTML = `
          <i data-lucide="alert-triangle"></i>
          <div>
            <strong>กำลังใช้งาน Local Storage (Fallback)</strong><br>
            <span>สาเหตุ: ${errorMessage || 'ยังไม่ได้เชื่อมต่อกับ MySQL Server'}</span> (ข้อมูลถูกบันทึกไว้อย่างปลอดภัยในเครื่อง)
          </div>
        `;
      }

      document.getElementById('adminDbHost').value = config.host || 'localhost';
      document.getElementById('adminDbPort').value = config.port || 3306;
      document.getElementById('adminDbUser').value = config.user || 'root';
      document.getElementById('adminDbName').value = config.database || 'maintenance_db';
    }
  } catch (err) {
    banner.className = 'db-status-banner fallback';
    banner.innerHTML = `<i data-lucide="x-circle"></i> <div>ไม่สามารถตรวจสอบสถานะฐานข้อมูลได้</div>`;
  }

  if (window.lucide) lucide.createIcons();
}

async function handleAdminDbReconnect(e) {
  e.preventDefault();
  const host = document.getElementById('adminDbHost').value.trim();
  const port = document.getElementById('adminDbPort').value.trim();
  const user = document.getElementById('adminDbUser').value.trim();
  const password = document.getElementById('adminDbPassword').value;
  const database = document.getElementById('adminDbName').value.trim();

  const btn = document.getElementById('btnAdminDbTest');
  const origHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i data-lucide="loader" class="spin"></i> กำลังทดสอบเชื่อมต่อ...';
  if (window.lucide) lucide.createIcons();

  try {
    const res = await fetch('/api/db/reconnect', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ host, port, user, password, database })
    });
    const result = await res.json();
    if (result.success) {
      showToast('เชื่อมต่อสำเร็จ', result.message, 'success');
      checkAdminDbStatus();
      fetchAdminStats();
      fetchAdminRequests();
    } else {
      showToast('การเชื่อมต่อล้มเหลว', result.message, 'error');
    }
  } catch (err) {
    showToast('ข้อผิดพลาด', err.message, 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = origHtml;
    if (window.lucide) lucide.createIcons();
  }
}

// ================= EXPORT CSV =================
function exportAdminCSV() {
  if (allAdminRequests.length === 0) {
    showToast('ไม่มีข้อมูล', 'ไม่พบข้อมูลรายการแจ้งซ่อมสำหรับส่งออก', 'info');
    return;
  }

  const rows = [
    ['รหัสติดตาม', 'วันที่แจ้ง', 'ผู้แจ้ง', 'เบอร์โทร', 'แผนก', 'สถานที่', 'อุปกรณ์', 'รุ่น', 'ปัญหาที่เกิด', 'ความเร่งด่วน', 'สถานะ', 'ช่างผู้ดูแล']
  ];

  allAdminRequests.forEach(r => {
    rows.push([
      r.tracking_code,
      r.created_at,
      `"${(r.reporter_name || '').replace(/"/g, '""')}"`,
      `"${r.phone || ''}"`,
      `"${(r.department || '').replace(/"/g, '""')}"`,
      `"${(r.location || '').replace(/"/g, '""')}"`,
      `"${(r.equipment_name || '').replace(/"/g, '""')}"`,
      `"${(r.equipment_model || '').replace(/"/g, '""')}"`,
      `"${(r.issue_description || '').replace(/"/g, '""')}"`,
      getStatusText(r.urgency),
      getStatusText(r.status),
      `"${(r.technician_name || '').replace(/"/g, '""')}"`
    ]);
  });

  const csvContent = '\uFEFF' + rows.map(e => e.join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.setAttribute('href', url);
  link.setAttribute('download', `nu_support_requests_${new Date().toISOString().slice(0,10)}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);

  showToast('สำเร็จ', 'ส่งออกข้อมูล CSV เรียบร้อยแล้ว', 'success');
}

// ================= HELPERS (NO EMOJIS, MONOCHROME ICONS) =================
function renderStatusPill(status) {
  const map = {
    'pending': { icon: 'clock', text: 'รอดำเนินการ', cls: 'pending' },
    'assigned': { icon: 'user-check', text: 'มอบหมายช่างแล้ว', cls: 'assigned' },
    'in_progress': { icon: 'wrench', text: 'กำลังดำเนินการซ่อม', cls: 'in_progress' },
    'completed': { icon: 'check-circle-2', text: 'ซ่อมเสร็จสิ้น', cls: 'completed' },
    'cancelled': { icon: 'x-circle', text: 'ยกเลิก', cls: 'cancelled' }
  };
  const item = map[status] || { icon: 'help-circle', text: status, cls: 'pending' };
  return `<span class="status-pill ${item.cls}"><i data-lucide="${item.icon}"></i> <span>${item.text}</span></span>`;
}

function renderUrgencyPill(urgency) {
  const map = {
    'normal': { icon: 'check', text: 'ปกติ (Normal)', cls: 'normal' },
    'urgent': { icon: 'alert-circle', text: 'เร่งด่วน (Urgent)', cls: 'urgent' },
    'emergency': { icon: 'flame', text: 'ด่วนที่สุด (Emergency)', cls: 'emergency' }
  };
  const item = map[urgency] || { icon: 'info', text: urgency, cls: 'normal' };
  return `<span class="urgency-pill ${item.cls}"><i data-lucide="${item.icon}"></i> <span>${item.text}</span></span>`;
}

function getStatusText(val) {
  const map = {
    'pending': 'รอดำเนินการ',
    'assigned': 'มอบหมายช่างแล้ว',
    'in_progress': 'กำลังดำเนินการซ่อม',
    'completed': 'ซ่อมเสร็จสิ้น',
    'cancelled': 'ยกเลิก',
    'normal': 'ปกติ',
    'urgent': 'เร่งด่วน',
    'emergency': 'ด่วนที่สุด'
  };
  return map[val] || val;
}

function formatDate(isoString) {
  if (!isoString) return '-';
  try {
    const d = new Date(isoString);
    if (isNaN(d.getTime())) return isoString;
    return d.toLocaleString('th-TH', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  } catch (e) {
    return isoString;
  }
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function showToast(title, message, type = 'info') {
  const container = document.getElementById('toastContainer');
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;

  const iconName = type === 'success' ? 'check-circle' : type === 'error' ? 'alert-triangle' : 'info';

  toast.innerHTML = `
    <i data-lucide="${iconName}"></i>
    <div class="toast-content">
      <div class="toast-title">${escapeHtml(title)}</div>
      <div class="toast-msg">${escapeHtml(message)}</div>
    </div>
  `;

  container.appendChild(toast);
  if (window.lucide) lucide.createIcons();

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}
