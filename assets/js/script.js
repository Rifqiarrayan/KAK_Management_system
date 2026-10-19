/**
 * KAK Management System - Frontend JavaScript
 * Converts Google Apps Script API calls to PHP API calls
 */

// Ambil base path dari current URL untuk mendukung nested folders
const getCurrentBasePath = () => {
  const path = window.location.pathname;
  
  // Hapus trailing slash
  let cleanPath = path.replace(/\/$/, '');
  
  // Cari posisi index.php atau file dengan ekstensi
  const parts = cleanPath.split('/').filter(p => p.length > 0);
  
  // Jika path berisi banyak folder, kita perlu tahu folder mana yang adalah "aplikasi root"
  // Untuk nested structure: /PARENT/APP_FOLDER/
  // Kita ambil semua sampai ke folder APP terakhir
  
  let basePath = '/';
  if (parts.length > 0) {
    // Cari folder yang paling mirip dengan nama aplikasi
    for (let i = parts.length - 1; i >= 0; i--) {
      // Jika ini folder aplikasi kita (berisi 'KAK_Management')
      if (parts[i].includes('KAK_Management')) {
        basePath = '/' + parts.slice(0, i + 1).join('/');
        break;
      }
    }
    
    // Jika tidak ketemu, gunakan full path kecuali file yang diakses
    if (basePath === '/') {
      basePath = '/' + parts.join('/');
    }
  }
  
  return basePath;
};

const API_BASE_URL = getCurrentBasePath() + '/api';
const LOGO_URL = 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c3/BPJS_Ketenagakerjaan_logo.svg/3840px-BPJS_Ketenagakerjaan_logo.svg.png';

// DEBUG: Log API Base URL
console.log('[INIT] window.location.pathname:', window.location.pathname);
console.log('[INIT] getCurrentBasePath():', getCurrentBasePath());
console.log('[INIT] API Base URL:', API_BASE_URL);

let currentUser = {
  userId: null,
  namaReviewer: null,
  role: null
};

let pengadaanListCache = [];
let reviewerListCache = [];

let logActivityState = {
  page: 1,
  pageSize: 10
};
let logActivityInitialized = false;
let logSearchTimeout = null;

function getActorPayload() {
  return {
    actorUserId: currentUser.userId,
    actorNama: currentUser.namaReviewer,
    actorRole: currentUser.role
  };
}

function getAccessRole() {
  return (currentUser.role || '').trim();
}

// ===== UTILITY FUNCTIONS =====
function showNotification(message, type = 'info') {
  const alertDiv = document.createElement('div');
  alertDiv.className = `alert alert-${type}`;
  alertDiv.textContent = message;
  
  const mainContent = document.querySelector('.main-content');
  if (mainContent) {
    mainContent.insertBefore(alertDiv, mainContent.firstChild);
  } else {
    document.body.insertBefore(alertDiv, document.body.firstChild);
  }
  
  setTimeout(() => {
    alertDiv.remove();
  }, 5000);
}

function showPage(pageName) {
  document.querySelectorAll('.page').forEach(page => {
    page.classList.remove('active');
  });
  const targetPage = document.getElementById(pageName + 'Page');
  if (targetPage) {
    targetPage.classList.add('active');
  }
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function toggleMobileMenu() {
  const dropdown = document.getElementById('mobileMenu');
  const openIcon = document.querySelector('.hamburger-icon-open');
  const closeIcon = document.querySelector('.hamburger-icon-close');
  
  if (dropdown) {
    const isOpen = dropdown.classList.contains('show');
    if (isOpen) {
      dropdown.classList.remove('show');
      if (openIcon) openIcon.style.display = 'block';
      if (closeIcon) closeIcon.style.display = 'none';
    } else {
      dropdown.classList.add('show');
      if (openIcon) openIcon.style.display = 'none';
      if (closeIcon) closeIcon.style.display = 'block';
    }
  }
}

function closeMobileMenu() {
  const dropdown = document.getElementById('mobileMenu');
  const openIcon = document.querySelector('.hamburger-icon-open');
  const closeIcon = document.querySelector('.hamburger-icon-close');
  if (dropdown) {
    dropdown.classList.remove('show');
    if (openIcon) openIcon.style.display = 'block';
    if (closeIcon) closeIcon.style.display = 'none';
  }
}

function showAdminTab(tabName) {
  document.querySelectorAll('#adminPage .tab').forEach(tab => {
    tab.classList.remove('active');
  });
  document.getElementById(tabName + 'Tab').classList.add('active');
  
  document.querySelectorAll('#adminPage .menu-item').forEach(item => {
    item.classList.remove('active');
  });
  event.target.classList.add('active');
  
  if (tabName === 'dashboard') {
    loadAdminDashboard();
  } else if (tabName === 'pengadaan') {
    loadPengadaanList();
  } else if (tabName === 'logActivity') {
    setupLogActivityHandlers();
    loadLogActivityOptions();
    loadLogActivityList(true);
  } else if (tabName === 'reviewer') {
    loadReviewerList();
  }
}

function showReviewerTab(tabName) {
  document.querySelectorAll('#reviewerPage .tab').forEach(tab => {
    tab.classList.remove('active');
  });
  
  let tabId;
  if (tabName === 'dashboard') {
    tabId = 'reviewerDashboardTab';
  } else if (tabName === 'list') {
    tabId = 'reviewerListTab';
  } else if (tabName === 'review') {
    tabId = 'reviewerReviewTab';
  }
  
  if (tabId) {
    document.getElementById(tabId).classList.add('active');
  }
  
  document.querySelectorAll('#reviewerPage .menu-item').forEach(item => {
    item.classList.remove('active');
  });
  event.target.classList.add('active');
  
  if (tabName === 'dashboard') {
    loadReviewerDashboard();
  } else if (tabName === 'list') {
    loadReviewerPengadaanList();
  } else if (tabName === 'review') {
    loadPengadaanSelectForReview();
  }
}

function formatCurrency(value) {
  return 'Rp ' + parseInt(value).toLocaleString('id-ID');
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('id-ID', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
}

function formatDateTime(dateString) {
  const date = new Date(dateString);
  if (Number.isNaN(date.getTime())) return dateString || '-';
  const formatted = date.toLocaleString('id-ID', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  });
  return formatted.replace(/\//g, '-').replace(',', '');
}

// ===== API CALL HELPER =====
async function callAPI(endpoint, method = 'GET', data = null) {
  try {
    const options = {
      method: method,
      headers: {
        'Content-Type': 'application/json'
      }
    };
    
    // Build API URL - langsung ke api/index.php dengan query parameter
    let fullUrl = `${API_BASE_URL}/index.php?endpoint=${endpoint}`;

    if (data && method === 'GET') {
      const params = new URLSearchParams();
      Object.keys(data).forEach(key => {
        if (data[key] !== undefined && data[key] !== null && data[key] !== '') {
          params.append(key, data[key]);
        }
      });
      const qs = params.toString();
      if (qs) {
        fullUrl += `&${qs}`;
      }
    } else if (data && (method === 'POST' || method === 'PUT')) {
      options.body = JSON.stringify(data);
    }
    console.log('[API DEBUG] Calling:', fullUrl, 'Method:', method);
    
    const response = await fetch(fullUrl, options);
    console.log('[API DEBUG] Response Status:', response.status);
    
    const result = await response.json();
    console.log('[API DEBUG] Response Body:', result);
    
    if (!response.ok) {
      throw new Error(result.message || 'API Error');
    }
    
    return result;
  } catch (error) {
    console.error('[API DEBUG] Error:', error);
    showNotification('Terjadi kesalahan: ' + error.message, 'danger');
    throw error;
  }
}

// ===== INITIALIZATION =====
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('brandLogo').src = LOGO_URL;
});

// ===== LOGIN HANDLER =====
function handleLogin(event) {
  event.preventDefault();
  
  const username = document.getElementById('username').value;
  const password = document.getElementById('password').value;
  const errorDiv = document.getElementById('loginError');
  
  errorDiv.style.display = 'none';
  
  // DEBUG: Log credentials
  console.log('[LOGIN DEBUG] Attempting login with:', {
    username: username,
    password: password.length + ' chars',
    timestamp: new Date().toISOString()
  });
  
  callAPI('authenticate', 'POST', { username, password })
    .then(result => {
      console.log('[LOGIN DEBUG] API Response:', result);
      
      if (result.success) {
        console.log('[LOGIN DEBUG] ✓ Login successful');
        currentUser = {
          userId: result.userId,
          namaReviewer: result.namaReviewer,
          role: result.role
        };
        
        console.log('[LOGIN DEBUG] User data:', currentUser);
        
        if (result.role === 'Admin') {
          showPage('admin');
          document.getElementById('userGreeting').textContent = 'Admin: ' + result.namaReviewer;
          document.getElementById('dashboardTab').classList.add('active');
          document.getElementById('pengadaanTab').classList.remove('active');
          document.getElementById('reviewerTab').classList.remove('active');
          document.querySelectorAll('#adminPage .menu-item').forEach(item => item.classList.remove('active'));
          document.querySelectorAll('#adminPage .menu-item')[0].classList.add('active');
          loadAdminDashboard();
        } else {
          showPage('reviewer');
          document.getElementById('reviewerGreeting').textContent = 'Reviewer: ' + result.namaReviewer;
          document.getElementById('reviewerDashboardTab').classList.add('active');
          document.getElementById('reviewerListTab').classList.remove('active');
          document.getElementById('reviewerReviewTab').classList.remove('active');
          document.querySelectorAll('#reviewerPage .menu-item').forEach(item => item.classList.remove('active'));
          document.querySelectorAll('#reviewerPage .menu-item')[0].classList.add('active');
          loadReviewerDashboard();
          loadPengadaanSelectForReview();
        }
      } else {
        console.log('[LOGIN DEBUG] ✗ Login failed:', result.message);
        errorDiv.textContent = result.message;
        errorDiv.style.display = 'block';
      }
    })
    .catch((error) => {
      console.error('[LOGIN DEBUG] ✗ API Error:', error);
      errorDiv.textContent = 'Terjadi kesalahan saat login. Cek browser console (F12) untuk detail.';
      errorDiv.style.display = 'block';
    });
}

function handleLogout() {
  const payload = {
    userId: currentUser.userId,
    namaUser: currentUser.namaReviewer,
    roleUser: currentUser.role
  };

  callAPI('logout', 'POST', payload)
    .catch(() => {})
    .finally(() => {
      currentUser = { userId: null, namaReviewer: null, role: null };
      showPage('login');
      document.getElementById('loginForm').reset();
      document.getElementById('loginError').style.display = 'none';
    });
}

// ===== ADMIN DASHBOARD =====
function loadAdminDashboard() {
  callAPI('dashboard', 'GET')
    .then(data => {
      const total = data.total || 0;
      const belumReview = data.belumReview || 0;
      const perluRevisi = data.perluRevisi || 0;
      const disetujui = data.disetujui || 0;
      
      document.getElementById('totalPengadaan').textContent = total;
      document.getElementById('belumReview').textContent = belumReview;
      document.getElementById('perluRevisi').textContent = perluRevisi;
      document.getElementById('disetujui').textContent = disetujui;
      
      // Draw Status Review Chart (Donut)
      const statusData = google.visualization.arrayToDataTable([
        ['Status', 'Jumlah'],
        ['Belum Direview', belumReview],
        ['Perlu Revisi', perluRevisi],
        ['Disetujui', disetujui]
      ]);
      
      const statusOptions = {
        title: '',
        pieHole: 0.45,
        colors: ['#D97706', '#DC2626', '#16A34A'],
        backgroundColor: 'transparent',
        legend: { position: 'bottom', textStyle: { fontSize: 12, color: '#4B5563' } },
        chartArea: { width: '100%', height: '85%' },
        fontSize: 12,
        tooltip: { textStyle: { fontSize: 12 } },
        pieSliceTextStyle: { color: '#FFFFFF', fontSize: 12 }
      };
      
      const statusChart = new google.visualization.PieChart(document.getElementById('chartStatusReview'));
      statusChart.draw(statusData, statusOptions);
      
      // Draw Pengadaan per Bulan Chart
      const monthlyData = data.monthlyData || [];
      if (monthlyData.length > 0) {
        const monthlyChartData = google.visualization.arrayToDataTable([
          ['Bulan', 'Pengadaan'],
          ...monthlyData.map(item => [item.month, item.count])
        ]);
        
        const monthlyOptions = {
          title: '',
          legend: { position: 'none' },
          colors: ['#1B5E3B'],
          backgroundColor: 'transparent',
          chartArea: { width: '90%', height: '80%' },
          vAxis: { minValue: 0, textStyle: { fontSize: 11, color: '#6B7280' }, gridlines: { color: '#E5E7EB' } },
          hAxis: { textStyle: { fontSize: 11, color: '#6B7280' }, slantedText: true, slantedTextAngle: 25 },
          bar: { groupWidth: '70%' },
          fontSize: 11
        };
        
        const monthlyChart = new google.visualization.ColumnChart(document.getElementById('chartPengadaanPerBulan'));
        monthlyChart.draw(monthlyChartData, monthlyOptions);
      }
      
      // Draw Review per Reviewer Chart
      const reviewerData = data.reviewerData || [];
      if (reviewerData.length > 0) {
        const reviewerChartData = google.visualization.arrayToDataTable([
          ['Reviewer', 'Total Review'],
          ...reviewerData.map(item => [item.nama, item.count])
        ]);
        
        const reviewerOptions = {
          title: '',
          legend: { position: 'none' },
          colors: ['#2E7D52'],
          backgroundColor: 'transparent',
          chartArea: { width: '90%', height: '80%' },
          vAxis: { minValue: 0, textStyle: { fontSize: 11, color: '#6B7280' }, gridlines: { color: '#E5E7EB' } },
          hAxis: { textStyle: { fontSize: 11, color: '#6B7280' }, slantedText: true, slantedTextAngle: 25 },
          bar: { groupWidth: '70%' },
          fontSize: 11
        };
        
        const reviewerChart = new google.visualization.ColumnChart(document.getElementById('chartReviewPerReviewer'));
        reviewerChart.draw(reviewerChartData, reviewerOptions);
      }

      // Draw review progress by implementation period using unique procurement IDs.
      const reviewProgressData = data.reviewProgressData || [];
      if (reviewProgressData.length > 0) {
        const progressChartData = google.visualization.arrayToDataTable([
          ['Pelaksanaan', 'Pengadaan Direview'],
          ...reviewProgressData.map(item => [item.month, Number(item.count)])
        ]);

        const progressOptions = {
          title: '',
          legend: { position: 'none' },
          colors: ['#16A34A'],
          backgroundColor: 'transparent',
          chartArea: { width: '88%', height: '80%' },
          vAxis: { minValue: 0, textStyle: { fontSize: 11, color: '#6B7280' }, gridlines: { color: '#E5E7EB' } },
          hAxis: { textStyle: { fontSize: 11, color: '#6B7280' }, slantedText: true, slantedTextAngle: 25 },
          pointSize: 6,
          lineWidth: 3,
          fontSize: 11
        };

        const progressChart = new google.visualization.LineChart(document.getElementById('chartTrenProgresReview'));
        progressChart.draw(progressChartData, progressOptions);
      }

      renderActionRequiredList(data.actionRequired || []);
    })
    .catch(() => {
      showNotification('Gagal memuat dashboard', 'danger');
    });
}

function renderActionRequiredList(items) {
  const tbody = document.getElementById('actionRequiredList');
  if (!tbody) return;

  if (items.length === 0) {
    tbody.innerHTML = '<tr><td colspan="4" class="dashboard-empty-state">Tidak ada pengadaan yang membutuhkan tindakan.</td></tr>';
    return;
  }

  tbody.innerHTML = items.map(item => {
    const statusClass = item.statusReview === 'PERLU REVISI' ? 'badge-danger' : 'badge-warning';
    const updatedAt = item.updatedAt || item.tanggalInput || '-';
    return `<tr>
      <td><strong>${item.idPengadaan || '-'}</strong></td>
      <td>${item.namaKegiatan || '-'}</td>
      <td><span class="badge ${statusClass}">${item.statusReview || '-'}</span></td>
      <td>${formatDateTime(updatedAt)}</td>
    </tr>`;
  }).join('');
}

// ===== PENGADAAN MANAGEMENT =====
function loadPengadaanList() {
  Promise.all([
    callAPI('pengadaan', 'GET'),
    callAPI('filter-options', 'GET')
  ])
    .then(([pengadaanResult, optionsResult]) => {
      pengadaanListCache = pengadaanResult.pengadaan || [];
      
      // Populate filter dropdowns
      populateFilterDropdowns(optionsResult);
      
      // Apply filters and display
      applyPengadaanFilters();
      hidePengadaanForm();
    })
    .catch(() => {
      showNotification('Gagal memuat daftar pengadaan', 'danger');
    });
}

function populateFilterDropdowns(options) {
  const kodeSelect = document.getElementById('pengadaanKodeFilter');
  const unitSelect = document.getElementById('pengadaanUnitFilter');
  const pelaksanaanSelect = document.getElementById('pengadaanPelaksanaanFilter');
  
  // Clear and repopulate
  kodeSelect.innerHTML = '<option value="">Semua Kode Kantor</option>';
  (options.kodeKantor || []).forEach(kode => {
    kodeSelect.innerHTML += `<option value="${kode}">${kode}</option>`;
  });
  
  unitSelect.innerHTML = '<option value="">Semua Unit Kerja</option>';
  (options.unitKerja || []).forEach(unit => {
    unitSelect.innerHTML += `<option value="${unit}">${unit}</option>`;
  });
  
  pelaksanaanSelect.innerHTML = '<option value="">Semua Pelaksanaan</option>';
  (options.pelaksanaan || []).forEach(pel => {
    pelaksanaanSelect.innerHTML += `<option value="${pel}">${pel}</option>`;
  });
}

function applyPengadaanFilters() {
  const statusFilter = document.getElementById('pengadaanStatusFilter').value;
  const kodeFilter = document.getElementById('pengadaanKodeFilter').value;
  const unitFilter = document.getElementById('pengadaanUnitFilter').value;
  const pelaksanaanFilter = document.getElementById('pengadaanPelaksanaanFilter').value;
  
  let filtered = pengadaanListCache;
  
  // Apply status filter
  if (statusFilter === 'BELUM_REVIEW') {
    filtered = filtered.filter(p => !['DISETUJUI', 'PERLU REVISI'].includes(p.STATUS_REVIEW));
  } else if (statusFilter !== 'ALL') {
    filtered = filtered.filter(p => p.STATUS_REVIEW === statusFilter);
  }
  
  // Apply other filters
  if (kodeFilter) filtered = filtered.filter(p => p.KODE_KANTOR === kodeFilter);
  if (unitFilter) filtered = filtered.filter(p => p.UNIT_KERJA === unitFilter);
  if (pelaksanaanFilter) filtered = filtered.filter(p => p.PELAKSANAAN_PENGADAAN === pelaksanaanFilter);
  
  displayPengadaanList(filtered);
}

function displayPengadaanList(pengadaanList) {
  const tbody = document.getElementById('pengadaanList');
  tbody.innerHTML = '';
  
  if (pengadaanList.length === 0) {
    tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:20px;">Tidak ada data pengadaan</td></tr>';
    return;
  }
  
  pengadaanList.forEach(item => {
    // Check if KAK exists
    callAPI('kak-get', 'GET', { idPengadaan: item.ID_PENGADAAN })
      .then(kakResult => {
        const kak = kakResult.kak;
        const statusKAK = kak ? 'KAK sudah di input' : 'Belum di input';
        const kakEditBtn = kak 
          ? `<button onclick="editKAK('${item.ID_PENGADAAN}')" class="action-btn action-btn-edit action-btn-small">✏ Edit KAK</button>`
          : '';
        
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${item.ID_PENGADAAN}</td>
          <td>${item.KODE_KANTOR}</td>
          <td>${item.UNIT_KERJA}</td>
          <td>${item.NM_KEGIATAN_BARU}</td>
          <td>${formatCurrency(item.ANGGARAN)}</td>
          <td>${item.PELAKSANAAN_PENGADAAN}</td>
          <td>${statusKAK}<br>${kakEditBtn}</td>
          <td>
            <span class="badge badge-${getStatusBadgeClass(item.STATUS_REVIEW)}">${item.STATUS_REVIEW}</span>
            <div style="font-size:12px;color:#666;margin-top:4px;">Progress: ${item.COMPLETION_PERCENT || 0}%</div>
          </td>
          <td>${item.TANGGAL_INPUT}</td>
          <td>
            <button onclick="editPengadaan('${item.ID_PENGADAAN}')" class="action-btn action-btn-edit action-btn-small">✏ Edit Pengadaan</button>
            <button onclick="downloadPDF('${item.ID_PENGADAAN}')" class="action-btn action-btn-pdf action-btn-small">📄 PDF</button>
            <button onclick="deletePengadaan('${item.ID_PENGADAAN}')" class="action-btn action-btn-delete action-btn-small">🗑 Hapus</button>
          </td>
        `;
        tbody.appendChild(row);
      })
      .catch(error => {
        console.error('Error checking KAK:', error);
        // Jika error saat check KAK, tetap tampilkan row tanpa status KAK
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${item.ID_PENGADAAN}</td>
          <td>${item.KODE_KANTOR}</td>
          <td>${item.UNIT_KERJA}</td>
          <td>${item.NM_KEGIATAN_BARU}</td>
          <td>${formatCurrency(item.ANGGARAN)}</td>
          <td>${item.PELAKSANAAN_PENGADAAN}</td>
          <td>-</td>
          <td>
            <span class="badge badge-${getStatusBadgeClass(item.STATUS_REVIEW)}">${item.STATUS_REVIEW}</span>
            <div style="font-size:12px;color:#666;margin-top:4px;">Progress: ${item.COMPLETION_PERCENT || 0}%</div>
          </td>
          <td>${item.TANGGAL_INPUT}</td>
          <td>
            <button onclick="editPengadaan('${item.ID_PENGADAAN}')" class="action-btn action-btn-edit action-btn-small">✏ Edit Pengadaan</button>
            <button onclick="downloadPDF('${item.ID_PENGADAAN}')" class="action-btn action-btn-pdf action-btn-small">📄 PDF</button>
            <button onclick="deletePengadaan('${item.ID_PENGADAAN}')" class="action-btn action-btn-delete action-btn-small">🗑 Hapus</button>
          </td>
        `;
        tbody.appendChild(row);
      });
  });
}

function getStatusBadgeClass(status) {
  if (status === 'DISETUJUI') return 'success';
  if (status === 'PERLU REVISI') return 'warning';
  return 'info';
}

function showPengadaanForm() {
  document.getElementById('pengadaanFormContainer').style.display = 'block';
  document.getElementById('pengadaanForm').reset();
  document.getElementById('pengadaanStep').style.display = 'block';
  document.getElementById('kakStep').style.display = 'none';
  
  // Reset mode to new
  window.tempPengadaanData = {};
  
  // Reset form handler
  const pengadaanForm = document.getElementById('pengadaanForm');
  pengadaanForm.onsubmit = nextToKAK;
  
  // Reset fields to editable
  document.getElementById('kodKantor').readOnly = false;
  document.getElementById('unitKerja').readOnly = false;
  document.getElementById('nmKegiatan').readOnly = false;
  document.getElementById('anggaran').readOnly = false;
  document.getElementById('pelaksanaan').readOnly = false;
  
  // Reset button text
  const submitBtn = pengadaanForm.querySelector('button[type="submit"]');
  if (submitBtn) {
    submitBtn.textContent = 'Selanjutnya';
  }
  
  // Reset title
  document.getElementById('formStepTitle').textContent = 'Form Pengadaan';
}

function hidePengadaanForm() {
  document.getElementById('pengadaanFormContainer').style.display = 'none';
}

function nextToKAK(event) {
  event.preventDefault();
  
  const kodKantor = document.getElementById('kodKantor').value;
  const unitKerja = document.getElementById('unitKerja').value;
  const nmKegiatan = document.getElementById('nmKegiatan').value;
  const anggaran = document.getElementById('anggaran').value;
  const pelaksanaan = document.getElementById('pelaksanaan').value;
  
  window.tempPengadaanData = { kodKantor, unitKerja, nmKegiatan, anggaran, pelaksanaan };

  const container = document.getElementById('kakFieldsContainerStep');
  container.innerHTML = '';

  const fieldGroup = document.createElement('div');
  fieldGroup.className = 'form-group';
  fieldGroup.innerHTML = `
    <label for="kak_Link_data_kak">Link Data KAK</label>
    <textarea id="kak_Link_data_kak" name="kak_Link_data_kak" style="min-height: 80px;" placeholder="Masukkan link data KAK" required></textarea>
  `;
  container.appendChild(fieldGroup);

  document.getElementById('pengadaanStep').style.display = 'none';
  document.getElementById('kakStep').style.display = 'block';
  document.getElementById('formStepTitle').textContent = 'Form KAK';
}

function formatFieldName(field) {
  if (field === 'Link_data_kak') return 'Link Data KAK';
  if (field === 'PENUTUP') return 'Kesimpulan';
  return field.split('_').map(word => word.charAt(0) + word.slice(1).toLowerCase()).join(' ');
}

function backToPengadaan() {
  document.getElementById('pengadaanStep').style.display = 'block';
  document.getElementById('kakStep').style.display = 'none';
  document.getElementById('formStepTitle').textContent = 'Form Pengadaan';
}

function savePengadaanWithKAK(event) {
  event.preventDefault();
  
  const tempData = window.tempPengadaanData || {};
  const editMode = tempData.editMode || 'new';
  const editingId = tempData.editingId;
  
  // Jika edit KAK only, langsung save KAK
  if (editMode === 'kakOnly' && editingId) {
    const kakData = {};
    document.querySelectorAll('#kakFieldsContainerStep textarea').forEach(textarea => {
      const fieldName = textarea.id.replace('kak_', '');
      kakData[fieldName] = textarea.value;
    });
    
    callAPI('kak-save', 'POST', { idPengadaan: editingId, kakData })
      .then(() => {
        showNotification('Data KAK berhasil disimpan', 'success');
        hidePengadaanForm();
        loadPengadaanList();
      })
      .catch(() => {
        showNotification('Gagal menyimpan data KAK', 'danger');
      });
    return;
  }
  
  // Jika edit pengadaan only, update pengadaan saja
  if (editMode === 'pengadaanOnly' && editingId) {
    callAPI('pengadaan-update', 'POST', {
      idPengadaan: editingId,
      kodKantor: document.getElementById('kodKantor').value,
      unitKerja: document.getElementById('unitKerja').value,
      nmKegiatan: document.getElementById('nmKegiatan').value,
      anggaran: parseInt(document.getElementById('anggaran').value),
      pelaksanaan: document.getElementById('pelaksanaan').value,
      ...getActorPayload()
    })
      .then(() => {
        showNotification('Data Pengadaan berhasil diupdate', 'success');
        hidePengadaanForm();
        loadPengadaanList();
      })
      .catch(() => {
        showNotification('Gagal mengupdate data pengadaan', 'danger');
      });
    return;
  }
  
  // Mode create new pengadaan + KAK
  callAPI('pengadaan', 'POST', {
    kodKantor: tempData.kodKantor || document.getElementById('kodKantor').value,
    unitKerja: tempData.unitKerja || document.getElementById('unitKerja').value,
    nmKegiatan: tempData.nmKegiatan || document.getElementById('nmKegiatan').value,
    anggaran: parseInt(tempData.anggaran || document.getElementById('anggaran').value),
    pelaksanaan: tempData.pelaksanaan || document.getElementById('pelaksanaan').value,
    ...getActorPayload()
  })
    .then(result => {
      const idPengadaan = result.id;
      
      const kakData = {};
      document.querySelectorAll('#kakFieldsContainerStep textarea').forEach(textarea => {
        const fieldName = textarea.id.replace('kak_', '');
        kakData[fieldName] = textarea.value;
      });
      
      return callAPI('kak-save', 'POST', { idPengadaan, kakData });
    })
    .then(() => {
      showNotification('Pengadaan dan KAK berhasil disimpan', 'success');
      hidePengadaanForm();
      loadPengadaanList();
    })
    .catch(() => {
      showNotification('Gagal menyimpan data', 'danger');
    });
}

function editPengadaan(idPengadaan) {
  if (!idPengadaan) {
    showNotification('ID Pengadaan harus diisi', 'danger');
    return;
  }
  
  console.log('Edit Pengadaan for:', idPengadaan);
  
  callAPI('pengadaan-by-id', 'GET', { idPengadaan })
    .then(result => {
      const pengadaan = result.pengadaan;
      
      if (!pengadaan) {
        showNotification('Data pengadaan tidak ditemukan', 'danger');
        return;
      }
      
      // Populate form
      document.getElementById('kodKantor').value = pengadaan.KODE_KANTOR || '';
      document.getElementById('unitKerja').value = pengadaan.UNIT_KERJA || '';
      document.getElementById('nmKegiatan').value = pengadaan.NM_KEGIATAN_BARU || '';
      document.getElementById('anggaran').value = pengadaan.ANGGARAN || '';
      document.getElementById('pelaksanaan').value = pengadaan.PELAKSANAAN_PENGADAAN || '';
      
      // Make fields editable
      document.getElementById('kodKantor').readOnly = false;
      document.getElementById('unitKerja').readOnly = false;
      document.getElementById('nmKegiatan').readOnly = false;
      document.getElementById('anggaran').readOnly = false;
      document.getElementById('pelaksanaan').readOnly = false;
      
      // Store for later
      window.tempPengadaanData = {
        editingId: idPengadaan,
        editMode: 'pengadaanOnly'
      };
      
      // Update form to show save button instead of next button
      const pengadaanForm = document.getElementById('pengadaanForm');
      pengadaanForm.onsubmit = function(event) {
        event.preventDefault();
        savePengadaanOnly();
      };
      
      // Show form
      document.getElementById('pengadaanFormContainer').style.display = 'block';
      document.getElementById('pengadaanStep').style.display = 'block';
      document.getElementById('kakStep').style.display = 'none';
      document.getElementById('formStepTitle').textContent = 'Edit Data Pengadaan';
      
      // Change button text
      const submitBtn = pengadaanForm.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.textContent = 'Simpan Perubahan';
      }
    })
    .catch(() => {
      showNotification('Gagal memuat data pengadaan', 'danger');
    });
}

function savePengadaanOnly() {
  const tempData = window.tempPengadaanData || {};
  const editingId = tempData.editingId;
  
  if (!editingId) {
    showNotification('ID Pengadaan tidak valid', 'danger');
    return;
  }
  
  callAPI('pengadaan-update', 'POST', {
    idPengadaan: editingId,
    kodKantor: document.getElementById('kodKantor').value,
    unitKerja: document.getElementById('unitKerja').value,
    nmKegiatan: document.getElementById('nmKegiatan').value,
    anggaran: parseInt(document.getElementById('anggaran').value),
    pelaksanaan: document.getElementById('pelaksanaan').value,
    ...getActorPayload()
  })
    .then(() => {
      showNotification('Data Pengadaan berhasil diupdate', 'success');
      hidePengadaanForm();
      loadPengadaanList();
      
      // Reset form handler back to normal
      const pengadaanForm = document.getElementById('pengadaanForm');
      pengadaanForm.onsubmit = nextToKAK;
      const submitBtn = pengadaanForm.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.textContent = 'Selanjutnya';
      }
    })
    .catch(() => {
      showNotification('Gagal mengupdate data pengadaan', 'danger');
    });
}

function deletePengadaan(idPengadaan) {
  if (!idPengadaan) {
    showNotification('ID Pengadaan harus diisi', 'danger');
    return;
  }
  
  if (!confirm('Apakah Anda yakin ingin menghapus pengadaan ini?')) return;
  
  callAPI('pengadaan-delete', 'POST', { idPengadaan, ...getActorPayload() })
    .then(() => {
      showNotification('Pengadaan berhasil dihapus', 'success');
      loadPengadaanList();
    })
    .catch(() => {
      showNotification('Gagal menghapus pengadaan', 'danger');
    });
}

// ===== REVIEWER MANAGEMENT =====
function loadReviewerList() {
  callAPI('reviewer-list', 'GET')
    .then(result => {
      reviewerListCache = result.reviewers || [];
      displayReviewerList(reviewerListCache);
      hideReviewerForm();
    })
    .catch(() => {
      showNotification('Gagal memuat daftar reviewer', 'danger');
    });
}

function displayReviewerList(reviewers) {
  const tbody = document.getElementById('reviewerList');
  tbody.innerHTML = '';
  
  reviewers.forEach(reviewer => {
    const row = document.createElement('tr');
    const statusText = reviewer.AKTIF ? 'Aktif' : 'Non-Aktif';
    row.innerHTML = `
      <td>${reviewer.NAMA_REVIEWER}</td>
      <td>${reviewer.USERNAME}</td>
      <td>${reviewer.ROLE}</td>
      <td><span class="badge ${reviewer.AKTIF ? 'badge-success' : 'badge-warning'}">${statusText}</span></td>
      <td>
        <a class="action-btn action-btn-edit action-btn-small" onclick="editReviewer('${reviewer.USER_ID}')">Edit</a>
        <a class="action-btn action-btn-delete action-btn-small" onclick="deleteReviewer('${reviewer.USERNAME}')">Hapus</a>
      </td>
    `;
    tbody.appendChild(row);
  });
}

function showReviewerForm() {
  document.getElementById('reviewerFormContainer').style.display = 'block';
  document.getElementById('reviewerForm').reset();
  document.getElementById('editReviewerId').value = '';
  document.getElementById('reviewerFormTitle').textContent = 'Form Reviewer dan Admin/Notulis';
}

function hideReviewerForm() {
  document.getElementById('reviewerFormContainer').style.display = 'none';
}

function saveReviewer(event) {
  event.preventDefault();
  
  const editId = document.getElementById('editReviewerId').value;
  const namaReviewer = document.getElementById('namaReviewer').value;
  const username = document.getElementById('usernameReviewer').value;
  const password = document.getElementById('passwordReviewer').value;
  const role = document.getElementById('roleReviewer').value;
  const aktif = document.getElementById('activeReviewer').value === 'true';
  
  const endpoint = editId ? 'reviewer-update' : 'reviewer-add';
  const data = {
    userId: editId || 'USR-' + Date.now(),
    namaReviewer,
    username,
    password,
    role,
    aktif,
    ...getActorPayload()
  };
  
  callAPI(endpoint, 'POST', data)
    .then(() => {
      showNotification('Reviewer berhasil disimpan', 'success');
      loadReviewerList();
    })
    .catch(() => {
      showNotification('Gagal menyimpan reviewer', 'danger');
    });
}

function editReviewer(userId) {
  const reviewer = reviewerListCache.find(r => r.USER_ID === userId);
  if (!reviewer) return;
  
  document.getElementById('editReviewerId').value = userId;
  document.getElementById('namaReviewer').value = reviewer.NAMA_REVIEWER;
  document.getElementById('usernameReviewer').value = reviewer.USERNAME;
  document.getElementById('passwordReviewer').value = reviewer.PASSWORD || '';
  document.getElementById('roleReviewer').value = reviewer.ROLE;
  document.getElementById('activeReviewer').value = reviewer.AKTIF ? 'true' : 'false';
  document.getElementById('reviewerFormTitle').textContent = 'Edit Reviewer';
  showReviewerForm();
}

function deleteReviewer(username) {
  if (!confirm('Apakah Anda yakin ingin menghapus reviewer ini?')) return;
  
  callAPI('reviewer-delete', 'POST', { username, ...getActorPayload() })
    .then(() => {
      showNotification('Reviewer berhasil dihapus', 'success');
      loadReviewerList();
    })
    .catch(() => {
      showNotification('Gagal menghapus reviewer', 'danger');
    });
}

// ===== REVIEWER DASHBOARD =====
function loadReviewerDashboard() {
  callAPI('dashboard', 'GET')
    .then(data => {
      const total = data.total || 0;
      const belumReview = data.belumReview || 0;
      const perluRevisi = data.perluRevisi || 0;
      const disetujui = data.disetujui || 0;
      
      document.getElementById('reviewerTotalPengadaan').textContent = total;
      document.getElementById('reviewerBelumReview').textContent = belumReview;
      document.getElementById('reviewerPerluRevisi').textContent = perluRevisi;
      document.getElementById('reviewerDisetujui').textContent = disetujui;
      
      // Draw Status Review Chart
      const statusData = google.visualization.arrayToDataTable([
        ['Status', 'Jumlah'],
        ['Belum Direview', belumReview],
        ['Perlu Revisi', perluRevisi],
        ['Disetujui', disetujui]
      ]);
      
      const statusOptions = {
        title: '',
        pieHole: 0.45,
        colors: ['#D97706', '#DC2626', '#16A34A'],
        backgroundColor: 'transparent',
        legend: { position: 'bottom', textStyle: { fontSize: 12, color: '#4B5563' } },
        chartArea: { width: '100%', height: '85%' },
        fontSize: 12,
        pieSliceTextStyle: { color: '#FFFFFF', fontSize: 12 }
      };
      
      const statusChart = new google.visualization.PieChart(document.getElementById('reviewerChartStatusReview'));
      statusChart.draw(statusData, statusOptions);
      
      // Draw Pengadaan per Bulan Chart
      const monthlyData = data.monthlyData || [];
      if (monthlyData.length > 0) {
        const monthlyChartData = google.visualization.arrayToDataTable([
          ['Bulan', 'Pengadaan'],
          ...monthlyData.map(item => [item.month, item.count])
        ]);
        
        const monthlyOptions = {
          title: '',
          legend: { position: 'none' },
          colors: ['#1B5E3B'],
          backgroundColor: 'transparent',
          chartArea: { width: '90%', height: '80%' },
          vAxis: { minValue: 0, textStyle: { fontSize: 11, color: '#6B7280' }, gridlines: { color: '#E5E7EB' } },
          hAxis: { textStyle: { fontSize: 11, color: '#6B7280' }, slantedText: true, slantedTextAngle: 25 },
          bar: { groupWidth: '70%' },
          fontSize: 11
        };
        
        const monthlyChart = new google.visualization.ColumnChart(document.getElementById('reviewerChartPengadaanPerBulan'));
        monthlyChart.draw(monthlyChartData, monthlyOptions);
      }
    });
}

// ===== REVIEWER PENGADAAN LIST =====
function loadReviewerPengadaanList() {
  callAPI('pengadaan', 'GET')
    .then(result => {
      const pengadaanList = result.pengadaan || [];
      const container = document.getElementById('reviewerPengadaanList');
      container.innerHTML = '';
      
      pengadaanList.forEach(item => {
        const card = document.createElement('div');
        card.className = 'pengadaan-card';
        card.innerHTML = `
          <div class="pengadaan-card-header">
            <div>
              <div class="pengadaan-card-title">${item.NM_KEGIATAN_BARU}</div>
              <div class="pengadaan-card-meta">${item.ID_PENGADAAN}</div>
            </div>
            <span class="badge badge-${getStatusBadgeClass(item.STATUS_REVIEW)}">${item.STATUS_REVIEW}</span>
          </div>
          <div class="pengadaan-card-body">
            <div class="pengadaan-field">
              <div class="pengadaan-field-label">Kode Kantor</div>
              <div class="pengadaan-field-value">${item.KODE_KANTOR}</div>
            </div>
            <div class="pengadaan-field">
              <div class="pengadaan-field-label">Unit Kerja</div>
              <div class="pengadaan-field-value">${item.UNIT_KERJA}</div>
            </div>
            <div class="pengadaan-field">
              <div class="pengadaan-field-label">Anggaran</div>
              <div class="pengadaan-field-value">${formatCurrency(item.ANGGARAN)}</div>
            </div>
            <div class="pengadaan-field">
              <div class="pengadaan-field-label">Pelaksanaan</div>
              <div class="pengadaan-field-value">${item.PELAKSANAAN_PENGADAAN}</div>
            </div>
          </div>
        `;
        container.appendChild(card);
      });
    });
}

// ===== REVIEWER REVIEW =====
function loadPengadaanSelectForReview() {
  callAPI('pengadaan', 'GET')
    .then(result => {
      const pengadaanList = result.pengadaan || [];
      const select = document.getElementById('reviewPengadaanSelect');
      select.innerHTML = '<option value="">-- Pilih Pengadaan --</option>';

      pengadaanList.forEach(item => {
        callAPI('kak-get', 'GET', { idPengadaan: item.ID_PENGADAAN })
          .then(kakResult => {
            if (kakResult.kak) {
              const option = document.createElement('option');
              option.value = item.ID_PENGADAAN;
              option.textContent = `${item.ID_PENGADAAN} - ${item.NM_KEGIATAN_BARU}`;
              select.appendChild(option);
            }
          })
          .catch(() => {
            // ignore kak check failures for dropdown
          });
      });
    });
}

function loadReviewForm() {
  const idPengadaan = document.getElementById('reviewPengadaanSelect').value;
  if (!idPengadaan) {
    document.getElementById('reviewDataContainer').style.display = 'none';
    return;
  }
  
  Promise.all([
    callAPI('pengadaan-by-id', 'GET', { idPengadaan }),
    callAPI('kak-get', 'GET', { idPengadaan }),
    callAPI('kak-fields', 'GET'),
    callAPI('review-get', 'GET', { idPengadaan }),
    callAPI('reviewer-list', 'GET')
  ])
    .then(([pengadaanResult, kakResult, fieldsResult, reviewsResult, reviewerResult]) => {
      const pengadaan = pengadaanResult.pengadaan;
      const kak = kakResult.kak;
      const fields = fieldsResult.fields;
      const existingReviews = reviewsResult.reviews;
      const reviewers = reviewerResult.reviewers || [];
      
      // Display Pengadaan Data
      const pengadaanDiv = document.getElementById('reviewPengadaanData');
      pengadaanDiv.innerHTML = `
        <div class="readonly-field">
          <span class="readonly-label">ID Pengadaan:</span>
          ${pengadaan.ID_PENGADAAN}
        </div>
        <div class="readonly-field">
          <span class="readonly-label">Nama Kegiatan:</span>
          ${pengadaan.NM_KEGIATAN_BARU}
        </div>
        <div class="readonly-field">
          <span class="readonly-label">Kode Kantor:</span>
          ${pengadaan.KODE_KANTOR}
        </div>
        <div class="readonly-field">
          <span class="readonly-label">Unit Kerja:</span>
          ${pengadaan.UNIT_KERJA}
        </div>
        <div class="readonly-field">
          <span class="readonly-label">Anggaran:</span>
          ${formatCurrency(pengadaan.ANGGARAN)}
        </div>
      `;
      
      // Display KAK Data
      const kakDiv = document.getElementById('reviewKAKData');
      kakDiv.innerHTML = '';
      if (kak) {
        const value = kak.Link_data_kak || '-';
        const section = document.createElement('div');
        section.className = 'kak-section';
        section.innerHTML = `
          <div class="readonly-field">
            <span class="readonly-label">Link Data KAK:</span>
            ${value !== '-' ? '<a href="' + value + '" target="_blank">' + value + '</a>' : '-'}
          </div>
        `;
        kakDiv.appendChild(section);
      } else {
        kakDiv.innerHTML = '<div class="alert alert-warning">KAK belum diinput oleh Admin</div>';
      }
      
      // Build Review Form
      const reviewFormDiv = document.getElementById('reviewFormFields');
      reviewFormDiv.innerHTML = '';
      
      fields.forEach(field => {
        const existingReview = existingReviews.find(r => r.BAGIAN_KAK === field);
        const isBulan = (field === 'BULAN');
        
        let html = `
          <div class="review-field">
            <div class="review-field-title">${formatFieldName(field)}</div>
            <div class="review-field-controls">
              <div class="review-field-status">
                <label>Status</label>
                <select name="status_${field}" required onchange="toggleMonthSelect('${field}')">
                  <option value="">-- Pilih --</option>
                  <option value="Sesuai" ${existingReview?.STATUS_REVIEW === 'Sesuai' ? 'selected' : ''}>Sesuai</option>
                  <option value="Perlu Revisi" ${existingReview?.STATUS_REVIEW === 'Perlu Revisi' ? 'selected' : ''}>Perlu Revisi</option>
                </select>
              </div>`;
        
        if (isBulan) {
          html += `
              <div class="review-field-status" id="${field}_bulan_container" style="display:none;">
                <label>Bulan</label>
                <select name="${field}_bulan">
                  <option value="">-- Pilih Bulan --</option>
                  <option value="Januari">Januari</option>
                  <option value="Februari">Februari</option>
                  <option value="Maret">Maret</option>
                  <option value="April">April</option>
                  <option value="Mei">Mei</option>
                  <option value="Juni">Juni</option>
                  <option value="Juli">Juli</option>
                  <option value="Agustus">Agustus</option>
                  <option value="September">September</option>
                  <option value="Oktober">Oktober</option>
                  <option value="November">November</option>
                  <option value="Desember">Desember</option>
                </select>
              </div>`;
        }
        
        html += `
              <div class="review-field-catatan">
                <label>Catatan</label>
                <textarea name="catatan_${field}">${existingReview?.CATATAN_REVIEW || ''}</textarea>
              </div>
            </div>
          </div>`;
        
        reviewFormDiv.innerHTML += html;
      });
      
      // Add Multi-select Reviewer
      let reviewerHtml = `
        <div class="review-field">
          <div class="review-field-title">Reviewer Terlibat</div>
          <div class="review-field-controls reviewer-multi-select">
            <div style="display:flex; align-items:center; gap:8px;">
              <button type="button" class="btn btn-secondary btn-sm" onclick="toggleReviewerDropdown()">Pilih reviewer</button>
              <span id="reviewerDropdownLabel" style="color:#555; font-size:12px;">Belum dipilih</span>
            </div>
            <div id="reviewerDropdownList" class="reviewer-checkboxes" style="display:none;">`;
      
      // Filter only active reviewers with role 'Reviewer'
      const activeReviewers = reviewers.filter(r => r.ROLE === 'Reviewer' && r.AKTIF);
      
      if (activeReviewers.length === 0) {
        reviewerHtml += '<div style="color:#999; font-size:12px;">Tidak ada reviewer aktif</div>';
      } else {
        activeReviewers.forEach(reviewer => {
          reviewerHtml += `
            <label class="reviewer-option">
              <input type="checkbox" name="reviewer_involved" value="${reviewer.NAMA_REVIEWER}" onchange="updateReviewerDropdownLabel()">
              <span>${reviewer.NAMA_REVIEWER}</span>
            </label>`;
        });
      }
      
      reviewerHtml += `
            </div>
          </div>
        </div>`;
      
      reviewFormDiv.innerHTML += reviewerHtml;
      
      document.getElementById('reviewDataContainer').style.display = 'block';
    })
    .catch(() => {
      showNotification('Gagal memuat data untuk review', 'danger');
    });
}

// Toggle month dropdown visibility for BULAN field
function toggleMonthSelect(field) {
  if (field !== 'BULAN') return;
  const statusEl = document.querySelector(`select[name="status_${field}"]`);
  const monthContainer = document.getElementById(`${field}_bulan_container`);
  if (!statusEl || !monthContainer) return;
  const hasStatus = !!statusEl.value;
  monthContainer.style.display = hasStatus ? 'block' : 'none';
}

function toggleReviewerDropdown() {
  const list = document.getElementById('reviewerDropdownList');
  if (!list) return;
  list.style.display = (list.style.display === 'none' || list.style.display === '') ? 'block' : 'none';
}

function updateReviewerDropdownLabel() {
  const selected = Array.from(document.querySelectorAll('input[name="reviewer_involved"]:checked'))
    .map(cb => cb.value)
    .filter(Boolean);
  const label = document.getElementById('reviewerDropdownLabel');
  if (label) {
    label.textContent = selected.length ? selected.join(', ') : 'Belum dipilih';
  }
}

function submitReview(event) {
  event.preventDefault();
  
  const idPengadaan = document.getElementById('reviewPengadaanSelect').value;
  if (!idPengadaan) {
    showNotification('Pilih pengadaan terlebih dahulu', 'danger');
    return;
  }
  
  const reviews = {};
  const fields = document.querySelectorAll('.review-field');
  
  // Collect selected reviewers if multi-select is implemented
  const selectedReviewers = Array.from(document.querySelectorAll('input[name="reviewer_involved"]:checked'))
    .map(cb => cb.value)
    .filter(Boolean);
  
  fields.forEach(field => {
    const statusSelect = field.querySelector('select[name^="status_"]');
    const catatanTextarea = field.querySelector('textarea[name^="catatan_"]');
    
    if (statusSelect && catatanTextarea) {
      const fieldName = statusSelect.name.replace('status_', '');
      
      // Check for BULAN field
      let bulanVal = '';
      if (fieldName === 'BULAN') {
        const bulanSelect = field.querySelector(`select[name="${fieldName}_bulan"]`);
        bulanVal = bulanSelect ? bulanSelect.value : '';
      }
      
      reviews[fieldName] = {
        status: statusSelect.value,
        catatan: catatanTextarea.value,
        bulan: bulanVal
      };
    }
  });
  
  callAPI('review-submit', 'POST', {
    idPengadaan,
    namaReviewer: currentUser.namaReviewer,
    reviews,
    selectedReviewers: selectedReviewers.length > 0 ? selectedReviewers : [currentUser.namaReviewer],
    ...getActorPayload()
  })
    .then(() => {
      showNotification('Review berhasil disubmit', 'success');
      document.getElementById('reviewPengadaanSelect').value = '';
      document.getElementById('reviewDataContainer').style.display = 'none';
      loadPengadaanSelectForReview();
    })
    .catch(() => {
      showNotification('Gagal mensubmit review', 'danger');
    });
}

// ===== FILTER PANEL FUNCTIONS =====
function toggleFilterPanel(panelId) {
  const panel = document.getElementById(panelId);
  if (!panel) return;
  
  const isOpen = panel.classList.contains('active');
  
  // Close all panels first
  document.querySelectorAll('.filter-panel').forEach(p => {
    p.classList.remove('active');
  });
  
  if (!isOpen) {
    panel.classList.add('active');
  }
}

// ===== LOG AKTIVITAS =====
function setupLogActivityHandlers() {
  if (logActivityInitialized) return;
  logActivityInitialized = true;

  const searchInput = document.getElementById('logSearchInput');
  const dateFrom = document.getElementById('logDateFrom');
  const dateTo = document.getElementById('logDateTo');
  const userFilter = document.getElementById('logUserFilter');
  const moduleFilter = document.getElementById('logModuleFilter');
  const activityFilter = document.getElementById('logActivityFilter');
  const pageSizeSelect = document.getElementById('logPageSize');

  if (searchInput) {
    searchInput.addEventListener('input', () => {
      clearTimeout(logSearchTimeout);
      logSearchTimeout = setTimeout(() => {
        logActivityState.page = 1;
        loadLogActivityList();
      }, 300);
    });
  }

  [dateFrom, dateTo, userFilter, moduleFilter, activityFilter].forEach(el => {
    if (el) {
      el.addEventListener('change', () => {
        logActivityState.page = 1;
        loadLogActivityList();
      });
    }
  });

  if (pageSizeSelect) {
    pageSizeSelect.value = String(logActivityState.pageSize);
  }
}

function loadLogActivityOptions() {
  callAPI('log-activity-options', 'GET', { role: getAccessRole() })
    .then(result => {
      const userSelect = document.getElementById('logUserFilter');
      const moduleSelect = document.getElementById('logModuleFilter');
      const activitySelect = document.getElementById('logActivityFilter');

      if (userSelect) {
        userSelect.innerHTML = '<option value="">Semua User</option>';
        (result.users || []).forEach(user => {
          userSelect.innerHTML += `<option value="${user}">${user}</option>`;
        });
      }

      if (moduleSelect) {
        moduleSelect.innerHTML = '<option value="">Semua Modul</option>';
        (result.modules || []).forEach(modul => {
          moduleSelect.innerHTML += `<option value="${modul}">${modul}</option>`;
        });
      }

      if (activitySelect) {
        activitySelect.innerHTML = '<option value="">Semua Aktivitas</option>';
        (result.activities || []).forEach(aktivitas => {
          activitySelect.innerHTML += `<option value="${aktivitas}">${aktivitas}</option>`;
        });
      }
    })
    .catch(() => {
      showNotification('Gagal memuat filter log aktivitas', 'danger');
    });
}

function getLogActivityFilters() {
  return {
    dateFrom: document.getElementById('logDateFrom')?.value || '',
    dateTo: document.getElementById('logDateTo')?.value || '',
    user: document.getElementById('logUserFilter')?.value || '',
    module: document.getElementById('logModuleFilter')?.value || '',
    activity: document.getElementById('logActivityFilter')?.value || '',
    search: document.getElementById('logSearchInput')?.value?.trim() || ''
  };
}

function loadLogActivityList(resetPage = false) {
  if (resetPage) {
    logActivityState.page = 1;
  }

  const filters = getLogActivityFilters();
  callAPI('log-activity-list', 'GET', {
    ...filters,
    page: logActivityState.page,
    pageSize: logActivityState.pageSize,
    role: getAccessRole()
  })
    .then(result => {
      renderLogActivityList(result.items || []);
      updateLogPagination(result.total || 0, result.page || 1, result.pageSize || logActivityState.pageSize);
    })
    .catch(() => {
      showNotification('Gagal memuat log aktivitas', 'danger');
    });
}

function renderLogActivityList(items) {
  const tbody = document.getElementById('logActivityList');
  if (!tbody) return;

  tbody.innerHTML = '';
  if (!items.length) {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:20px;">Tidak ada data log</td></tr>';
    return;
  }

  items.forEach(item => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${formatDateTime(item.Created_at)}</td>
      <td>${item.Nama_user || '-'}</td>
      <td>${item.Role_user || '-'}</td>
      <td>${item.Aktivitas || '-'}</td>
      <td>${item.Modul || '-'}</td>
      <td>${item.Id_referensi || '-'}</td>
    `;
    tbody.appendChild(row);
  });
}

function updateLogPagination(total, page, pageSize) {
  const totalPages = Math.max(1, Math.ceil(total / pageSize));
  logActivityState.page = Math.min(page, totalPages);
  logActivityState.pageSize = pageSize;
  logActivityState.totalPages = totalPages;

  const pageInfo = document.getElementById('logPageInfo');
  if (pageInfo) {
    pageInfo.textContent = `Page ${logActivityState.page} dari ${totalPages} (Total ${total})`;
  }

  const prevBtn = document.getElementById('logPrevBtn');
  const nextBtn = document.getElementById('logNextBtn');
  if (prevBtn) prevBtn.disabled = logActivityState.page <= 1;
  if (nextBtn) nextBtn.disabled = logActivityState.page >= totalPages;
}

function changeLogPage(delta) {
  const nextPage = (logActivityState.page || 1) + delta;
  if (nextPage < 1 || (logActivityState.totalPages && nextPage > logActivityState.totalPages)) return;
  logActivityState.page = nextPage;
  loadLogActivityList();
}

function changeLogPageSize() {
  const pageSizeSelect = document.getElementById('logPageSize');
  if (!pageSizeSelect) return;
  const newSize = parseInt(pageSizeSelect.value, 10);
  logActivityState.pageSize = Number.isNaN(newSize) ? 10 : newSize;
  logActivityState.page = 1;
  loadLogActivityList();
}

function exportLogActivityPDF() {
  const filters = getLogActivityFilters();
  showNotification('Membuat laporan log aktivitas... mohon tunggu', 'info');

  callAPI('log-activity-pdf', 'GET', { ...filters, role: getAccessRole() })
    .then(result => {
      if (result.success && result.html) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(result.html);
        printWindow.document.close();

        printWindow.onload = function() {
          printWindow.print();
        };
        showNotification('PDF siap untuk dicetak', 'success');
      } else {
        showNotification('Gagal membuat PDF', 'danger');
      }
    })
    .catch(() => {
      showNotification('Error membuat PDF', 'danger');
    });
}

// ===== EDIT KAK FUNCTION =====
function editKAK(idPengadaan) {
  if (!idPengadaan) {
    showNotification('ID Pengadaan harus diisi', 'danger');
    return;
  }
  
  console.log('Edit KAK for:', idPengadaan);
  
  Promise.all([
    callAPI('pengadaan-by-id', 'GET', { idPengadaan }),
    callAPI('kak-get', 'GET', { idPengadaan })
  ])
    .then(([pengadaanResult, kakResult]) => {
      const pengadaan = pengadaanResult.pengadaan;
      const kak = kakResult.kak;
      
      // Populate pengadaan fields as read-only
      document.getElementById('kodKantor').value = pengadaan.KODE_KANTOR || '';
      document.getElementById('unitKerja').value = pengadaan.UNIT_KERJA || '';
      document.getElementById('nmKegiatan').value = pengadaan.NM_KEGIATAN_BARU || '';
      document.getElementById('anggaran').value = pengadaan.ANGGARAN || '';
      document.getElementById('pelaksanaan').value = pengadaan.PELAKSANAAN_PENGADAAN || '';
      
      // Make fields read-only
      document.getElementById('kodKantor').readOnly = true;
      document.getElementById('unitKerja').readOnly = true;
      document.getElementById('nmKegiatan').readOnly = true;
      document.getElementById('anggaran').readOnly = true;
      document.getElementById('pelaksanaan').readOnly = true;
      
      // Store for later
      window.tempPengadaanData = {
        editingId: idPengadaan,
        editMode: 'kakOnly'
      };
      
      // Build KAK fields
      const container = document.getElementById('kakFieldsContainerStep');
      container.innerHTML = '';

      const fieldGroup = document.createElement('div');
      fieldGroup.className = 'form-group';
      fieldGroup.innerHTML = `
        <label for="kak_Link_data_kak">Link Data KAK</label>
        <textarea id="kak_Link_data_kak" name="kak_Link_data_kak" style="min-height: 80px;" placeholder="Masukkan link data KAK" required>${kak && kak.Link_data_kak ? kak.Link_data_kak : ''}</textarea>
      `;
      container.appendChild(fieldGroup);
      
      // Show form
      document.getElementById('pengadaanFormContainer').style.display = 'block';
      document.getElementById('pengadaanStep').style.display = 'none';
      document.getElementById('kakStep').style.display = 'block';
      document.getElementById('formStepTitle').textContent = 'Edit Data KAK';
    })
    .catch(() => {
      showNotification('Gagal memuat data KAK', 'danger');
    });
}

// ===== VIEW KAK FUNCTION =====
function viewKAK(idPengadaan) {
  if (!idPengadaan) {
    showNotification('ID Pengadaan harus diisi', 'danger');
    return;
  }
  
  console.log('View KAK for:', idPengadaan);
  
  Promise.all([
    callAPI('kak-get', 'GET', { idPengadaan }),
    callAPI('kak-fields', 'GET')
  ])
    .then(([kakResult]) => {
      const kak = kakResult.kak;
      
      if (!kak) {
        showNotification('KAK tidak ditemukan', 'danger');
        return;
      }
      
      // Create modal overlay
      const overlay = document.createElement('div');
      overlay.className = 'kak-view-modal-overlay';
      overlay.id = 'kakViewOverlay';
      
      let modalHTML = '<div class="kak-view-modal-content">';
      modalHTML += `<h3 class="kak-view-modal-header">Detail KAK - ${idPengadaan}</h3>`;

      const value = kak.Link_data_kak || '-';
      modalHTML += `
        <div class="kak-view-modal-section">
          <span class="kak-view-modal-label">Link Data KAK:</span>
          <p class="kak-view-modal-value">${value}</p>
        </div>
      `;
      
      modalHTML += '<button class="kak-view-modal-close" onclick="closeKAKView()">Tutup</button>';
      modalHTML += '</div>';
      
      overlay.innerHTML = modalHTML;
      document.body.appendChild(overlay);
    })
    .catch(() => {
      showNotification('Gagal memuat KAK', 'danger');
    });
}

function closeKAKView() {
  const overlay = document.getElementById('kakViewOverlay');
  if (overlay) overlay.remove();
}

// ===== COMPREHENSIVE PDF DOWNLOAD =====
function downloadComprehensivePDF() {
  showNotification('Membuat laporan lengkap... mohon tunggu', 'info');
  
  callAPI('comprehensive-pdf', 'GET')
    .then(result => {
      if (result.success && result.html) {
        // Create a new window and write the HTML
        const printWindow = window.open('', '_blank');
        printWindow.document.write(result.html);
        printWindow.document.close();
        
        // Trigger print dialog
        printWindow.onload = function() {
          printWindow.print();
        };
        
        showNotification('PDF siap untuk dicetak', 'success');
      } else {
        showNotification('Gagal membuat PDF', 'danger');
      }
    })
    .catch(() => {
      showNotification('Error membuat PDF', 'danger');
    });
}

// ===== PENGADAAN PDF DOWNLOAD =====
function downloadPDF(idPengadaan) {
  if (!idPengadaan) {
    showNotification('ID Pengadaan harus diisi', 'danger');
    return;
  }

  showNotification('Membuat PDF... mohon tunggu', 'info');

  callAPI('pengadaan-pdf', 'GET', { idPengadaan })
    .then(result => {
      if (result.success && result.html) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(result.html);
        printWindow.document.close();

        printWindow.onload = function() {
          printWindow.print();
        };

        showNotification('PDF siap untuk dicetak', 'success');
      } else {
        showNotification('Gagal membuat PDF', 'danger');
      }
    })
    .catch(() => {
      showNotification('Error membuat PDF', 'danger');
    });
}

// ===== REVIEWER DASHBOARD =====
function loadReviewerDashboard() {
  callAPI('dashboard', 'GET')
    .then(data => {
      const total = data.total || 0;
      const belumReview = data.belumReview || 0;
      const perluRevisi = data.perluRevisi || 0;
      const disetujui = data.disetujui || 0;
      
      document.getElementById('reviewerTotalPengadaan').textContent = total;
      document.getElementById('reviewerBelumReview').textContent = belumReview;
      document.getElementById('reviewerPerluRevisi').textContent = perluRevisi;
      document.getElementById('reviewerDisetujui').textContent = disetujui;
      
      // Draw Status Review Chart (Donut)
      const statusData = google.visualization.arrayToDataTable([
        ['Status', 'Jumlah'],
        ['Belum Direview', belumReview],
        ['Perlu Revisi', perluRevisi],
        ['Disetujui', disetujui]
      ]);
      
      const statusOptions = {
        title: '',
        pieHole: 0.4,
        colors: ['#F59E0B', '#EF4444', '#10B981'],
        legend: { position: 'bottom', textStyle: { fontSize: 12 } },
        chartArea: { width: '100%', height: '85%' },
        fontSize: 12,
        tooltip: { textStyle: { fontSize: 12 } }
      };
      
      const statusChart = new google.visualization.PieChart(document.getElementById('reviewerChartStatusReview'));
      statusChart.draw(statusData, statusOptions);
      
      // Draw Pengadaan per Bulan Chart
      const monthlyData = data.monthlyData || [];
      if (monthlyData.length > 0) {
        const monthlyChartData = google.visualization.arrayToDataTable([
          ['Bulan', 'Pengadaan'],
          ...monthlyData.map(item => [item.month, item.count])
        ]);
        
        const monthlyOptions = {
          title: '',
          legend: { position: 'none' },
          colors: ['#009B4C'],
          chartArea: { width: '90%', height: '80%' },
          vAxis: { minValue: 0, textStyle: { fontSize: 11 } },
          hAxis: { textStyle: { fontSize: 11 } },
          bar: { groupWidth: '75%' },
          fontSize: 11
        };
        
        const monthlyChart = new google.visualization.ColumnChart(document.getElementById('reviewerChartPengadaanPerBulan'));
        monthlyChart.draw(monthlyChartData, monthlyOptions);
      }
    })
    .catch(() => {
      showNotification('Gagal memuat dashboard', 'danger');
    });
}

// ===== REVIEWER PENGADAAN LIST WITH CARD LAYOUT =====
function loadReviewerPengadaanList() {
  Promise.all([
    callAPI('pengadaan', 'GET'),
    callAPI('filter-options', 'GET')
  ])
    .then(([pengadaanResult, optionsResult]) => {
      const pengadaanList = pengadaanResult.pengadaan || [];
      
      // Populate filter dropdowns for reviewer
      populateReviewerFilterDropdowns(optionsResult);
      
      // Apply filters
      applyReviewerFilters(pengadaanList);
    })
    .catch(() => {
      showNotification('Gagal memuat daftar pengadaan', 'danger');
    });
}

function populateReviewerFilterDropdowns(options) {
  const kodeSelect = document.getElementById('reviewerKodeFilter');
  const unitSelect = document.getElementById('reviewerUnitFilter');
  const pelaksanaanSelect = document.getElementById('reviewerPelaksanaanFilter');
  
  kodeSelect.innerHTML = '<option value="">Semua Kode Kantor</option>';
  (options.kodeKantor || []).forEach(kode => {
    kodeSelect.innerHTML += `<option value="${kode}">${kode}</option>`;
  });
  
  unitSelect.innerHTML = '<option value="">Semua Unit Kerja</option>';
  (options.unitKerja || []).forEach(unit => {
    unitSelect.innerHTML += `<option value="${unit}">${unit}</option>`;
  });
  
  pelaksanaanSelect.innerHTML = '<option value="">Semua Pelaksanaan</option>';
  (options.pelaksanaan || []).forEach(pel => {
    pelaksanaanSelect.innerHTML += `<option value="${pel}">${pel}</option>`;
  });
}

function applyReviewerFilters(pengadaanList) {
  const statusFilter = document.getElementById('reviewerStatusFilter').value;
  const kodeFilter = document.getElementById('reviewerKodeFilter').value;
  const unitFilter = document.getElementById('reviewerUnitFilter').value;
  const pelaksanaanFilter = document.getElementById('reviewerPelaksanaanFilter').value;
  
  let filtered = pengadaanList;
  
  // Apply filters
  if (statusFilter === 'BELUM_REVIEW') {
    filtered = filtered.filter(p => !['DISETUJUI', 'PERLU REVISI'].includes(p.STATUS_REVIEW));
  } else if (statusFilter !== 'ALL') {
    filtered = filtered.filter(p => p.STATUS_REVIEW === statusFilter);
  }
  
  if (kodeFilter) filtered = filtered.filter(p => p.KODE_KANTOR === kodeFilter);
  if (unitFilter) filtered = filtered.filter(p => p.UNIT_KERJA === unitFilter);
  if (pelaksanaanFilter) filtered = filtered.filter(p => p.PELAKSANAAN_PENGADAAN === pelaksanaanFilter);
  
  displayReviewerPengadaanList(filtered);
}

function displayReviewerPengadaanList(pengadaanList) {
  const container = document.getElementById('reviewerPengadaanList');
  container.innerHTML = '';
  
  if (pengadaanList.length === 0) {
    container.innerHTML = '<div class="alert alert-info">Tidak ada data dengan filter tersebut</div>';
    return;
  }
  
  let renderedCount = 0;
  let checkedCount = 0;

  pengadaanList.forEach(pengadaan => {
    callAPI('kak-get', 'GET', { idPengadaan: pengadaan.ID_PENGADAAN })
      .then(kakResult => {
        checkedCount += 1;
        const kak = kakResult.kak;

        if (kak) {
          renderedCount += 1;
          const card = document.createElement('div');
          card.className = 'pengadaan-card';
          card.innerHTML = `
            <div class="pengadaan-card-header">
              <div>
                <div class="pengadaan-card-title">${pengadaan.NM_KEGIATAN_BARU}</div>
                <div class="pengadaan-card-meta">ID: ${pengadaan.ID_PENGADAAN}</div>
              </div>
              <span class="badge badge-${getStatusBadgeClass(pengadaan.STATUS_REVIEW)}">${pengadaan.STATUS_REVIEW || 'Belum Review'}</span>
            </div>
            <div class="pengadaan-card-body">
              <div class="pengadaan-field">
                <div class="pengadaan-field-label">Kantor</div>
                <div class="pengadaan-field-value">${pengadaan.KODE_KANTOR}</div>
              </div>
              <div class="pengadaan-field">
                <div class="pengadaan-field-label">Unit Kerja</div>
                <div class="pengadaan-field-value">${pengadaan.UNIT_KERJA}</div>
              </div>
              <div class="pengadaan-field">
                <div class="pengadaan-field-label">Anggaran</div>
                <div class="pengadaan-field-value">${formatCurrency(pengadaan.ANGGARAN)}</div>
              </div>
              <div class="pengadaan-field">
                <div class="pengadaan-field-label">Pelaksanaan</div>
                <div class="pengadaan-field-value">${pengadaan.PELAKSANAAN_PENGADAAN}</div>
              </div>
            </div>
            <div style="text-align:right; display: flex; gap: 8px; justify-content: flex-end;">
              <button class="btn btn-secondary btn-sm" onclick="downloadPDF('${pengadaan.ID_PENGADAAN}')">📄 PDF</button>
              <button class="btn btn-primary btn-sm" onclick="showReviewerTab('review'); setReviewPengadaan('${pengadaan.ID_PENGADAAN}')">Review</button>
            </div>
          `;
          container.appendChild(card);
        }

        if (checkedCount === pengadaanList.length && renderedCount === 0) {
          container.innerHTML = '<div class="alert alert-info">Tidak ada data pengadaan dengan KAK untuk ditampilkan</div>';
        }
      })
      .catch(() => {
        checkedCount += 1;
        if (checkedCount === pengadaanList.length && renderedCount === 0) {
          container.innerHTML = '<div class="alert alert-info">Tidak ada data pengadaan dengan KAK untuk ditampilkan</div>';
        }
      });
  });
}

function setReviewPengadaan(idPengadaan) {
  document.getElementById('reviewPengadaanSelect').value = idPengadaan;
  loadReviewForm();
}

// ===== CLOSE FILTER PANELS ON OUTSIDE CLICK =====
document.addEventListener('click', (e) => {
  const isPanel = e.target.closest('.filter-panel');
  const isToggle = e.target.closest('.filter-toggle');
  if (!isPanel && !isToggle) {
    document.querySelectorAll('.filter-panel').forEach(panel => {
      panel.classList.remove('active');
    });
  }
});

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.filter-panel').forEach(panel => {
      panel.classList.remove('active');
    });
    
    // Also close KAK view modal
    const kakOverlay = document.getElementById('kakViewOverlay');
    if (kakOverlay) kakOverlay.remove();
  }
});
