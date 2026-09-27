// =========================================================
// NU Support Repair - Client Application Logic
// =========================================================

// Web Hosting Auto-URL Resolver for /api/ and assets
function getAppBase() {
  const pathname = window.location.pathname;
  let baseDir = pathname.substring(0, pathname.lastIndexOf('/') + 1);
  if (!baseDir.endsWith('/')) baseDir += '/';
  return baseDir;
}

function formatImageUrl(url) {
  if (!url) return '';
  if (url.startsWith('http://') || url.startsWith('https://')) return url;
  const clean = url.startsWith('/') ? url.substring(1) : url;
  return getAppBase() + clean;
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

let currentActiveTicket = null;
let searchDebounceTimer = null;
let lastSearchQuery = '';
let lastSearchResults = [];
let isInternalHashChange = false;

document.addEventListener('DOMContentLoaded', () => {
  // Initialize Lucide icons
  if (window.lucide) lucide.createIcons();

  // Load initial application data
  fetchStats();
  fetchRecentRequests();

  // Handle URL hash routing
  handleHashChange();
  window.addEventListener('hashchange', handleHashChange);
});

// ================= ROUTING & NAVIGATION =================
function navigateTo(pageId, updateHash = true) {
  // Update nav buttons
  document.querySelectorAll('.nav-item').forEach(btn => {
    btn.classList.toggle('active', btn.getAttribute('data-page') === pageId);
  });

  // Switch pages
  document.querySelectorAll('.page-section').forEach(sec => {
    sec.classList.remove('active');
  });

  const targetPage = document.getElementById(`page-${pageId}`);
  if (targetPage) {
    targetPage.classList.add('active');
  }

  if (updateHash) {
    window.location.hash = pageId;
  }

  // Trigger page-specific loads
  if (pageId === 'home') {
    fetchStats();
    fetchRecentRequests();
  } else if (pageId === 'history') {
    fetchHistory();
  }

  window.scrollTo({ top: 0, behavior: 'smooth' });

  // Re-render icons
  setTimeout(() => {
    if (window.lucide) lucide.createIcons();
  }, 60);
}

function handleHashChange() {
  if (isInternalHashChange) {
    isInternalHashChange = false;
    return;
  }
  const hash = window.location.hash.replace('#', '') || 'home';
  if (hash.startsWith('track?q=')) {
    const query = decodeURIComponent(hash.split('q=')[1] || '');
    navigateTo('track', false);
    if (query) {
      document.getElementById('trackSearchInput').value = query;
      executeTrackSearch(query, true);
    }
  } else if (hash.startsWith('track?code=')) {
    const code = decodeURIComponent(hash.split('code=')[1] || '');
    navigateTo('track', false);
    if (code) {
      document.getElementById('trackSearchInput').value = code;
      executeTrackSearch(code, true);
    }
  } else if (['home', 'report', 'track', 'history'].includes(hash)) {
    navigateTo(hash, false);
  } else {
    navigateTo('home', false);
  }
}

// ================= STATS & OVERVIEW =================
async function fetchStats() {
  try {
    const res = await fetch('/api/stats');
    const json = await res.json();
    if (json.success && json.data) {
      animateCounter('statTotal', json.data.total || 0);
      animateCounter('statPending', json.data.pending || 0);
      animateCounter('statProgress', json.data.in_progress || 0);
      animateCounter('statCompleted', json.data.completed || 0);
    }
  } catch (err) {
    console.error('Error fetching stats:', err);
  }
}

function animateCounter(elementId, targetValue) {
  const el = document.getElementById(elementId);
  if (!el) return;
  const start = parseInt(el.innerText, 10) || 0;
  const duration = 600;
  const stepTime = 20;
  const steps = duration / stepTime;
  const increment = (targetValue - start) / steps;
  let current = start;

  const timer = setInterval(() => {
    current += increment;
    if ((increment >= 0 && current >= targetValue) || (increment < 0 && current <= targetValue)) {
      el.innerText = targetValue;
      clearInterval(timer);
    } else {
      el.innerText = Math.round(current);
    }
  }, stepTime);
}

// Recent requests feed on home page
async function fetchRecentRequests() {
  const container = document.getElementById('recentRequestsList');
  if (!container) return;

  try {
    const res = await fetch('/api/requests?limit=5');
    const json = await res.json();
    if (json.success && json.data) {
      const items = json.data;
      if (items.length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-gray-400">ยังไม่มีรายการแจ้งซ่อม</div>';
        return;
      }

      let html = '';
      items.forEach(item => {
        const iconName = getEquipmentIcon(item.equipment_name);
        html += `
          <div class="recent-item" onclick="openTicketInTrack('${item.tracking_code}')">
            <div class="recent-item-left">
              <div class="recent-item-icon">
                <i data-lucide="${iconName}"></i>
              </div>
              <div class="recent-item-info">
                <span class="recent-item-code">${escapeHtml(item.tracking_code)}</span>
                <span class="recent-item-desc">${escapeHtml(item.equipment_name)}: ${escapeHtml(item.issue_description)}</span>
                <span class="recent-item-sub">โดย: ${escapeHtml(item.reporter_name)} | ${formatDate(item.created_at)}</span>
              </div>
            </div>
            <div class="recent-item-right">
              ${renderStatusPillHtml(item.status)}
              ${renderUrgencyPillHtml(item.urgency)}
            </div>
          </div>
        `;
      });
      container.innerHTML = html;
      if (window.lucide) lucide.createIcons();
    }
  } catch (err) {
    container.innerHTML = '<div class="text-center py-4 text-rose-500">โหลดข้อมูลล้มเหลว</div>';
  }
}

// Shortcut from Home Page Equipment Cards
function selectEquipmentAndReport(equipmentName) {
  navigateTo('report');
  pickEquipment(equipmentName);
  const modelInput = document.getElementById('equipmentModel');
  if (modelInput) {
    setTimeout(() => modelInput.focus(), 300);
  }
}

// ================= FORM: REPORT ISSUE =================
// 4 Equipment Selection Sync
function pickEquipment(eqName) {
  const select = document.getElementById('equipmentName');
  if (select) {
    select.value = eqName;
  }
}

function syncEquipmentTiles(eqName) {
  // Graceful no-op retained for backwards compatibility
}

function appendIssueTag(tagText) {
  const textarea = document.getElementById('issueDescription');
  if (textarea.value.trim() === '') {
    textarea.value = tagText;
  } else {
    textarea.value += ' ' + tagText;
  }
  textarea.focus();
}

function handleImagePreview(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('imagePreviewImg').src = e.target.result;
      document.getElementById('dropContent').style.display = 'none';
      document.getElementById('dropPreview').style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function removeImagePreview(event) {
  if (event && event.stopPropagation) event.stopPropagation();
  const input = document.getElementById('issueImageInput');
  input.value = '';
  document.getElementById('imagePreviewImg').src = '';
  document.getElementById('dropPreview').style.display = 'none';
  document.getElementById('dropContent').style.display = 'flex';
}

function resetReportForm() {
  document.getElementById('maintenanceReportForm').reset();
  removeImagePreview({ stopPropagation: () => {} });
  syncEquipmentTiles('');
}

async function handleReportSubmit(e) {
  e.preventDefault();

  const form = document.getElementById('maintenanceReportForm');
  const submitBtn = document.getElementById('submitReportBtn');
  const originalBtnHtml = submitBtn.innerHTML;

  const equipmentName = document.getElementById('equipmentName').value;
  if (!equipmentName) {
    showToast('กรุณาเลือกอุปกรณ์', 'โปรดเลือกประเภทอุปกรณ์ที่ต้องการแจ้งซ่อม', 'info');
    return;
  }

  const formData = new FormData();
  formData.append('reporter_name', document.getElementById('reporterName').value.trim());
  formData.append('phone', document.getElementById('reporterPhone').value.trim());
  formData.append('department', document.getElementById('reporterDept').value.trim());
  formData.append('location', document.getElementById('equipmentLocation').value.trim());
  formData.append('equipment_name', equipmentName);
  formData.append('equipment_model', document.getElementById('equipmentModel').value.trim());
  formData.append('issue_description', document.getElementById('issueDescription').value.trim());

  const urgencyRadio = document.querySelector('input[name="urgency"]:checked');
  formData.append('urgency', urgencyRadio ? urgencyRadio.value : 'normal');

  const fileInput = document.getElementById('issueImageInput');
  if (fileInput.files[0]) {
    formData.append('image', fileInput.files[0]);
  }

  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i data-lucide="loader" class="spin"></i> กำลังบันทึกข้อมูล...';
  if (window.lucide) lucide.createIcons();

  try {
    const res = await fetch('/api/requests', {
      method: 'POST',
      body: formData
    });

    const result = await res.json();
    if (result.success && result.data) {
      showToast('สำเร็จ!', 'บันทึกการแจ้งซ่อมเรียบร้อยแล้ว', 'success');
      currentActiveTicket = result.data;
      showSuccessModal(result.data);
      resetReportForm();
      fetchStats();
    } else {
      showToast('เกิดข้อผิดพลาด', result.message || 'ไม่สามารถส่งข้อมูลได้', 'error');
    }
  } catch (err) {
    showToast('เกิดข้อผิดพลาด', err.message, 'error');
  } finally {
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalBtnHtml;
    if (window.lucide) lucide.createIcons();
  }
}

function showSuccessModal(ticket) {
  document.getElementById('slipTrackingCode').innerText = ticket.tracking_code;
  document.getElementById('slipReporter').innerText = ticket.reporter_name;
  document.getElementById('slipEquipment').innerText = ticket.equipment_name;
  document.getElementById('slipModel').innerText = ticket.equipment_model;
  document.getElementById('slipIssue').innerText = ticket.issue_description;
  document.getElementById('slipDate').innerText = formatDate(ticket.created_at);

  document.getElementById('successModal').classList.add('active');
  if (window.lucide) lucide.createIcons();
}

function closeSuccessModal() {
  document.getElementById('successModal').classList.remove('active');
}

function copySlipCode() {
  const code = document.getElementById('slipTrackingCode').innerText;
  navigator.clipboard.writeText(code).then(() => {
    showToast('คัดลอกรหัสแล้ว', `${code}`, 'info');
  });
}

function goToTrackingFromSlip() {
  closeSuccessModal();
  if (currentActiveTicket) {
    openTicketInTrack(currentActiveTicket.tracking_code);
  }
}

// ================= PAGE 3: TRACK STATUS =================
// Supports searching by Tracking Code OR Reporter Name OR Phone!
function quickTrackFromHero() {
  const input = document.getElementById('heroTrackingInput');
  const q = input.value.trim();
  if (!q) {
    showToast('แจ้งเตือน', 'กรุณากรอกรหัสแจ้งซ่อม หรือชื่อผู้แจ้ง', 'info');
    return;
  }
  openTicketInTrack(q);
}

function loadSampleTicket(query) {
  document.getElementById('trackSearchInput').value = query;
  executeTrackSearch(query);
}

function openTicketInTrack(query) {
  navigateTo('track');
  document.getElementById('trackSearchInput').value = query;
  executeTrackSearch(query);
}

async function executeTrackSearch(forcedQuery = null, skipHashUpdate = false) {
  const input = document.getElementById('trackSearchInput');
  const query = (typeof forcedQuery === 'string' ? forcedQuery : input.value).trim();

  if (!query) {
    showToast('แจ้งเตือน', 'กรุณากรอกรหัสแจ้งซ่อม หรือชื่อผู้แจ้งปัญหา', 'info');
    return;
  }

  const btn = document.getElementById('trackSearchBtn');
  const origHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i data-lucide="loader" class="spin"></i> ค้นหา...';
  if (window.lucide) lucide.createIcons();

  const emptyBox = document.getElementById('trackEmptyState');
  const multiBox = document.getElementById('multiResultsContainer');
  const singleBox = document.getElementById('trackResultContainer');
  const backBar = document.getElementById('backToResultsBar');

  try {
    const res = await fetch(`/api/track/search?q=${encodeURIComponent(query)}`);
    const json = await res.json();

    if (json.success && Array.isArray(json.data) && json.data.length > 0) {
      const results = json.data;

      if (results.length === 1) {
        // Direct single ticket view
        multiBox.style.display = 'none';
        emptyBox.style.display = 'none';
        singleBox.style.display = 'block';

        if (lastSearchResults.length > 1) {
          if (backBar) {
            backBar.style.display = 'block';
            document.getElementById('backToResultsCount').innerText = lastSearchResults.length;
          }
        } else {
          if (backBar) backBar.style.display = 'none';
        }

        renderTrackDetails(results[0]);
        if (!skipHashUpdate) {
          isInternalHashChange = true;
          window.location.hash = `track?code=${encodeURIComponent(results[0].tracking_code)}`;
        }
      } else {
        // Multiple tickets found (e.g. searched by reporter name)
        lastSearchQuery = query;
        lastSearchResults = results;

        singleBox.style.display = 'none';
        emptyBox.style.display = 'none';
        if (backBar) backBar.style.display = 'none';
        multiBox.style.display = 'block';
        renderMultiResults(results);
        if (!skipHashUpdate) {
          isInternalHashChange = true;
          window.location.hash = `track?q=${encodeURIComponent(query)}`;
        }
      }
    } else {
      singleBox.style.display = 'none';
      multiBox.style.display = 'none';
      if (backBar) backBar.style.display = 'none';
      emptyBox.style.display = 'block';
      showToast('ไม่พบข้อมูล', 'ไม่พบรายการแจ้งซ่อมตามรหัสหรือชื่อที่ระบุ', 'info');
    }
  } catch (err) {
    showToast('ข้อผิดพลาด', err.message, 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = origHtml;
    if (window.lucide) lucide.createIcons();
  }
}

// Render list when multiple tickets are matched by name
function renderMultiResults(tickets) {
  document.getElementById('multiResultsCount').innerText = tickets.length;
  const listEl = document.getElementById('multiResultsList');

  let html = '';
  tickets.forEach(t => {
    const iconName = getEquipmentIcon(t.equipment_name);
    html += `
      <div class="search-result-card" onclick="selectTrackTicket('${t.tracking_code}')">
        <div class="sr-left">
          <div class="sr-icon">
            <i data-lucide="${iconName}"></i>
          </div>
          <div class="sr-info">
            <span class="sr-code">${escapeHtml(t.tracking_code)}</span>
            <span class="sr-reporter">ผู้แจ้ง: ${escapeHtml(t.reporter_name)} (แผนก: ${escapeHtml(t.department || '-')})</span>
            <span class="sr-equipment">${escapeHtml(t.equipment_name)}: ${escapeHtml(t.equipment_model)} - ${escapeHtml(t.issue_description)}</span>
          </div>
        </div>
        <div class="sr-right">
          ${renderStatusPillHtml(t.status)}
          <button class="btn btn-outline btn-sm">
            <span>ดูสถานะงาน</span>
            <i data-lucide="chevron-right"></i>
          </button>
        </div>
      </div>
    `;
  });

  listEl.innerHTML = html;
  if (window.lucide) lucide.createIcons();
}

function selectTrackTicket(code) {
  const ticket = lastSearchResults.find(t => t.tracking_code === code);
  const singleBox = document.getElementById('trackResultContainer');
  const multiBox = document.getElementById('multiResultsContainer');
  const emptyBox = document.getElementById('trackEmptyState');
  const backBar = document.getElementById('backToResultsBar');

  if (ticket) {
    multiBox.style.display = 'none';
    emptyBox.style.display = 'none';
    singleBox.style.display = 'block';
    if (backBar) {
      backBar.style.display = 'block';
      document.getElementById('backToResultsCount').innerText = lastSearchResults.length;
    }
    renderTrackDetails(ticket);
    isInternalHashChange = true;
    window.location.hash = `track?code=${encodeURIComponent(code)}`;
  } else {
    executeTrackSearch(code);
  }
}

function backToSearchResults() {
  if (lastSearchResults && lastSearchResults.length > 0) {
    document.getElementById('trackResultContainer').style.display = 'none';
    document.getElementById('trackEmptyState').style.display = 'none';
    document.getElementById('multiResultsContainer').style.display = 'block';
    if (lastSearchQuery) {
      document.getElementById('trackSearchInput').value = lastSearchQuery;
      isInternalHashChange = true;
      window.location.hash = `track?q=${encodeURIComponent(lastSearchQuery)}`;
    }
  }
}

function renderTrackDetails(ticket) {
  currentActiveTicket = ticket;

  // Header
  document.getElementById('resTrackingCode').innerText = ticket.tracking_code;
  const statusPill = document.getElementById('resStatusPill');
  statusPill.className = `status-pill ${ticket.status}`;
  statusPill.innerHTML = getStatusPillContent(ticket.status);

  const urgencyPill = document.getElementById('resUrgencyPill');
  urgencyPill.className = `urgency-pill ${ticket.urgency}`;
  urgencyPill.innerHTML = getUrgencyPillContent(ticket.urgency);

  // Stepper Timeline
  updateTimelineStepper(ticket);

  // Equipment & reporter info
  document.getElementById('resEquipmentName').innerText = ticket.equipment_name;
  document.getElementById('resEquipmentModel').innerText = ticket.equipment_model;
  document.getElementById('resIssueDesc').innerText = ticket.issue_description;
  document.getElementById('resReporterName').innerText = ticket.reporter_name;
  document.getElementById('resDepartmentLocation').innerText = `${ticket.department || 'ทั่วไป'} - ${ticket.location || 'สำนักงาน'}`;
  document.getElementById('resPhone').innerText = ticket.phone || 'ไม่ได้ระบุ';
  document.getElementById('resCreatedAt').innerText = formatDate(ticket.created_at);

  // Attached image
  const imgWrap = document.getElementById('resImageContainer');
  if (ticket.image_url) {
    imgWrap.style.display = 'block';
    document.getElementById('resAttachedImage').src = formatImageUrl(ticket.image_url);
  } else {
    imgWrap.style.display = 'none';
  }

  // Technician info
  document.getElementById('resTechName').innerText = ticket.technician_name || 'ยังไม่ได้มอบหมายช่าง';
  document.getElementById('resTechNotes').innerText = ticket.technician_notes || 'อยู่ระหว่างรอการบันทึกจากเจ้าหน้าที่ช่าง';
  document.getElementById('resEstDate').innerText = ticket.estimated_completion ? formatDateOnly(ticket.estimated_completion) : 'ยังไม่ระบุ';

  if (window.lucide) lucide.createIcons();
}

function updateTimelineStepper(ticket) {
  const steps = ['pending', 'assigned', 'in_progress', 'completed'];
  const statusRank = {
    'pending': 1,
    'assigned': 2,
    'in_progress': 3,
    'completed': 4,
    'cancelled': 0
  };

  const currentRank = statusRank[ticket.status] || 1;

  steps.forEach((st, idx) => {
    const el = document.getElementById(`step-${st}`);
    const rank = idx + 1;
    el.className = 't-step';

    if (ticket.status === 'cancelled') {
      if (idx === 0) el.classList.add('done');
    } else {
      if (rank < currentRank) {
        el.classList.add('done');
      } else if (rank === currentRank) {
        el.classList.add('active');
      }
    }
  });

  // Bars
  for (let i = 1; i <= 3; i++) {
    const bar = document.getElementById(`bar-${i}`);
    if (bar) {
      if (currentRank > i && ticket.status !== 'cancelled') {
        bar.className = 't-bar done';
      } else {
        bar.className = 't-bar';
      }
    }
  }

  // Stepper labels
  document.getElementById('stepPendingDate').innerText = formatDate(ticket.created_at);
  document.getElementById('stepAssignedName').innerText = ticket.technician_name ? ticket.technician_name : (currentRank >= 2 ? 'มอบหมายแล้ว' : 'กำลังจัดสรร');
  document.getElementById('stepProgressNote').innerText = ticket.status === 'in_progress' ? 'กำลังซ่อมแซม' : (currentRank > 3 ? 'ผ่านการซ่อมแล้ว' : 'รอเริ่มซ่อม');
  document.getElementById('stepCompletedDate').innerText = ticket.status === 'completed' ? 'ปิดงานเรียบร้อย' : 'รอส่งมอบ';
}

function copyTrackingCode() {
  const code = document.getElementById('resTrackingCode').innerText;
  navigator.clipboard.writeText(code).then(() => {
    showToast('คัดลอกรหัสแล้ว', `${code}`, 'info');
  });
}

// ================= PAGE 4: MAINTENANCE HISTORY =================
function debounceHistorySearch() {
  clearTimeout(searchDebounceTimer);
  searchDebounceTimer = setTimeout(() => {
    fetchHistory();
  }, 300);
}

function resetHistoryFilters() {
  document.getElementById('historySearchInput').value = '';
  document.getElementById('historyStatusFilter').value = 'all';
  document.getElementById('historyEquipmentFilter').value = 'all';
  document.getElementById('historyUrgencyFilter').value = 'all';
  fetchHistory();
}

function filterHistoryBy(status) {
  navigateTo('history');
  document.getElementById('historyStatusFilter').value = status;
  fetchHistory();
}

async function fetchHistory() {
  const tableBody = document.getElementById('historyTableBody');
  const countBadge = document.getElementById('historyCountBadge');

  const search = document.getElementById('historySearchInput').value.trim();
  const status = document.getElementById('historyStatusFilter').value;
  const equipment = document.getElementById('historyEquipmentFilter').value;
  const urgency = document.getElementById('historyUrgencyFilter').value;

  const queryParams = new URLSearchParams({
    search,
    status,
    equipment,
    urgency,
    limit: 100
  });

  tableBody.innerHTML = `
    <tr>
      <td colspan="9" class="text-center py-4">
        <div class="loading-spinner">
          <i data-lucide="loader" class="spin"></i>
          <span>กำลังค้นหาข้อมูล...</span>
        </div>
      </td>
    </tr>
  `;
  if (window.lucide) lucide.createIcons();

  try {
    const res = await fetch(`/api/requests?${queryParams.toString()}`);
    const json = await res.json();

    if (json.success && json.data) {
      const list = json.data;
      countBadge.innerText = list.length;

      if (list.length === 0) {
        tableBody.innerHTML = `
          <tr>
            <td colspan="9" class="text-center py-4 text-gray-400">
              ไม่พบประวัติการแจ้งซ่อมตามเงื่อนไขที่เลือก
            </td>
          </tr>
        `;
        return;
      }

      let rowsHtml = '';
      list.forEach(item => {
        rowsHtml += `
          <tr>
            <td>
              <span class="table-code" onclick="openTicketInTrack('${item.tracking_code}')" title="คลิกเพื่อติดตามสถานะ">
                ${escapeHtml(item.tracking_code)}
              </span>
            </td>
            <td>${formatDate(item.created_at)}</td>
            <td class="table-reporter">
              <strong>${escapeHtml(item.reporter_name)}</strong>
              <span>${escapeHtml(item.department || 'ทั่วไป')} ${item.phone ? `(${escapeHtml(item.phone)})` : ''}</span>
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
              ${renderUrgencyPillHtml(item.urgency)}
            </td>
            <td>
              ${renderStatusPillHtml(item.status)}
            </td>
            <td>${escapeHtml(item.technician_name || '-')}</td>
            <td class="text-center">
              <div class="table-actions">
                <button class="btn-table-action" onclick="openDetailModal(${item.id})" title="ดูรายละเอียด">
                  <i data-lucide="eye"></i>
                </button>
                <button class="btn-table-action" onclick="openTicketInTrack('${item.tracking_code}')" title="ติดตามสถานะ">
                  <i data-lucide="search"></i>
                </button>
              </div>
            </td>
          </tr>
        `;
      });

      tableBody.innerHTML = rowsHtml;
      if (window.lucide) lucide.createIcons();
    }
  } catch (err) {
    tableBody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-rose-500">โหลดข้อมูลล้มเหลว: ${err.message}</td></tr>`;
  }
}

// Detail Modal
async function openDetailModal(id) {
  try {
    const res = await fetch(`/api/requests?limit=100`);
    const json = await res.json();
    if (!json.success || !json.data) return;

    const item = json.data.find(r => r.id === id);
    if (!item) return;

    document.getElementById('modalDetailTitle').innerText = `${item.equipment_name} (${item.equipment_model})`;
    document.getElementById('modalDetailCode').innerText = item.tracking_code;

    const body = document.getElementById('modalDetailBody');
    body.innerHTML = `
      <div class="info-list">
        <div class="info-row">
          <span class="info-label">รหัสติดตาม:</span>
          <span class="info-value highlight">${escapeHtml(item.tracking_code)}</span>
        </div>
        <div class="info-row">
          <span class="info-label">สถานะปัจจุบัน:</span>
          ${renderStatusPillHtml(item.status)}
        </div>
        <div class="info-row">
          <span class="info-label">ระดับความเร่งด่วน:</span>
          ${renderUrgencyPillHtml(item.urgency)}
        </div>
        <div class="info-row">
          <span class="info-label">ผู้แจ้งปัญหา:</span>
          <span class="info-value">${escapeHtml(item.reporter_name)} (แผนก: ${escapeHtml(item.department || '-')})</span>
        </div>
        <div class="info-row">
          <span class="info-label">เบอร์ติดต่อ / สถานที่:</span>
          <span class="info-value">${escapeHtml(item.phone || '-')} / ${escapeHtml(item.location || '-')}</span>
        </div>
        <div class="info-row">
          <span class="info-label">ปัญหาที่เกิด:</span>
          <div class="info-box-desc">${escapeHtml(item.issue_description)}</div>
        </div>
        <div class="info-row">
          <span class="info-label">ช่างผู้รับผิดชอบ:</span>
          <span class="info-value">${escapeHtml(item.technician_name || 'ยังไม่ได้มอบหมาย')}</span>
        </div>
        <div class="info-row">
          <span class="info-label">บันทึกของช่าง:</span>
          <div class="info-box-desc">${escapeHtml(item.technician_notes || 'ไม่มีบันทึก')}</div>
        </div>
        <div class="info-row">
          <span class="info-label">วันที่แจ้ง:</span>
          <span class="info-value">${formatDate(item.created_at)}</span>
        </div>
      </div>
      ${item.image_url ? `
        <div style="margin-top: 15px;">
          <span class="info-label">รูปภาพปัญหา:</span>
          <div class="attached-img-wrap"><img src="${formatImageUrl(item.image_url)}" alt="รูปภาพปัญหา"></div>
        </div>
      ` : ''}
    `;

    document.getElementById('modalGoToTrackBtn').onclick = () => {
      closeDetailModal();
      openTicketInTrack(item.tracking_code);
    };

    document.getElementById('detailModal').classList.add('active');
    if (window.lucide) lucide.createIcons();
  } catch (err) {
    showToast('ข้อผิดพลาด', err.message, 'error');
  }
}

function closeDetailModal() {
  document.getElementById('detailModal').classList.remove('active');
}

// Export CSV
async function exportHistoryCSV() {
  try {
    const res = await fetch('/api/requests?limit=1000');
    const json = await res.json();
    if (!json.success || !json.data || json.data.length === 0) {
      showToast('ไม่มีข้อมูล', 'ไม่พบข้อมูลสำหรับส่งออก', 'info');
      return;
    }

    const rows = [
      ['รหัสติดตาม', 'วันที่แจ้ง', 'ผู้แจ้ง', 'เบอร์โทร', 'แผนก', 'สถานที่', 'อุปกรณ์', 'รุ่น', 'ปัญหาที่เกิด', 'ความเร่งด่วน', 'สถานะ', 'ช่างผู้ดูแล']
    ];

    json.data.forEach(r => {
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
    link.setAttribute('download', `nu_support_repair_${new Date().toISOString().slice(0,10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    showToast('สำเร็จ', 'ส่งออกไฟล์ CSV เรียบร้อยแล้ว', 'success');
  } catch (err) {
    showToast('ข้อผิดพลาด', err.message, 'error');
  }
}

// Print functions
function printSlipTicket() {
  if (!currentActiveTicket) return;
  buildPrintableSlip(currentActiveTicket);
  window.print();
}

function printCurrentSlip() {
  if (!currentActiveTicket) return;
  buildPrintableSlip(currentActiveTicket);
  window.print();
}

function printHistoryTable() {
  window.print();
}

function buildPrintableSlip(t) {
  const printArea = document.getElementById('printableSlipArea');
  printArea.innerHTML = `
    <div class="print-slip-box">
      <h2 class="print-slip-title">ใบรับแจ้งซ่อมบำรุง (Maintenance Request Slip)</h2>
      <p style="text-align:center; font-size:12px; color:#555;">NU Support Repair System</p>
      <div class="print-code">${t.tracking_code}</div>
      <table class="print-table">
        <tr><td><strong>วันที่แจ้ง:</strong></td><td>${formatDate(t.created_at)}</td></tr>
        <tr><td><strong>ผู้แจ้งปัญหา:</strong></td><td>${escapeHtml(t.reporter_name)} (แผนก: ${escapeHtml(t.department || '-')})</td></tr>
        <tr><td><strong>เบอร์ติดต่อ:</strong></td><td>${escapeHtml(t.phone || '-')} | สถานที่: ${escapeHtml(t.location || '-')}</td></tr>
        <tr><td><strong>อุปกรณ์:</strong></td><td>${escapeHtml(t.equipment_name)}</td></tr>
        <tr><td><strong>ชื่อรุ่น / รหัส:</strong></td><td>${escapeHtml(t.equipment_model)}</td></tr>
        <tr><td><strong>ปัญหาที่เกิด:</strong></td><td>${escapeHtml(t.issue_description)}</td></tr>
        <tr><td><strong>ระดับความเร่งด่วน:</strong></td><td>${getStatusText(t.urgency)}</td></tr>
        <tr><td><strong>สถานะปัจจุบัน:</strong></td><td>${getStatusText(t.status)}</td></tr>
        <tr><td><strong>ช่างผู้ดูแล:</strong></td><td>${escapeHtml(t.technician_name || 'รอจัดสรร')}</td></tr>
      </table>
      <div style="margin-top:30px; display:flex; justify-content:space-between; font-size:12px;">
        <div style="text-align:center;">ลงชื่อ.........................................<br>(ผู้แจ้งปัญหา)</div>
        <div style="text-align:center;">ลงชื่อ.........................................<br>(เจ้าหน้าที่ช่างผู้รับเรื่อง)</div>
      </div>
    </div>
  `;
}

// ================= TOAST & HELPERS (NO EMOJIS, MONOCHROME ICONS) =================
function renderStatusPillHtml(status) {
  const map = {
    'pending': { icon: 'clock', text: 'รอดำเนินการ', cls: 'pending' },
    'assigned': { icon: 'user-check', text: 'มอบหมายช่างแล้ว', cls: 'assigned' },
    'in_progress': { icon: 'wrench', text: 'กำลังดำเนินการซ่อม', cls: 'in_progress' },
    'completed': { icon: 'check-circle-2', text: 'ซ่อมเสร็จสิ้น', cls: 'completed' },
    'cancelled': { icon: 'x-circle', text: 'ยกเลิก', cls: 'cancelled' }
  };
  const item = map[status] || { icon: 'info', text: status, cls: 'pending' };
  return `<span class="status-pill ${item.cls}"><i data-lucide="${item.icon}"></i> <span>${item.text}</span></span>`;
}

function getStatusPillContent(status) {
  const map = {
    'pending': { icon: 'clock', text: 'รอดำเนินการ' },
    'assigned': { icon: 'user-check', text: 'มอบหมายช่างแล้ว' },
    'in_progress': { icon: 'wrench', text: 'กำลังดำเนินการซ่อม' },
    'completed': { icon: 'check-circle-2', text: 'ซ่อมเสร็จสิ้น' },
    'cancelled': { icon: 'x-circle', text: 'ยกเลิก' }
  };
  const item = map[status] || { icon: 'info', text: status };
  return `<i data-lucide="${item.icon}"></i> <span>${item.text}</span>`;
}

function renderUrgencyPillHtml(urgency) {
  const map = {
    'normal': { icon: 'check', text: 'ปกติ (Normal)', cls: 'normal' },
    'urgent': { icon: 'alert-circle', text: 'เร่งด่วน (Urgent)', cls: 'urgent' },
    'emergency': { icon: 'flame', text: 'ด่วนที่สุด (Emergency)', cls: 'emergency' }
  };
  const item = map[urgency] || { icon: 'info', text: urgency, cls: 'normal' };
  return `<span class="urgency-pill ${item.cls}"><i data-lucide="${item.icon}"></i> <span>${item.text}</span></span>`;
}

function getUrgencyPillContent(urgency) {
  const map = {
    'normal': { icon: 'check', text: 'ปกติ (Normal)' },
    'urgent': { icon: 'alert-circle', text: 'เร่งด่วน (Urgent)' },
    'emergency': { icon: 'flame', text: 'ด่วนที่สุด (Emergency)' }
  };
  const item = map[urgency] || { icon: 'info', text: urgency };
  return `<i data-lucide="${item.icon}"></i> <span>${item.text}</span>`;
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

function getEquipmentIcon(name) {
  if (!name) return 'wrench';
  if (name.includes('คอม') || name.includes('Desktop') || name.includes('PC')) return 'monitor';
  if (name.includes('โน็ตบุ๊ค') || name.includes('โน้ตบุ๊ก') || name.includes('Laptop') || name.includes('Notebook')) return 'laptop';
  if (name.includes('เครื่องพิม') || name.includes('เครื่องพิมพ์') || name.includes('Printer')) return 'printer';
  if (name.includes('เครือข่าย') || name.includes('Network') || name.includes('Wi-Fi') || name.includes('Router')) return 'wifi';
  return 'wrench';
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

function formatDateOnly(isoString) {
  if (!isoString) return '-';
  try {
    const d = new Date(isoString);
    if (isNaN(d.getTime())) return isoString;
    return d.toLocaleDateString('th-TH', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
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
