/**
 * SCRIPT.JS - COMPLETE CLIENT ENGINE & DATABASE LAYER
 * Sistem Manajemen Data Siswa (SMKS PGRI 3 Malang)
 * Mendukung Supabase PostgreSQL REST Cloud & Local Storage Fallback
 */

// ==========================================
// 1. STATE & DATABASE CONFIGURATION
// ==========================================
const DEFAULT_PRODI = [
  { id_prodi: 1, kd_prodi: 'RPL', nama_prodi: 'Rekayasa Perangkat Lunak' },
  { id_prodi: 2, kd_prodi: 'DKV', nama_prodi: 'Desain Komunikasi Visual' },
  { id_prodi: 3, kd_prodi: 'TKR', nama_prodi: 'Teknik Kendaraan Ringan' },
  { id_prodi: 4, kd_prodi: 'BP',  nama_prodi: 'Broadcasting & Perfilman' }
];

const DEFAULT_SISWA = [
  {
    id: 1,
    nis: '25999',
    nama: 'Raffa Hamizan',
    kelas: '10',
    tahun_ajaran: '2025/2026',
    kd_prodi: 'RPL',
    jenis_kelamin: 'L',
    foto: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80'
  }
];

let supabaseClient = null;
let currentView = 'home';
let prodiList = [];
let siswaList = [];

// ==========================================
// 2. SUPABASE / LOCAL DB ADAPTER
// ==========================================
const DB = {
  getCredentials() {
    const url = localStorage.getItem('supabase_url') || '';
    const key = localStorage.getItem('supabase_key') || '';
    return { url, key };
  },

  init() {
    const { url, key } = this.getCredentials();
    if (url && key && window.supabase) {
      try {
        supabaseClient = window.supabase.createClient(url, key);
        this.updateBadge(true, 'Supabase Cloud');
      } catch (e) {
        console.warn('Gagal inisialisasi Supabase, fallback ke LocalStorage:', e);
        supabaseClient = null;
        this.updateBadge(false, 'Local Browser DB');
      }
    } else {
      supabaseClient = null;
      this.updateBadge(false, 'Local Browser DB');
    }
  },

  updateBadge(isCloud, label) {
    const badge = document.getElementById('db-status-badge');
    const text = document.getElementById('db-status-text');
    const statDb = document.getElementById('stat-db-mode');
    if (text) text.textContent = label;
    if (statDb) statDb.textContent = isCloud ? 'Supabase' : 'Local DB';
    if (badge) {
      if (isCloud) {
        badge.style.background = 'rgba(16, 185, 129, 0.15)';
        badge.style.color = '#34d399';
        badge.style.borderColor = 'rgba(16, 185, 129, 0.3)';
      } else {
        badge.style.background = 'rgba(37, 99, 235, 0.15)';
        badge.style.color = '#60a5fa';
        badge.style.borderColor = 'rgba(37, 99, 235, 0.3)';
      }
    }
  },

  // PRODI CRUD
  async getProdi() {
    if (supabaseClient) {
      try {
        const { data, error } = await supabaseClient.from('prodi').select('*').order('kd_prodi', { ascending: true });
        if (!error && data) return data;
        console.warn('Supabase getProdi fallback:', error);
      } catch (err) {
        console.warn('Supabase query error:', err);
      }
    }
    const raw = localStorage.getItem('db_prodi');
    if (!raw) {
      localStorage.setItem('db_prodi', JSON.stringify(DEFAULT_PRODI));
      return DEFAULT_PRODI;
    }
    return JSON.parse(raw);
  },

  async insertProdi(prodi) {
    if (supabaseClient) {
      const { data, error } = await supabaseClient.from('prodi').insert([prodi]).select();
      if (error) throw error;
      return data[0];
    }
    const current = await this.getProdi();
    const exists = current.find(p => p.kd_prodi.toUpperCase() === prodi.kd_prodi.toUpperCase());
    if (exists) throw new Error('Kode Prodi sudah digunakan!');
    prodi.id_prodi = Date.now();
    current.push(prodi);
    localStorage.setItem('db_prodi', JSON.stringify(current));
    return prodi;
  },

  async updateProdi(id, prodi) {
    if (supabaseClient) {
      const { data, error } = await supabaseClient.from('prodi').update(prodi).eq('id_prodi', id).select();
      if (error) throw error;
      return data[0];
    }
    let current = await this.getProdi();
    const conflict = current.find(p => p.kd_prodi.toUpperCase() === prodi.kd_prodi.toUpperCase() && p.id_prodi != id);
    if (conflict) throw new Error('Kode Prodi sudah digunakan oleh program studi lain!');
    current = current.map(p => (p.id_prodi == id ? { ...p, ...prodi } : p));
    localStorage.setItem('db_prodi', JSON.stringify(current));
    return prodi;
  },

  async deleteProdi(id, kd_prodi) {
    const siswa = await this.getSiswa();
    const isUsed = siswa.some(s => s.kd_prodi.toUpperCase() === kd_prodi.toUpperCase());
    if (isUsed) {
      throw new Error('Data tidak bisa dihapus karena masih digunakan oleh data siswa!');
    }

    if (supabaseClient) {
      const { error } = await supabaseClient.from('prodi').delete().eq('id_prodi', id);
      if (error) throw error;
      return true;
    }
    let current = await this.getProdi();
    current = current.filter(p => p.id_prodi != id);
    localStorage.setItem('db_prodi', JSON.stringify(current));
    return true;
  },

  // SISWA CRUD
  async getSiswa() {
    if (supabaseClient) {
      try {
        const { data, error } = await supabaseClient.from('siswa').select('*').order('id', { ascending: false });
        if (!error && data) return data;
        console.warn('Supabase getSiswa fallback:', error);
      } catch (err) {
        console.warn('Supabase query error:', err);
      }
    }
    const raw = localStorage.getItem('db_siswa');
    if (!raw) {
      localStorage.setItem('db_siswa', JSON.stringify(DEFAULT_SISWA));
      return DEFAULT_SISWA;
    }
    return JSON.parse(raw);
  },

  async insertSiswa(siswa) {
    if (supabaseClient) {
      const { data, error } = await supabaseClient.from('siswa').insert([siswa]).select();
      if (error) throw error;
      return data[0];
    }
    const current = await this.getSiswa();
    const exists = current.find(s => s.nis === siswa.nis);
    if (exists) throw new Error('NIS sudah terdaftar!');
    siswa.id = Date.now();
    current.unshift(siswa);
    localStorage.setItem('db_siswa', JSON.stringify(current));
    return siswa;
  },

  async updateSiswa(id, siswa) {
    if (supabaseClient) {
      const { data, error } = await supabaseClient.from('siswa').update(siswa).eq('id', id).select();
      if (error) throw error;
      return data[0];
    }
    let current = await this.getSiswa();
    const conflict = current.find(s => s.nis === siswa.nis && s.id != id);
    if (conflict) throw new Error('NIS sudah digunakan oleh siswa lain!');
    current = current.map(s => (s.id == id ? { ...s, ...siswa } : s));
    localStorage.setItem('db_siswa', JSON.stringify(current));
    return siswa;
  },

  async deleteSiswa(id) {
    if (supabaseClient) {
      const { error } = await supabaseClient.from('siswa').delete().eq('id', id);
      if (error) throw error;
      return true;
    }
    let current = await this.getSiswa();
    current = current.filter(s => s.id != id);
    localStorage.setItem('db_siswa', JSON.stringify(current));
    return true;
  }
};

// ==========================================
// 3. UI HELPERS & NOTIFICATIONS
// ==========================================
function showToast(message, type = 'info') {
  const container = document.getElementById('toast-container');
  if (!container) return;
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
  toast.innerHTML = `<span>${icon}</span><span>${message}</span>`;
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(30px)';
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}

function updateGreetings() {
  const greetingEl = document.getElementById('greeting-text');
  const dateEl = document.getElementById('today-date-text');
  if (!greetingEl || !dateEl) return;

  const now = new Date();
  const jam = now.getHours();
  let ucapan = 'Selamat Datang!';
  if (jam >= 4 && jam < 11) ucapan = 'Selamat Pagi! 🌅';
  else if (jam >= 11 && jam < 15) ucapan = 'Selamat Siang! ☀️';
  else if (jam >= 15 && jam < 18) ucapan = 'Selamat Sore! 🌇';
  else ucapan = 'Selamat Malam! 🌙';

  greetingEl.textContent = `${ucapan} Petugas Data`;
  dateEl.textContent = `Tanggal: ${now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}`;
}

// ==========================================
// 4. NAVIGATION & VIEW CONTROLLER
// ==========================================
function switchView(viewName) {
  currentView = viewName;
  document.querySelectorAll('.page-view').forEach(el => el.style.display = 'none');
  const target = document.getElementById(`view-${viewName}`);
  if (target) target.style.display = 'block';

  document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
    link.classList.toggle('active', link.getAttribute('data-view') === viewName);
  });

  const label = document.getElementById('current-page-label');
  if (label) {
    if (viewName === 'home') label.textContent = 'Dashboard';
    else if (viewName === 'siswa') label.textContent = 'Manajemen Data Siswa';
    else if (viewName === 'prodi') label.textContent = 'Program Studi';
    else if (viewName === 'settings') label.textContent = 'Pengaturan Database';
  }

  // Auto Refresh View Data
  if (viewName === 'home') loadDashboard();
  else if (viewName === 'siswa') renderSiswaTable();
  else if (viewName === 'prodi') renderProdiTable();

  // Close sidebar on mobile
  document.getElementById('sidebar')?.classList.remove('open');
}

// ==========================================
// 5. RENDER FUNCTIONS
// ==========================================
async function loadDashboard() {
  updateGreetings();
  try {
    prodiList = await DB.getProdi();
    siswaList = await DB.getSiswa();

    document.getElementById('stat-total-siswa').textContent = siswaList.length;
    document.getElementById('stat-total-prodi').textContent = prodiList.length;
  } catch (err) {
    console.error('Error loading dashboard:', err);
  }
}

async function renderProdiTable() {
  const tbody = document.getElementById('tbody-prodi');
  const search = document.getElementById('search-prodi')?.value.toLowerCase().trim() || '';
  tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 25px;">Memuat data program studi...</td></tr>';

  try {
    prodiList = await DB.getProdi();
    siswaList = await DB.getSiswa();

    let filtered = prodiList.filter(p => 
      p.kd_prodi.toLowerCase().includes(search) || 
      p.nama_prodi.toLowerCase().includes(search)
    );

    if (filtered.length === 0) {
      tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 30px; color: var(--text-muted);">Tidak ada data program studi ditemukan.</td></tr>';
      return;
    }

    tbody.innerHTML = filtered.map(p => {
      const studentCount = siswaList.filter(s => s.kd_prodi.toUpperCase() === p.kd_prodi.toUpperCase()).length;
      return `
        <tr>
          <td><span class="badge-tag">${escapeHtml(p.kd_prodi)}</span></td>
          <td style="font-weight: 600;">${escapeHtml(p.nama_prodi)}</td>
          <td><span style="color: var(--text-secondary);">${studentCount} Siswa</span></td>
          <td style="text-align: right;">
            <div class="action-buttons" style="justify-content: flex-end;">
              <button class="btn btn-secondary btn-sm" onclick="openEditProdi(${p.id_prodi})">Edit</button>
              <button class="btn btn-danger btn-sm" onclick="confirmDeleteProdi(${p.id_prodi}, '${escapeHtml(p.kd_prodi)}')">Hapus</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; color: var(--danger); padding: 20px;">Gagal memuat prodi: ${err.message}</td></tr>`;
  }
}

async function renderSiswaTable() {
  const tbody = document.getElementById('tbody-siswa');
  const search = document.getElementById('search-siswa')?.value.toLowerCase().trim() || '';
  const filterProdi = document.getElementById('filter-prodi')?.value || '';
  tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 25px;">Memuat data siswa...</td></tr>';

  try {
    siswaList = await DB.getSiswa();
    prodiList = await DB.getProdi();
    populateProdiDropdowns();

    let filtered = siswaList.filter(s => {
      const matchSearch = (s.nama || '').toLowerCase().includes(search) || (s.nis || '').toLowerCase().includes(search);
      const matchProdi = filterProdi === '' || s.kd_prodi === filterProdi;
      return matchSearch && matchProdi;
    });

    if (filtered.length === 0) {
      tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">Tidak ada data siswa ditemukan.</td></tr>';
      return;
    }

    tbody.innerHTML = filtered.map(s => {
      const prodiObj = prodiList.find(p => p.kd_prodi.toUpperCase() === (s.kd_prodi || '').toUpperCase());
      const namaProdi = prodiObj ? prodiObj.nama_prodi : s.kd_prodi;
      const genderBadge = s.jenis_kelamin === 'P' ? '<span class="badge-tag badge-female">Perempuan</span>' : '<span class="badge-tag">Laki-laki</span>';
      const fotoUrl = s.foto || `https://ui-avatars.com/api/?name=${encodeURIComponent(s.nama)}&background=1f4e79&color=fff`;

      return `
        <tr>
          <td>
            <img src="${escapeHtml(fotoUrl)}" alt="${escapeHtml(s.nama)}" class="student-avatar" onerror="this.src='https://ui-avatars.com/api/?name=User&background=334155&color=fff'">
          </td>
          <td style="font-family: monospace; font-weight: 600;">${escapeHtml(s.nis)}</td>
          <td style="font-weight: 600;">${escapeHtml(s.nama)}</td>
          <td>${escapeHtml(s.kelas)}</td>
          <td>${escapeHtml(s.tahun_ajaran)}</td>
          <td><span class="badge-tag">${escapeHtml(s.kd_prodi)}</span> <span style="font-size: 12px; color: var(--text-muted);">${escapeHtml(namaProdi)}</span></td>
          <td>${genderBadge}</td>
          <td style="text-align: right;">
            <div class="action-buttons" style="justify-content: flex-end;">
              <button class="btn btn-secondary btn-sm" onclick="openEditSiswa(${s.id})">Edit</button>
              <button class="btn btn-danger btn-sm" onclick="confirmDeleteSiswa(${s.id})">Hapus</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; color: var(--danger); padding: 20px;">Gagal memuat siswa: ${err.message}</td></tr>`;
  }
}

function populateProdiDropdowns() {
  const filter = document.getElementById('filter-prodi');
  const modalSelect = document.getElementById('siswa-prodi');
  if (!filter || !modalSelect) return;

  const currentFilterVal = filter.value;
  const currentModalVal = modalSelect.value;

  filter.innerHTML = '<option value="">Semua Program Studi</option>';
  modalSelect.innerHTML = '<option value="">-- Pilih Program Studi --</option>';

  prodiList.forEach(p => {
    filter.innerHTML += `<option value="${escapeHtml(p.kd_prodi)}">${escapeHtml(p.kd_prodi)} - ${escapeHtml(p.nama_prodi)}</option>`;
    modalSelect.innerHTML += `<option value="${escapeHtml(p.kd_prodi)}">${escapeHtml(p.kd_prodi)} - ${escapeHtml(p.nama_prodi)}</option>`;
  });

  filter.value = currentFilterVal;
  modalSelect.value = currentModalVal;
}

// ==========================================
// 6. MODALS & FORMS HANDLERS
// ==========================================
function openModal(id) {
  document.getElementById(id)?.classList.add('active');
}

function closeModal(id) {
  document.getElementById(id)?.classList.remove('active');
}

// PRODI MODAL
function openAddProdi() {
  document.getElementById('modal-prodi-title').textContent = 'Tambah Program Studi';
  document.getElementById('prodi-id').value = '';
  document.getElementById('prodi-kd').value = '';
  document.getElementById('prodi-nama').value = '';
  document.getElementById('btn-save-prodi').textContent = 'Simpan Data Prodi';
  openModal('modal-prodi');
}

window.openEditProdi = function(id) {
  const p = prodiList.find(item => item.id_prodi == id);
  if (!p) return;
  document.getElementById('modal-prodi-title').textContent = 'Edit Program Studi';
  document.getElementById('prodi-id').value = p.id_prodi;
  document.getElementById('prodi-kd').value = p.kd_prodi;
  document.getElementById('prodi-nama').value = p.nama_prodi;
  document.getElementById('btn-save-prodi').textContent = 'Update Data Prodi';
  openModal('modal-prodi');
};

window.confirmDeleteProdi = async function(id, kd_prodi) {
  if (!confirm(`Yakin ingin menghapus Program Studi "${kd_prodi}"?`)) return;
  try {
    await DB.deleteProdi(id, kd_prodi);
    showToast(`Program Studi ${kd_prodi} berhasil dihapus!`, 'success');
    renderProdiTable();
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
};

// SISWA MODAL
function openAddSiswa() {
  document.getElementById('modal-siswa-title').textContent = 'Tambah Data Siswa';
  document.getElementById('siswa-id').value = '';
  document.getElementById('siswa-nis').value = '';
  document.getElementById('siswa-nama').value = '';
  document.getElementById('siswa-kelas').value = '';
  document.getElementById('siswa-tahun').value = '2025/2026';
  document.getElementById('siswa-prodi').value = '';
  document.querySelector('input[name="siswa-gender"][value="L"]').checked = true;
  document.getElementById('siswa-foto-file').value = '';
  document.getElementById('siswa-foto-url').value = '';
  document.getElementById('foto-preview-container').style.display = 'none';
  document.getElementById('btn-save-siswa').textContent = 'Simpan Data Siswa';
  openModal('modal-siswa');
}

window.openEditSiswa = function(id) {
  const s = siswaList.find(item => item.id == id);
  if (!s) return;
  document.getElementById('modal-siswa-title').textContent = 'Edit Data Siswa';
  document.getElementById('siswa-id').value = s.id;
  document.getElementById('siswa-nis').value = s.nis;
  document.getElementById('siswa-nama').value = s.nama;
  document.getElementById('siswa-kelas').value = s.kelas;
  document.getElementById('siswa-tahun').value = s.tahun_ajaran;
  document.getElementById('siswa-prodi').value = s.kd_prodi;
  const genderRadio = document.querySelector(`input[name="siswa-gender"][value="${s.jenis_kelamin}"]`);
  if (genderRadio) genderRadio.checked = true;
  document.getElementById('siswa-foto-file').value = '';
  document.getElementById('siswa-foto-url').value = s.foto && s.foto.startsWith('http') ? s.foto : '';
  
  const preview = document.getElementById('foto-preview-img');
  if (s.foto) {
    preview.src = s.foto;
    document.getElementById('foto-preview-container').style.display = 'block';
  } else {
    document.getElementById('foto-preview-container').style.display = 'none';
  }
  document.getElementById('btn-save-siswa').textContent = 'Update Data Siswa';
  openModal('modal-siswa');
};

window.confirmDeleteSiswa = async function(id) {
  if (!confirm('Yakin ingin menghapus data siswa ini?')) return;
  try {
    await DB.deleteSiswa(id);
    showToast('Data siswa berhasil dihapus!', 'success');
    renderSiswaTable();
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
};

// ==========================================
// 7. FILE TO BASE64 HELPER
// ==========================================
function fileToBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => resolve(reader.result);
    reader.onerror = error => reject(error);
  });
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

// ==========================================
// 8. EVENT LISTENERS INITIALIZATION
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
  DB.init();

  // Check Login Session
  const isLoggedIn = sessionStorage.getItem('is_login') === 'true';
  if (isLoggedIn) {
    document.getElementById('login-screen').style.display = 'none';
    document.getElementById('app-layout').style.display = 'flex';
    document.getElementById('user-display').textContent = sessionStorage.getItem('user_name') || 'Admin';
    switchView('home');
  } else {
    document.getElementById('login-screen').style.display = 'flex';
    document.getElementById('app-layout').style.display = 'none';
  }

  // LOGIN FORM
  document.getElementById('login-form').addEventListener('submit', (e) => {
    e.preventDefault();
    const user = document.getElementById('login-username').value.trim();
    const pass = document.getElementById('login-password').value;

    if (user === 'admin' && pass === 'admin123') {
      sessionStorage.setItem('is_login', 'true');
      sessionStorage.setItem('user_name', user);
      document.getElementById('login-screen').style.display = 'none';
      document.getElementById('app-layout').style.display = 'flex';
      showToast('Berhasil login! Selamat datang.', 'success');
      switchView('home');
    } else {
      showToast('Username atau Password salah!', 'error');
    }
  });

  // LOGOUT
  document.getElementById('btn-logout').addEventListener('click', () => {
    sessionStorage.removeItem('is_login');
    sessionStorage.removeItem('user_name');
    document.getElementById('app-layout').style.display = 'none';
    document.getElementById('login-screen').style.display = 'flex';
    showToast('Berhasil logout dari sistem.', 'info');
  });

  // NAVIGATION CLICKS
  document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
    link.addEventListener('click', () => {
      const view = link.getAttribute('data-view');
      if (view) switchView(view);
    });
  });

  document.querySelectorAll('[data-view-target]').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = btn.getAttribute('data-view-target');
      if (target) switchView(target);
    });
  });

  // MOBILE SIDEBAR TOGGLE
  document.getElementById('menu-toggle')?.addEventListener('click', () => {
    document.getElementById('sidebar')?.classList.toggle('open');
  });

  // CLOSE MODALS
  document.querySelectorAll('[data-close-modal]').forEach(btn => {
    btn.addEventListener('click', () => {
      const modalId = btn.getAttribute('data-close-modal');
      closeModal(modalId);
    });
  });

  // BUTTON TRIGGERS
  document.getElementById('btn-quick-add-siswa')?.addEventListener('click', openAddSiswa);
  document.getElementById('btn-open-add-siswa')?.addEventListener('click', openAddSiswa);
  document.getElementById('btn-open-add-prodi')?.addEventListener('click', openAddProdi);

  document.getElementById('btn-refresh-siswa')?.addEventListener('click', renderSiswaTable);
  document.getElementById('btn-refresh-prodi')?.addEventListener('click', renderProdiTable);

  document.getElementById('search-siswa')?.addEventListener('input', renderSiswaTable);
  document.getElementById('filter-prodi')?.addEventListener('change', renderSiswaTable);
  document.getElementById('search-prodi')?.addEventListener('input', renderProdiTable);

  // FOTO PREVIEW HANDLER
  document.getElementById('siswa-foto-file')?.addEventListener('change', async (e) => {
    const file = e.target.files[0];
    if (file) {
      if (file.size > 2 * 1024 * 1024) {
        showToast('Ukuran foto maksimal 2MB!', 'error');
        e.target.value = '';
        return;
      }
      const base64 = await fileToBase64(file);
      const preview = document.getElementById('foto-preview-img');
      preview.src = base64;
      document.getElementById('foto-preview-container').style.display = 'block';
    }
  });

  // FORM SUBMIT: PRODI
  document.getElementById('form-prodi')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('prodi-id').value;
    const kd_prodi = document.getElementById('prodi-kd').value.trim().toUpperCase();
    const nama_prodi = document.getElementById('prodi-nama').value.trim();

    try {
      if (id) {
        await DB.updateProdi(id, { kd_prodi, nama_prodi });
        showToast('Data Program Studi berhasil diperbarui!', 'success');
      } else {
        await DB.insertProdi({ kd_prodi, nama_prodi });
        showToast('Program Studi baru berhasil disimpan!', 'success');
      }
      closeModal('modal-prodi');
      renderProdiTable();
      loadDashboard();
    } catch (err) {
      showToast(err.message, 'error');
    }
  });

  // FORM SUBMIT: SISWA
  document.getElementById('form-siswa')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('siswa-id').value;
    const nis = document.getElementById('siswa-nis').value.trim();
    const nama = document.getElementById('siswa-nama').value.trim();
    const kelas = document.getElementById('siswa-kelas').value.trim();
    const tahun_ajaran = document.getElementById('siswa-tahun').value.trim();
    const kd_prodi = document.getElementById('siswa-prodi').value;
    const jenis_kelamin = document.querySelector('input[name="siswa-gender"]:checked').value;
    
    let foto = document.getElementById('siswa-foto-url').value.trim();
    const fileInput = document.getElementById('siswa-foto-file');
    if (fileInput.files.length > 0) {
      foto = await fileToBase64(fileInput.files[0]);
    }

    try {
      const payload = { nis, nama, kelas, tahun_ajaran, kd_prodi, jenis_kelamin, foto };
      if (id) {
        await DB.updateSiswa(id, payload);
        showToast('Data siswa berhasil diperbarui!', 'success');
      } else {
        await DB.insertSiswa(payload);
        showToast('Data siswa berhasil ditambahkan!', 'success');
      }
      closeModal('modal-siswa');
      renderSiswaTable();
      loadDashboard();
    } catch (err) {
      showToast(err.message, 'error');
    }
  });

  // SUPABASE CONFIG FORM
  const creds = DB.getCredentials();
  if (document.getElementById('supabase-url')) document.getElementById('supabase-url').value = creds.url;
  if (document.getElementById('supabase-key')) document.getElementById('supabase-key').value = creds.key;

  document.getElementById('supabase-config-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const url = document.getElementById('supabase-url').value.trim();
    const key = document.getElementById('supabase-key').value.trim();

    localStorage.setItem('supabase_url', url);
    localStorage.setItem('supabase_key', key);
    DB.init();
    showToast('Konfigurasi Supabase berhasil disimpan!', 'success');
    loadDashboard();
  });

  document.getElementById('btn-reset-db')?.addEventListener('click', () => {
    localStorage.removeItem('supabase_url');
    localStorage.removeItem('supabase_key');
    document.getElementById('supabase-url').value = '';
    document.getElementById('supabase-key').value = '';
    DB.init();
    showToast('Berhasil beralih ke Local Browser Storage.', 'info');
    loadDashboard();
  });
});