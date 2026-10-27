// 1. IMPORT MODUL & KONSTANTA STORAGE
import taskProcessor, {
    TaskDataError,
    validateTask,
    validateTaskFormData,
    validateTaskTitle,
    validateTaskCourse,
    validateTaskDeadline,
    validateTaskPriority,
    validateTaskStatus,
    validateTaskUrl,
    validateTaskUrlLabel,
    validateTaskNotes,
    sanitizeInput,
    filterTasks,
    sortTasks,
    enrichTasks,
    getUniqueCourses,
    findTaskById,
    findTaskIndex,
    getTopUrgentTasks,
    calculateTaskStatistics,
    groupTasksByCourse,
    groupTasksByDate,
    parseTasksFromJSON
} from './taskProcessor.js';

const STORAGE_KEY_TASKS = 'tasktrack_agro_tasks';
const STORAGE_KEY_AUTH = 'tasktrack_agro_auth';
const STORAGE_KEY_NOTIF = 'tasktrack_agro_notif';
const STORAGE_KEY_THEME = 'tasktrack_theme';
const STORAGE_KEY_DENSITY = 'tasktrack_density';
const STORAGE_KEY_VIEW_MODE = 'tasktrack_view_mode';
const STORAGE_KEY_SOUND = 'tasktrack_sound_pref';

// 2. TEMPLATE SVG ICON
const ICONS = {
    calendar: '<svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>',
    clock: '<svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
    flag: '<svg class="icon-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>',
    check: '<svg class="icon-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>',
    link: '<svg class="icon-svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>',
    arrowRight: '<svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>',
    arrowLeft: '<svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
    rotate: '<svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>',
    detail: '<svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>',
    edit: '<svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>',
    delete: '<svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>',
    alertTriangle: '<svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>'
};

// 3. DATA DEFAULT
const defaultTasks = [
    {
        id: 'task-1',
        title: 'Tugas Semantik HTML & Aksesibilitas Web',
        course: 'Pemrograman Web Dasar',
        deadline: new Date(Date.now() + 3 * 24 * 3600 * 1000).toISOString().slice(0, 16),
        status: 'todo',
        priority: 'high',
        lms_url: 'https://elearning.kampus.ac.id',
        lms_label: 'LMS',
        instructions: 'Pastikan struktur heading bertingkat, form memiliki label terkait, dan kontras warna memenuhi standar WCAG AA.'
    },
    {
        id: 'task-2',
        title: 'Desain Entity Relationship Diagram (ERD)',
        course: 'Sistem Basis Data',
        deadline: new Date(Date.now() + 6 * 24 * 3600 * 1000).toISOString().slice(0, 16),
        status: 'todo',
        priority: 'low',
        lms_url: 'https://classroom.google.com',
        lms_label: 'Classroom',
        instructions: 'Rancang ERD sistem rekam medis klinik lengkap dengan kardinalitas 1-to-N dan relasi antar entitas.'
    },
    {
        id: 'task-3',
        title: 'Konfigurasi Routing OSPF & Subnetting',
        course: 'Jaringan Komputer Lanjut',
        deadline: new Date(Date.now() + 2 * 24 * 3600 * 1000).toISOString().slice(0, 16),
        status: 'inprogress',
        priority: 'medium',
        lms_url: 'https://elearning.kampus.ac.id',
        lms_label: 'LMS',
        instructions: 'Simulasikan di Cisco Packet Tracer dengan 3 router dan 4 subnet kelas C.'
    },
    {
        id: 'task-4',
        title: 'Resume Materi Algoritma Dijkstra',
        course: 'Struktur Data & Algoritma',
        deadline: new Date(Date.now() - 7 * 24 * 3600 * 1000).toISOString().slice(0, 16),
        status: 'done',
        priority: 'low',
        lms_url: 'https://elearning.kampus.ac.id',
        lms_label: 'LMS',
        instructions: 'Ringkas algoritma pencarian rute terpendek dengan matriks bobot berarah.'
    },
    {
        id: 'task-5',
        title: 'Upload Revisi Laporan Praktikum Modul 1',
        course: 'Pemrograman Berorientasi Objek',
        deadline: new Date(Date.now() + 4 * 3600 * 1000).toISOString().slice(0, 16),
        status: 'overdue',
        priority: 'high',
        lms_url: 'https://elearning.kampus.ac.id',
        lms_label: 'Kumpulkan Segera',
        instructions: 'Perbaiki diagram class dan lampirkan screenshot eksekusi unit test modul 1.'
    }
];

// 4. MANAJEMEN STATE & STORAGE
// Variabel ini fungsinya menampung seluruh array data tugas aktif di memori
let tasks = loadTasks();
// Objek ini fungsinya menyimpan state filter (matkul, query pencarian, dan opsi sorting)
let filterState = {
    course: 'all',
    search: '',
    sort: 'deadline-asc'
};

// Fungsi ini fungsinya mengambil dan memvalidasi data tugas dari localStorage atau mengembalikan data default
function loadTasks() {
    const stored = localStorage.getItem(STORAGE_KEY_TASKS);
    if (stored) {
        // Mengurai JSON tersimpan dengan fallback data default jika terjadi kerusakan struktur data
        return parseTasksFromJSON(stored, defaultTasks);
    }
    // Jika belum ada data tersimpan, set default tugas ke localStorage
    localStorage.setItem(STORAGE_KEY_TASKS, JSON.stringify(defaultTasks));
    return [...defaultTasks];
}

// Fungsi ini fungsinya menyimpan perubahan array tugas ke localStorage lalu memperbarui seluruh tampilan UI
function saveTasks() {
    localStorage.setItem(STORAGE_KEY_TASKS, JSON.stringify(tasks));
    updateFilterOptions(); // Perbarui opsi dropdown filter matkul
    renderBoard();         // Render ulang kartu Kanban
    renderListView();      // Render ulang tabel List View
    updateDashboard();     // Perbarui angka metrik di dashboard
    renderCalendar();      // Render ulang titik tugas di kalender
}

// 5. HELPER DOM & AKSESIBILITAS
// Fungsi ini fungsinya memperbarui live region announcer agar status dibacakan oleh pembaca layar (screen reader)
function announce(message) {
    const announcer = document.getElementById('a11y-announcer');
    if (announcer) {
        announcer.textContent = '';
        // Beri jeda 50ms agar browser mendeteksi perubahan teks aria-live
        setTimeout(() => { announcer.textContent = message; }, 50);
    }
}

// Fungsi ini fungsinya mengamankan teks dari karakter khusus HTML untuk mencegah serangan XSS
function escapeHtml(val) {
    if (val === null || val === undefined) return '';
    return String(val)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
const safeStr = escapeHtml;

// Fungsi ini fungsinya memformat teks tanggal tenggat waktu dan menghitung selisih sisa hari/jam
function formatDeadline(deadlineStr, status) {
    if (!deadlineStr) return { dateStr: 'Belum ditentukan', timeStr: 'Segera', isOverdue: false };

    const deadline = new Date(deadlineStr);
    const now = new Date();
    const diffMs = deadline - now;
    const diffHours = diffMs / (1000 * 60 * 60);

    const options = { month: 'short', day: 'numeric', year: 'numeric' };
    const dateStr = deadline.toLocaleDateString('en-US', options);

    // Jika tugas sudah selesai, sisa waktu berstatus 'Selesai'
    if (status === 'done') {
        return { dateStr, timeStr: 'Selesai', isOverdue: false };
    }

    // Jika waktu tenggat telah lampau
    if (diffMs <= 0) {
        return { dateStr, timeStr: 'Tenggat Lewat', isOverdue: true };
    } else if (diffHours < 24) {
        // Jika sisa waktu kurang dari 24 jam
        const h = Math.max(1, Math.floor(diffHours));
        return { dateStr, timeStr: `Sisa ${h} Jam`, isOverdue: true };
    } else {
        // Jika sisa waktu lebih dari 1 hari
        const d = Math.round(diffHours / 24);
        return { dateStr, timeStr: `${d} days`, isOverdue: false };
    }
}

// 6. INTERAKSI 3: PREFERENSI TEMA & LOCALSTORAGE (2 TEMA: DARK MODE & LIGHT MODE)
// Array daftar tema tampilan yang diizinkan dalam sistem
const AVAILABLE_THEMES = ['dark', 'light'];

// Fungsi ini fungsinya menginisialisasi preferensi pengguna (tema, kerapatan, mode view, audio) dari localStorage saat aplikasi pertama kali dimuat
function initPreferences() {
    let savedTheme = localStorage.getItem(STORAGE_KEY_THEME) || 'dark';
    if (!AVAILABLE_THEMES.includes(savedTheme)) {
        savedTheme = savedTheme === 'light' ? 'light' : 'dark';
    }
    const savedDensity = localStorage.getItem(STORAGE_KEY_DENSITY) || 'comfortable';
    const savedViewMode = localStorage.getItem(STORAGE_KEY_VIEW_MODE) || 'kanban';
    const savedSound = localStorage.getItem(STORAGE_KEY_SOUND) !== 'false';

    applyTheme(savedTheme);
    applyDensity(savedDensity);

    // Sinkronisasi nilai kontrol dropdown dan input form pengaturan
    const themeSelect = document.getElementById('select-theme');
    if (themeSelect) themeSelect.value = savedTheme;

    const densitySelect = document.getElementById('select-density');
    if (densitySelect) densitySelect.value = savedDensity;

    const soundCheck = document.getElementById('check-enable-sound');
    if (soundCheck) soundCheck.checked = savedSound;

    // Jika mode tampilan terakhir adalah List View, aktifkan langsung
    if (savedViewMode === 'list') {
        activateListView();
    }
}

// Fungsi ini fungsinya menerapkan tema (dark/light) ke atribut data-theme di elemen <html> dan menyimpannya ke localStorage
function applyTheme(themeName) {
    if (!AVAILABLE_THEMES.includes(themeName)) {
        themeName = themeName === 'light' ? 'light' : 'dark';
    }
    document.documentElement.setAttribute('data-theme', themeName);
    localStorage.setItem(STORAGE_KEY_THEME, themeName);

    // Update nilai dropdown select jika berbeda
    const themeSelect = document.getElementById('select-theme');
    if (themeSelect && themeSelect.value !== themeName) {
        themeSelect.value = themeName;
    }

    // Perbarui tooltip dan label aksesibilitas tombol toggle tema di header
    const themeToggleBtn = document.getElementById('btn-theme-toggle');
    if (themeToggleBtn) {
        const nextName = themeName === 'dark' ? 'Light Mode' : 'Dark Mode';
        themeToggleBtn.setAttribute('title', `Ganti ke tema ${nextName}`);
        themeToggleBtn.setAttribute('aria-label', `Ganti ke tema ${nextName}`);
    }
}

// Fungsi ini fungsinya beralih (toggle) antara tema Dark Mode dan Light Mode saat tombol tema diklik
function cycleTheme() {
    const current = localStorage.getItem(STORAGE_KEY_THEME) || 'dark';
    const nextTheme = current === 'dark' ? 'light' : 'dark';
    applyTheme(nextTheme);

    const themeSelect = document.getElementById('select-theme');
    if (themeSelect) themeSelect.value = nextTheme;

    const themeLabel = nextTheme === 'dark' ? 'Dark Mode' : 'Light Mode';
    announce(`Tema diubah menjadi ${themeLabel}`);
}

// Fungsi ini fungsinya mengatur kerapatan jarak kartu (comfortable atau compact) pada antarmuka Kanban
function applyDensity(densityName) {
    const valid = densityName === 'compact' ? 'compact' : 'comfortable';
    document.documentElement.setAttribute('data-density', valid);
    localStorage.setItem(STORAGE_KEY_DENSITY, valid);
}

// 7. INTERAKSI 1: FILTER, PENCARIAN & SORTING
// Fungsi ini fungsinya memproses pemfilteran dan pengurutan array tugas sesuai filterState yang aktif
function getProcessedTasks() {
    // Menyaring tugas berdasarkan mata kuliah dan teks pencarian
    const filtered = filterTasks(tasks, {
        course: filterState.course,
        search: filterState.search
    });

    // Mengurai field pengurutan dan arahnya (contoh: 'deadline-asc' -> sortBy: 'deadline', order: 'asc')
    const [sortField, sortOrder] = filterState.sort.split('-');
    return sortTasks(filtered, sortField, sortOrder);
}

// Fungsi ini fungsinya memasang event listener pada input pencarian, dropdown filter matkul, dan pengurutan
function initFilterAndSortControls() {
    const searchInput = document.getElementById('search-task-input');
    const filterSelect = document.getElementById('filter-course-select');
    const sortSelect = document.getElementById('sort-task-select');
    const filterBtn = document.getElementById('btn-filter-toggle');
    const filterPopover = document.getElementById('filter-popover');
    const filterCurrentLabel = document.getElementById('filter-current-label');

    // Event input pencarian kata kunci secara real-time
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            filterState.search = e.target.value.toLowerCase().trim();
            renderBoard();
            renderListView();
        });
    }

    // Event buka/tutup popover dropdown filter kategori
    if (filterBtn && filterPopover) {
        filterBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = filterPopover.classList.toggle('show');
            filterBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        // Menutup popover saat mengklik di luar area dropdown
        document.addEventListener('click', (e) => {
            if (!filterPopover.contains(e.target) && e.target !== filterBtn) {
                filterPopover.classList.remove('show');
                filterBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Event pemilihan kategori mata kuliah
    if (filterSelect) {
        filterSelect.addEventListener('change', () => {
            filterState.course = filterSelect.value;
            if (filterCurrentLabel) {
                filterCurrentLabel.textContent = filterSelect.value === 'all' ? 'Filter' : filterSelect.value;
            }
            renderBoard();
            renderListView();
            if (filterPopover) filterPopover.classList.remove('show');
            announce(`Filter diterapkan: ${filterSelect.value}`);
        });
    }

    // Event pemilihan kriteria pengurutan tugas (sorting)
    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            filterState.sort = sortSelect.value;
            renderBoard();
            renderListView();
            announce(`Urutan tugas diubah berdasarkan ${sortSelect.options[sortSelect.selectedIndex].text}`);
        });
    }
}

// Fungsi ini fungsinya memperbarui opsi daftar mata kuliah pada elemen <select> filter dan <datalist> form
function updateFilterOptions() {
    const filterSelect = document.getElementById('filter-course-select');
    if (!filterSelect) return;

    const current = filterSelect.value || 'all';
    const courses = getUniqueCourses(tasks);

    // Reset opsi filter dan tambahkan 'Semua Kategori'
    filterSelect.innerHTML = '<option value="all">Semua Kategori</option>';
    courses.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c;
        opt.textContent = c;
        if (c === current) opt.selected = true;
        filterSelect.appendChild(opt);
    });

    // Perbarui rekomendasi datalist formulir tambah tugas
    const datalist = document.getElementById('course-suggestions');
    if (datalist) {
        datalist.innerHTML = courses.map(c => `<option value="${escapeHtml(c)}">`).join('');
    }
}

// 8. INTERAKSI 2: DETAIL TUGAS, STATUS, NOTIFIKASI & RINGKASAN
const detailModal = document.getElementById('task-detail-modal');
const closeDetailModalBtn = document.getElementById('btn-close-detail-modal');
const closeDetailSecondaryBtn = document.getElementById('btn-close-detail-secondary');
const copyDetailNotesBtn = document.getElementById('btn-copy-detail-notes');
const editFromDetailBtn = document.getElementById('btn-edit-from-detail');
let activeDetailTaskId = null; // Menyimpan ID tugas yang sedang aktif dibuka pada modal detail

// Fungsi ini fungsinya membuka modal rincian tugas dan mengisi elemen pop-up dengan metadata tugas yang dipilih
function openDetailModal(taskId) {
    const task = findTaskById(tasks, taskId);
    if (!task || !detailModal) return;

    activeDetailTaskId = taskId;
    // Format informasi deadline dan status sisa waktu
    const timeInfo = formatDeadline(task.deadline, task.status);

    const titleEl = document.getElementById('detail-task-title');
    const courseEl = document.getElementById('detail-course-name');
    const dateEl = document.getElementById('detail-deadline-date');
    const countEl = document.getElementById('detail-countdown');
    const prioEl = document.getElementById('detail-priority-badge');
    const statusEl = document.getElementById('detail-status-badge');
    const lmsEl = document.getElementById('detail-lms-slot');
    const notesEl = document.getElementById('detail-instructions-box');
    const actionsContainer = document.getElementById('detail-status-actions-container');

    // Mengisi teks judul, matkul, dan tanggal tenggat
    if (titleEl) titleEl.textContent = task.title;
    if (courseEl) courseEl.textContent = task.course;
    if (dateEl) dateEl.textContent = timeInfo.dateStr;
    if (countEl) {
        countEl.textContent = timeInfo.timeStr;
        countEl.style.color = timeInfo.isOverdue && task.status !== 'done' ? '#f87171' : 'var(--accent-yellow)';
    }

    // Menampilkan badge prioritas dengan ikon SVG
    if (prioEl) {
        const p = (task.priority || 'medium').toLowerCase();
        prioEl.innerHTML = `<span class="priority-badge priority-${p}">${ICONS.flag} <span>${p.toUpperCase()}</span></span>`;
    }

    // Menampilkan badge status kolom
    if (statusEl) {
        const stLabels = { todo: 'Planned', inprogress: 'In progress', done: 'Completed', overdue: 'Overdue / < 24h' };
        statusEl.innerHTML = `<span class="col-count" style="font-size: 0.8rem; padding: 0.25rem 0.65rem;">${stLabels[task.status] || task.status}</span>`;
    }

    // Menampilkan tautan LMS jika tersedia
    if (lmsEl) {
        if (task.lms_url) {
            lmsEl.innerHTML = `<a href="${escapeHtml(task.lms_url)}" target="_blank" rel="noopener noreferrer" class="btn-lms-link">${ICONS.link} <span>${escapeHtml(task.lms_label || 'Buka Link')}</span></a>`;
        } else {
            lmsEl.innerHTML = '<span style="color: var(--text-muted); font-size: 0.8rem;">Tidak ada tautan</span>';
        }
    }

    // Menampilkan teks catatan instruksi tugas
    if (notesEl) {
        notesEl.textContent = task.instructions || 'Tidak ada catatan atau instruksi tambahan pada tugas ini.';
    }

    // Merender tombol-tombol pilihan status cepat di dalam modal detail
    if (actionsContainer) {
        const statuses = [
            { id: 'todo', label: 'Planned' },
            { id: 'inprogress', label: 'In progress' },
            { id: 'done', label: 'Completed' },
            { id: 'overdue', label: 'Overdue / < 24h' }
        ];

        actionsContainer.innerHTML = statuses.map(s => `
            <button type="button" class="btn-status-pill ${task.status === s.id ? 'active' : ''}" data-status="${s.id}">
                ${task.status === s.id ? ICONS.check : ''}
                <span>${s.label}</span>
            </button>
        `).join('');

        // Pasang listener klik untuk pembaruan status langsung
        actionsContainer.querySelectorAll('.btn-status-pill').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetStatus = btn.getAttribute('data-status');
                updateTaskStatus(taskId, targetStatus);
                openDetailModal(taskId); // Render ulang modal agar tombol aktif berpindah
            });
        });
    }

    // Tampilkan modal HTML5 <dialog>
    if (typeof detailModal.showModal === 'function') {
        detailModal.showModal();
    } else {
        detailModal.setAttribute('open', '');
    }

    announce(`Membuka rincian tugas "${task.title}"`);
}

// Fungsi ini fungsinya menutup modal detail rincian tugas
function closeDetailModal() {
    if (!detailModal) return;
    if (typeof detailModal.close === 'function') detailModal.close();
    else detailModal.removeAttribute('open');
    activeDetailTaskId = null;
}

// Fungsi ini fungsinya memperbarui status kolom tugas (todo, inprogress, done, overdue) dan menyimpan perubahan
function updateTaskStatus(taskId, newStatus) {
    const task = findTaskById(tasks, taskId);
    if (!task) return;
    task.status = newStatus;
    saveTasks(); // Simpan dan perbarui tampilan kanban
    announce(`Status tugas "${task.title}" diperbarui ke ${newStatus}`);
}

// Modal Ringkasan Cepat
const summaryModal = document.getElementById('summary-modal');
const quickSummaryBtn = document.getElementById('btn-quick-summary');
const closeSummaryBtn = document.getElementById('btn-close-summary-modal');
const closeSummaryBottomBtn = document.getElementById('btn-close-summary-bottom');

// Fungsi ini fungsinya membuka modal ringkasan dan merender analitik penyelesaian tugas per mata kuliah
function openSummaryModal() {
    if (!summaryModal) return;

    // Kalkulasi metrik keseluruhan dan pengelompokan per mata kuliah
    const stats = calculateTaskStatistics(tasks);
    const courseGroups = groupTasksByCourse(tasks);

    const sumTotal = document.getElementById('sum-total-tasks');
    const sumDone = document.getElementById('sum-completed-tasks');
    const sumRate = document.getElementById('sum-rate-tasks');
    const breakdownContainer = document.getElementById('summary-course-breakdown');

    // Menampilkan angka total, selesai, dan persentase tuntas
    if (sumTotal) sumTotal.textContent = stats.total;
    if (sumDone) sumDone.textContent = stats.completedCount;
    if (sumRate) sumRate.textContent = `${stats.completionRate}%`;

    // Merender bilah progres persentase selesai untuk tiap mata kuliah
    if (breakdownContainer) {
        if (courseGroups.length === 0) {
            breakdownContainer.innerHTML = '<p style="color: var(--text-muted); font-size: 0.8rem;">Belum ada data tugas terdaftar.</p>';
        } else {
            breakdownContainer.innerHTML = courseGroups.map(cg => `
                <div class="course-progress-row">
                    <div class="course-progress-header">
                        <span style="color: var(--text-main);">${escapeHtml(cg.course)}</span>
                        <span style="color: var(--text-muted); font-size: 0.75rem;">${cg.done}/${cg.total} Selesai (${cg.completionRate}%)</span>
                    </div>
                    <div class="course-progress-bar-bg">
                        <div class="course-progress-bar-fill" style="width: ${cg.completionRate}%;"></div>
                    </div>
                </div>
            `).join('');
        }
    }

    if (typeof summaryModal.showModal === 'function') summaryModal.showModal();
    else summaryModal.setAttribute('open', '');

    announce('Modal ringkasan dan analitik tugas dibuka');
}

// Fungsi ini fungsinya menutup modal ringkasan analitik tugas
function closeSummaryModal() {
    if (!summaryModal) return;
    if (typeof summaryModal.close === 'function') summaryModal.close();
    else summaryModal.removeAttribute('open');
}

// 9. RENDER KANBAN BOARD
// Objek penampung elemen dropzone untuk masing-masing 4 kolom status Kanban
const dropzones = {
    todo: document.getElementById('dropzone-todo'),
    inprogress: document.getElementById('dropzone-inprogress'),
    done: document.getElementById('dropzone-done'),
    overdue: document.getElementById('dropzone-overdue')
};

// Objek penampung elemen teks penghitung jumlah kartu di tiap header kolom
const counters = {
    todo: document.getElementById('count-todo'),
    inprogress: document.getElementById('count-inprogress'),
    done: document.getElementById('count-done'),
    overdue: document.getElementById('count-overdue')
};

// Fungsi ini fungsinya merender seluruh kartu tugas ke dalam 4 kolom Kanban sesuai status masing-masing
function renderBoard() {
    // Mengosongkan seluruh kolom dropzone sebelum dirender ulang
    Object.values(dropzones).forEach(z => { if (z) z.innerHTML = ''; });

    const sortedTasks = getProcessedTasks();
    const counts = { todo: 0, inprogress: 0, done: 0, overdue: 0 };

    // Loop untuk membuat elemen kartu semantik <article> untuk setiap tugas
    sortedTasks.forEach(task => {
        const targetStatus = task.status || 'todo';
        counts[targetStatus] = (counts[targetStatus] || 0) + 1;

        const timeInfo = formatDeadline(task.deadline, task.status);

        // Membuat elemen kontainer kartu tugas
        const card = document.createElement('article');
        card.className = 'task-card';
        card.id = task.id;
        card.draggable = true;
        card.setAttribute('role', 'listitem');
        card.setAttribute('aria-labelledby', `title-${task.id}`);
        card.setAttribute('tabindex', '0');

        // Beri garis aksen oranye jika tugas telah lewat tenggat
        if (timeInfo.isOverdue && task.status !== 'done') {
            card.style.borderLeft = '3px solid #f97316';
        }

        // Tentukan kelas CSS dan label badge prioritas
        let priorityBadgeClass = 'priority-med';
        let priorityBadgeLabel = 'Medium';
        let priorityIconSvg = ICONS.flag;
        const priorityVal = (task.priority || 'medium').toLowerCase();

        if (task.status === 'done') {
            priorityBadgeClass = 'priority-done';
            priorityBadgeLabel = 'Done';
            priorityIconSvg = ICONS.check;
        } else if (priorityVal === 'high' || timeInfo.isOverdue) {
            priorityBadgeClass = 'priority-high';
            priorityBadgeLabel = 'High';
        } else if (priorityVal === 'low') {
            priorityBadgeClass = 'priority-low';
            priorityBadgeLabel = 'Low';
        }

        // Tautan tombol eksternal pengumpulan LMS
        let lmsButtonHtml = '';
        if (task.lms_url) {
            const label = escapeHtml((task.lms_label || 'LMS').replace(/[^\x20-\x7E]/g, '').trim());
            const isUrgentLink = timeInfo.isOverdue && task.status !== 'done';
            lmsButtonHtml = `
                <a href="${escapeHtml(task.lms_url)}" target="_blank" rel="noopener noreferrer" 
                   class="btn-lms-link ${isUrgentLink ? 'btn-lms-urgent' : ''}" 
                   aria-label="Buka pengumpulan untuk ${escapeHtml(task.title)}">
                    ${ICONS.link}
                    <span>${label}</span>
                </a>
            `;
        }

        // Tombol navigasi pemindahan status antar kolom
        let moveButtons = '';
        if (task.status === 'todo') {
            moveButtons = `<button type="button" class="btn-action btn-move" data-id="${task.id}" data-next="inprogress" title="Pindah ke In progress" aria-label="Pindah ke In progress">${ICONS.arrowRight}</button>`;
        } else if (task.status === 'inprogress') {
            moveButtons = `
                <button type="button" class="btn-action btn-move" data-id="${task.id}" data-next="todo" title="Kembalikan ke Planned" aria-label="Kembalikan ke Planned">${ICONS.arrowLeft}</button>
                <button type="button" class="btn-action btn-move" data-id="${task.id}" data-next="done" title="Pindah ke Completed" aria-label="Pindah ke Completed">${ICONS.check}</button>
            `;
        } else if (task.status === 'overdue') {
            moveButtons = `
                <button type="button" class="btn-action btn-move" data-id="${task.id}" data-next="inprogress" title="Kerjakan Sekarang" aria-label="Pindah ke In progress">${ICONS.arrowRight}</button>
                <button type="button" class="btn-action btn-move" data-id="${task.id}" data-next="done" title="Tandai Selesai" aria-label="Tandai Selesai">${ICONS.check}</button>
            `;
        } else if (task.status === 'done') {
            moveButtons = `<button type="button" class="btn-action btn-move" data-id="${task.id}" data-next="inprogress" title="Buka Kembali" aria-label="Buka kembali">${ICONS.rotate}</button>`;
        }

        // Menyusun markup HTML bagian dalam kartu tugas
        card.innerHTML = `
            <h3 class="task-card-title" id="title-${task.id}" style="cursor: pointer;" title="Klik untuk rincian tugas">${escapeHtml(task.title)}</h3>
            <div class="task-card-meta">
                <span class="meta-item">${ICONS.calendar} <span>${timeInfo.dateStr}</span></span>
                <span class="meta-item ${timeInfo.isOverdue && task.status !== 'done' ? 'meta-urgent' : ''}">${ICONS.clock} <span>${timeInfo.timeStr}</span></span>
            </div>
            <p class="task-course-subtitle">${escapeHtml(task.course)}</p>
            ${task.instructions ? `<p class="task-instructions-preview" title="${escapeHtml(task.instructions)}">${escapeHtml(task.instructions)}</p>` : ''}
            <div class="task-card-footer">
                <div style="display: flex; align-items: center; gap: 0.55rem;">
                    <span class="priority-badge ${priorityBadgeClass}">${priorityIconSvg} <span>${priorityBadgeLabel}</span></span>
                    ${lmsButtonHtml}
                </div>
                <div class="task-actions">
                    ${moveButtons}
                    <button type="button" class="btn-action btn-detail" data-id="${task.id}" title="Lihat Rincian Tugas" aria-label="Lihat rincian tugas">${ICONS.detail}</button>
                    <button type="button" class="btn-action btn-edit" data-id="${task.id}" title="Edit Tugas" aria-label="Edit tugas">${ICONS.edit}</button>
                    <button type="button" class="btn-action btn-delete" data-id="${task.id}" title="Hapus Tugas" aria-label="Hapus tugas">${ICONS.delete}</button>
                </div>
            </div>
        `;

        // Klik judul kartu untuk membuka modal detail
        const titleHeader = card.querySelector('.task-card-title');
        if (titleHeader) {
            titleHeader.addEventListener('click', () => openDetailModal(task.id));
        }

        // Pasang event handler drag & drop pada elemen kartu
        setupDragAndDrop(card, task.id);

        // Masukkan kartu ke dropzone kolom yang bersangkutan
        if (dropzones[targetStatus]) {
            dropzones[targetStatus].appendChild(card);
        }
    });

    // Memperbarui angka badge jumlah tugas pada header tiap kolom
    if (counters.todo) counters.todo.textContent = counts.todo;
    if (counters.inprogress) counters.inprogress.textContent = counts.inprogress;
    if (counters.done) counters.done.textContent = counts.done;
    if (counters.overdue) counters.overdue.textContent = counts.overdue;

    // Jika ada kolom yang kosong, tampilkan pesan pemberitahuan kolom kosong
    Object.entries(dropzones).forEach(([key, zone]) => {
        if (zone && zone.children.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'empty-column-notice';
            empty.textContent = 'Belum ada tugas di kolom ini.';
            zone.appendChild(empty);
        }
    });

    // Pasang ulang listener pada tombol-tombol aksi kartu
    attachCardActionListeners();
}

// 10. RENDER LIST VIEW
// Fungsi ini fungsinya merender tampilan daftar tugas alternatif dalam format baris tabel
function renderListView() {
    const rowsContainer = document.getElementById('list-view-rows');
    if (!rowsContainer) return;

    const sortedTasks = getProcessedTasks();

    if (sortedTasks.length === 0) {
        rowsContainer.innerHTML = '<div class="empty-column-notice">Tidak ada tugas yang sesuai dengan pencarian atau filter.</div>';
        return;
    }

    // Merender setiap tugas menjadi satu baris tabel responsif
    rowsContainer.innerHTML = sortedTasks.map(t => {
        const timeInfo = formatDeadline(t.deadline, t.status);
        const lmsPill = t.lms_url ? 
            `<a href="${escapeHtml(t.lms_url)}" target="_blank" class="btn-lms-link">${ICONS.link} <span>${escapeHtml(t.lms_label || 'LMS')}</span></a>` : 
            `<span style="color: var(--text-muted); font-size: 0.75rem;">-</span>`;

        return `
            <div class="task-list-row">
                <div>
                    <div class="list-title" style="cursor: pointer;" onclick="window.openDetailDirect('${t.id}')">${escapeHtml(t.title)}</div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">${escapeHtml(t.status)}</span>
                </div>
                <div style="font-size: 0.85rem; color: #a3b8aa;">${escapeHtml(t.course)}</div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">${timeInfo.dateStr} (${timeInfo.timeStr})</div>
                <div><span class="priority-badge priority-${t.priority || 'med'}">${ICONS.flag} <span>${escapeHtml(t.priority || 'Medium')}</span></span></div>
                <div>${lmsPill}</div>
                <div style="text-align: right; display: flex; justify-content: flex-end; gap: 0.4rem;">
                    <button type="button" class="btn-action btn-detail" data-id="${t.id}" title="Lihat Rincian">${ICONS.detail}</button>
                    <button type="button" class="btn-action btn-edit" data-id="${t.id}" title="Edit">${ICONS.edit}</button>
                    <button type="button" class="btn-action btn-delete" data-id="${t.id}" title="Hapus">${ICONS.delete}</button>
                </div>
            </div>
        `;
    }).join('');

    // Memasang listener tombol pada tiap baris list view
    rowsContainer.querySelectorAll('.btn-detail').forEach(btn => {
        btn.addEventListener('click', () => openDetailModal(btn.getAttribute('data-id')));
    });
    rowsContainer.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => editTask(btn.getAttribute('data-id')));
    });
    rowsContainer.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', () => deleteTask(btn.getAttribute('data-id')));
    });
}

// Fungsi pembantu global untuk membuka detail tugas saat judul di list view diklik
window.openDetailDirect = function(id) {
    openDetailModal(id);
};

// 11. DRAG AND DROP NATIVE HTML5
let draggedTaskId = null; // Menyimpan ID kartu yang sedang di-drag

// Fungsi ini fungsinya mengatur event listener dragstart dan dragend pada elemen kartu tugas
function setupDragAndDrop(cardElement, taskId) {
    cardElement.addEventListener('dragstart', (e) => {
        draggedTaskId = taskId;
        cardElement.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', taskId);
    });

    cardElement.addEventListener('dragend', () => {
        cardElement.classList.remove('dragging');
        draggedTaskId = null;
        document.querySelectorAll('.column-dropzone').forEach(z => z.classList.remove('dragover'));
    });
}

// Fungsi ini fungsinya menginisialisasi area dropzone tiap kolom agar bisa menerima pelepasan kartu (drop)
function initDropzones() {
    Object.entries(dropzones).forEach(([statusKey, zone]) => {
        if (!zone) return;

        // Efek saat kartu digeser di atas dropzone
        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            zone.classList.add('dragover');
        });

        // Hapus kelas highlight saat kartu keluar dari area dropzone
        zone.addEventListener('dragleave', (e) => {
            if (!zone.contains(e.relatedTarget)) {
                zone.classList.remove('dragover');
            }
        });

        // Tangani kartu yang dilepaskan ke dalam dropzone
        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('dragover');
            const id = e.dataTransfer.getData('text/plain') || draggedTaskId;
            if (!id) return;

            const taskIndex = findTaskIndex(tasks, id);
            if (taskIndex > -1 && tasks[taskIndex].status !== statusKey) {
                tasks[taskIndex].status = statusKey; // Ubah status tugas ke kolom baru
                saveTasks();
                announce(`Tugas "${tasks[taskIndex].title}" dipindahkan ke kolom ${statusKey}`);
            }
        });
    });
}

// Fungsi ini fungsinya memasang event listener pada semua tombol aksi kartu (pindah status, detail, edit, hapus)
function attachCardActionListeners() {
    // Tombol geser status ke kolom berikutnya/sebelumnya
    document.querySelectorAll('.btn-move').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const nextStatus = btn.getAttribute('data-next');
            const task = findTaskById(tasks, id);
            if (task && nextStatus) {
                task.status = nextStatus;
                saveTasks();
                announce(`Tugas "${task.title}" dipindahkan ke ${nextStatus}`);
            }
        });
    });

    // Tombol detail rincian tugas
    document.querySelectorAll('.btn-detail').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            openDetailModal(id);
        });
    });

    // Tombol edit data tugas
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            editTask(id);
        });
    });

    // Tombol hapus kartu tugas
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            deleteTask(id);
        });
    });
}

// ==========================================================================
// 12. FORM CRUD TUGAS & VALIDASI CLIENT-SIDE
// ==========================================================================

// --- 12a. HELPERS VALIDASI UI ---

// Objek ini fungsinya memetakan properti data tugas ke ID elemen input dan ID pesan error di DOM
const FIELD_MAP = {
    title:        { inputId: 'input-judul',     errorId: 'error-judul' },
    course:       { inputId: 'input-matkul',    errorId: 'error-matkul' },
    deadline:     { inputId: 'input-deadline',  errorId: 'error-deadline' },
    priority:     { inputId: 'input-priority',  errorId: 'error-priority' },
    status:       { inputId: 'input-status',    errorId: 'error-status' },
    lms_url:      { inputId: 'input-url',       errorId: 'error-url' },
    lms_label:    { inputId: 'input-url-label', errorId: 'error-url-label' },
    instructions: { inputId: 'input-notes',     errorId: 'error-notes' }
};

// Fungsi ini fungsinya menampilkan pesan error validasi pada field tertentu dan menandai input tidak valid
function showFieldError(fieldKey, message) {
    const map = FIELD_MAP[fieldKey];
    if (!map) return;
    const input = document.getElementById(map.inputId);
    const errorEl = document.getElementById(map.errorId);
    // Baris ini menandai atribut aria-invalid agar terdeteksi oleh screen reader
    if (input) input.setAttribute('aria-invalid', 'true');
    // Baris ini merender ikon peringatan dan teks pesan error ke dalam elemen pesan
    if (errorEl) {
        errorEl.innerHTML = `${ICONS.alertTriangle} <span>${escapeHtml(message)}</span>`;
        errorEl.classList.add('show');
    }
}

// Fungsi ini fungsinya menghapus status error dari field tertentu saat input sudah valid
function clearFieldError(fieldKey) {
    const map = FIELD_MAP[fieldKey];
    if (!map) return;
    const input = document.getElementById(map.inputId);
    const errorEl = document.getElementById(map.errorId);
    // Baris ini mengembalikan status aria-invalid ke false
    if (input) input.setAttribute('aria-invalid', 'false');
    // Baris ini mengosongkan konten teks error dan menyembunyikan tampilannya
    if (errorEl) {
        errorEl.innerHTML = '';
        errorEl.classList.remove('show');
    }
}

// Fungsi ini fungsinya menghapus seluruh pesan error dari semua field form input
function clearAllFieldErrors() {
    Object.keys(FIELD_MAP).forEach(key => clearFieldError(key));
}

// Fungsi ini fungsinya menampilkan kotak ringkasan seluruh daftar error di bagian atas modal formulir
function showErrorSummary(errors) {
    const summaryEl = document.getElementById('form-error-summary');
    const listEl = document.getElementById('error-summary-list');
    if (!summaryEl || !listEl) return;

    listEl.innerHTML = '';
    // Baris ini mengiterasi setiap pesan error untuk dibuatkan tautan lompat ke input yang bersangkutan
    Object.entries(errors).forEach(([key, message]) => {
        const map = FIELD_MAP[key];
        const li = document.createElement('li');
        if (map) {
            const a = document.createElement('a');
            a.href = `#${map.inputId}`;
            a.textContent = message;
            // Baris ini memfokuskan keyboard ke input saat tautan daftar error diklik
            a.addEventListener('click', (e) => {
                e.preventDefault();
                const input = document.getElementById(map.inputId);
                if (input) input.focus();
            });
            li.appendChild(a);
        } else {
            li.textContent = message;
        }
        listEl.appendChild(li);
    });

    // Baris ini menampilkan kotak ringkasan error dan memfokuskan keyboard ke area tersebut
    summaryEl.removeAttribute('hidden');
    summaryEl.focus();
}

// Fungsi ini fungsinya menyembunyikan kotak ringkasan error formulir
function hideErrorSummary() {
    const summaryEl = document.getElementById('form-error-summary');
    if (summaryEl) summaryEl.setAttribute('hidden', '');
}

// Fungsi ini fungsinya mengumpulkan seluruh nilai yang diisi pengguna dari form ke objek tugas
function collectFormData(isNew) {
    const editId = document.getElementById('task-id').value;
    return {
        // Baris ini menggunakan ID yang ada saat edit atau membuat ID unik berbasis timestamp untuk tugas baru
        id: editId || ('task-' + Date.now()),
        title: document.getElementById('input-judul').value,
        course: document.getElementById('input-matkul').value,
        deadline: document.getElementById('input-deadline').value,
        priority: document.getElementById('input-priority').value,
        status: document.getElementById('input-status').value,
        lms_url: document.getElementById('input-url').value,
        lms_label: document.getElementById('input-url-label').value,
        instructions: document.getElementById('input-notes').value
    };
}

// Fungsi ini fungsinya memperbarui indikator hitungan karakter pada textarea catatan instruksi
function updateNotesCounter(value) {
    const counter = document.getElementById('notes-char-counter');
    if (!counter) return;
    const len = (value || '').length;
    // Baris ini menampilkan rasio jumlah karakter terhadap batas maksimal 500
    counter.textContent = `${len} / 500`;
    counter.className = 'char-counter';
    // Baris ini menambahkan warna peringatan jika mendekati atau mencapai batas kuota karakter
    if (len >= 450) counter.classList.add('counter-warning');
    if (len >= 500) counter.classList.add('counter-limit');
}

// --- 12b. FORM STATE & MODAL LIFECYCLE ---
// Variabel elemen DOM pemicu dan kontainer dialog formulir tugas
const taskForm = document.getElementById('task-form');
const resetBtn = document.getElementById('btn-reset-form');
const taskModal = document.getElementById('task-modal');
const closeTaskModalBtn = document.getElementById('btn-close-task-modal');
let lastActiveTrigger = null; // Menyimpan elemen tombol yang aktif sebelum modal dibuka untuk accessibility focus restoration

// Fungsi ini fungsinya membuka modal formulir tugas, baik mode tambah baru maupun mode edit
function openTaskModal(task = null) {
    if (!taskModal) return;

    // Bersihkan status error sebelumnya sebelum formulir ditampilkan
    clearAllFieldErrors();
    hideErrorSummary();

    if (task) {
        // Baris-baris ini mengisi form dengan data tugas lama jika dalam mode penyuntingan (edit)
        document.getElementById('task-id').value = task.id;
        document.getElementById('input-judul').value = task.title;
        document.getElementById('input-matkul').value = task.course;
        document.getElementById('input-deadline').value = task.deadline;
        document.getElementById('input-priority').value = task.priority || 'medium';
        document.getElementById('input-status').value = task.status || 'todo';
        document.getElementById('input-url').value = task.lms_url || '';
        document.getElementById('input-url-label').value = task.lms_label || '';
        document.getElementById('input-notes').value = task.instructions || '';
        updateNotesCounter(task.instructions || '');

        // Baris ini menyesuaikan label tombol dan judul form untuk mode edit
        document.getElementById('form-title').textContent = 'Perbarui Kartu Tugas';
        document.getElementById('save-btn-label').textContent = 'Perbarui Kartu Tugas';
        announce(`Formulir dibuka untuk menyunting tugas "${task.title}"`);
    } else {
        // Mode tambah tugas baru: kosongkan form dan set judul default
        resetTaskForm();
        document.getElementById('form-title').textContent = 'Tambah Tugas Kuliah Baru';
        document.getElementById('save-btn-label').textContent = 'Simpan Kartu Tugas';
        announce('Formulir tambah tugas baru dibuka');
    }

    // Baris ini membuka elemen <dialog> secara modal menggunakan showModal() native HTML5
    if (typeof taskModal.showModal === 'function') {
        taskModal.showModal();
    } else {
        taskModal.setAttribute('open', '');
    }

    // Baris ini menandai atribut aria-expanded menjadi true pada tombol pembuka
    const triggerBtn = document.getElementById('btn-new-task-trigger');
    if (triggerBtn) triggerBtn.setAttribute('aria-expanded', 'true');
    const navFormBtn = document.getElementById('nav-btn-open-form');
    if (navFormBtn) navFormBtn.setAttribute('aria-expanded', 'true');

    // Baris ini memfokuskan keyboard ke input judul setelah dialog terbuka
    setTimeout(() => {
        const inputJudul = document.getElementById('input-judul');
        if (inputJudul) inputJudul.focus();
    }, 60);
}

// Fungsi ini fungsinya menutup modal formulir tugas dan mengembalikan fokus keyboard ke pemicu awal
function closeTaskModal() {
    if (!taskModal) return;

    // Baris ini memanggil fungsi close() pada elemen dialog HTML5
    if (typeof taskModal.close === 'function') {
        taskModal.close();
    } else {
        taskModal.removeAttribute('open');
    }

    // Baris ini mengembalikan status aria-expanded tombol pemicu menjadi false
    const allTriggers = [
        document.getElementById('btn-new-task-trigger'),
        document.getElementById('nav-btn-open-form')
    ];
    allTriggers.forEach(btn => { if (btn) btn.setAttribute('aria-expanded', 'false'); });

    // Baris ini memulihkan fokus keyboard ke tombol terakhir yang diklik pengguna (A11y)
    if (lastActiveTrigger) {
        lastActiveTrigger.focus();
        lastActiveTrigger = null;
    }
}

// Fungsi ini fungsinya menyiapkan data tugas yang akan diedit dan membuka modal formulir
function editTask(id) {
    const task = findTaskById(tasks, id);
    if (!task) return;
    lastActiveTrigger = document.activeElement;
    switchView('view-tasks');
    openTaskModal(task);
}

// Fungsi ini fungsinya menghapus kartu tugas dari memori dan penyimpanan setelah konfirmasi pengguna
function deleteTask(id) {
    const task = findTaskById(tasks, id);
    if (!task) return;

    // Baris ini memunculkan dialog konfirmasi sebelum menghapus permanen
    if (confirm(`Apakah Anda yakin ingin menghapus tugas "${task.title}"?`)) {
        tasks = tasks.filter(t => t.id !== id);
        saveTasks();
        announce(`Tugas "${task.title}" telah dihapus`);
    }
}

// Fungsi ini fungsinya mereset seluruh nilai isian formulir kembali ke kondisi kosong awal
function resetTaskForm() {
    if (!taskForm) return;
    taskForm.reset();
    document.getElementById('task-id').value = '';
    document.getElementById('save-btn-label').textContent = 'Simpan Kartu Tugas';
    clearAllFieldErrors();
    hideErrorSummary();
    updateNotesCounter('');
}

// --- 12c. LIVE VALIDATION PER-FIELD (BLUR & INPUT) ---
// Fungsi ini fungsinya menginisialisasi validasi seketika pada setiap kolom form saat blur/input
function initLiveFieldValidation() {
    // Objek fungsi pemanggil validator untuk masing-masing ID kolom input
    const blurValidators = {
        'input-judul':     (val) => validateTaskTitle(val),
        'input-matkul':    (val) => validateTaskCourse(val),
        'input-deadline':  (val) => validateTaskDeadline(val, 'todo', !document.getElementById('task-id').value),
        'input-priority':  (val) => validateTaskPriority(val),
        'input-status':    (val) => validateTaskStatus(val),
        'input-url':       (val) => validateTaskUrl(val),
        'input-url-label': (val) => {
            const url = document.getElementById('input-url').value;
            return validateTaskUrlLabel(val, url);
        },
        'input-notes':     (val) => validateTaskNotes(val)
    };

    const fieldKeys = {
        'input-judul':     'title',
        'input-matkul':    'course',
        'input-deadline':  'deadline',
        'input-priority':  'priority',
        'input-status':    'status',
        'input-url':       'lms_url',
        'input-url-label': 'lms_label',
        'input-notes':     'instructions'
    };

    // Baris ini memasang event blur dan input pada seluruh elemen formulir tugas
    Object.entries(blurValidators).forEach(([inputId, validator]) => {
        const input = document.getElementById(inputId);
        if (!input) return;
        // Event blur: validasi saat pengguna meninggalkan input kolom
        input.addEventListener('blur', () => {
            const error = validator(input.value);
            const fieldKey = fieldKeys[inputId];
            if (error) {
                showFieldError(fieldKey, error);
            } else {
                clearFieldError(fieldKey);
            }
        });
        // Event input: langsung bersihkan pesan error jika pengguna sudah mulai memperbaiki isian
        input.addEventListener('input', () => {
            const error = validator(input.value);
            const fieldKey = fieldKeys[inputId];
            if (!error) clearFieldError(fieldKey);
        });
    });

    // Baris ini memantau pengetikan catatan instruksi untuk memperbarui counter karakter secara dinamis
    const notesInput = document.getElementById('input-notes');
    if (notesInput) {
        notesInput.addEventListener('input', () => updateNotesCounter(notesInput.value));
    }
}

// --- 12d. DEADLINE PRESET PILLS ---
// Fungsi ini fungsinya menyetel tenggat waktu secara otomatis berdasarkan tombol pintas (+1, +3, +7 hari)
function initDeadlinePresets() {
    document.querySelectorAll('.btn-preset-pill').forEach(btn => {
        btn.addEventListener('click', () => {
            const days = parseInt(btn.getAttribute('data-days'), 10);
            if (!days) return;
            // Baris ini menghitung tanggal di masa depan pada pukul 23:59:00
            const now = new Date();
            now.setDate(now.getDate() + days);
            now.setHours(23, 59, 0, 0);
            const pad = (n) => String(n).padStart(2, '0');
            const localStr = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
            const deadlineInput = document.getElementById('input-deadline');
            // Baris ini memasukkan format tanggal ke input dan membersihkan status error deadline
            if (deadlineInput) {
                deadlineInput.value = localStr;
                clearFieldError('deadline');
                announce(`Tenggat waktu disetel ke ${days} hari ke depan`);
            }
        });
    });
}



// ==========================================================================
// 13. DASHBOARD & KALENDER
// ==========================================================================

// NodeList tombol-tombol tab navigasi utama pada header
const navTabButtons = document.querySelectorAll('.nav-tab-btn');

// Fungsi ini fungsinya beralih antar tampilan utama (Papan Tugas, Dashboard, Kalender)
function switchView(targetViewId) {
    // Baris ini memperbarui status tombol tab yang aktif
    navTabButtons.forEach(btn => {
        if (btn.getAttribute('data-target') === targetViewId) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    // Baris ini menampilkan kontainer halaman yang dipilih dan menyembunyikan halaman lainnya
    document.querySelectorAll('.app-view-section').forEach(sec => {
        if (sec.id === targetViewId) {
            sec.classList.add('active-view');
        } else {
            sec.classList.remove('active-view');
        }
    });

    // Baris ini memicu pembaruan metrik dashboard dan pengambilan data mini dashboard jika tab dashboard aktif
    if (targetViewId === 'view-dashboard') {
        updateDashboard();
        const miniContainer = document.getElementById('mini-dashboard-container');
        if (miniContainer && !miniDashboardState.isLoaded && !miniDashboardState.isLoading) {
            fetchMiniDashboardData();
        }
    }
    // Baris ini memicu render ulang grid kalender jika tab kalender aktif
    if (targetViewId === 'view-calendar') renderCalendar();
}

// Fungsi ini fungsinya menghitung ringkasan statistik tugas dan memperbarui kartu metrik di dashboard
function updateDashboard() {
    // Baris ini menghitung agregat statistik dan mengambil 4 tugas dengan sisa waktu paling kritis
    const stats = calculateTaskStatistics(tasks);
    const topUrgentList = getTopUrgentTasks(tasks, 4);

    // Variabel elemen penampil angka ringkasan statistik
    const statTotal = document.getElementById('stat-total-tasks');
    const statPlanned = document.getElementById('stat-planned-tasks');
    const statProgress = document.getElementById('stat-progress-tasks');
    const statOverdue = document.getElementById('stat-overdue-tasks');

    // Baris-baris ini memasukkan data angka statistik ke elemen DOM kartu metrik
    if (statTotal) statTotal.textContent = stats.total;
    if (statPlanned) statPlanned.textContent = stats.byStatus.todo;
    if (statProgress) statProgress.textContent = stats.byStatus.inprogress;
    if (statOverdue) statOverdue.textContent = stats.overdueCount;

    // Baris ini merender daftar tugas mendesak (< 24 jam) ke dalam kontainer tugas darurat
    const urgentContainer = document.getElementById('urgent-tasks-list');
    if (urgentContainer) {
        if (topUrgentList.length === 0) {
            urgentContainer.innerHTML = '<p style="color: var(--text-muted); font-size: 0.85rem;">Tidak ada tugas mendekati batas waktu (< 24 jam). Kondisi aman.</p>';
        } else {
            urgentContainer.innerHTML = topUrgentList.map(t => {
                const time = formatDeadline(t.deadline, t.status);
                return `
                    <div style="background: var(--input-bg); border: 1px solid var(--border-subtle); padding: 0.85rem 1rem; border-radius: 8px; border-left: 3px solid #f87171; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="color: var(--text-main); font-size: 0.875rem;">${escapeHtml(t.title)}</strong>
                            <span style="display: block; font-size: 0.775rem; color: #fca5a5;">${time.timeStr} | ${escapeHtml(t.course)}</span>
                        </div>
                        <button type="button" class="btn-new-task" style="padding: 0.35rem 0.8rem; font-size: 0.75rem;" onclick="window.openDetailDirect('${t.id}')">
                            Rincian
                        </button>
                    </div>
                `;
            }).join('');
        }
    }
}


// ==========================================================================
// 14. FITUR MINI DASHBOARD AKADEMIK (FETCH API & JSON LOCAL FEED)
// Mengimplementasikan fetch(), async/await, try/catch, dan response.ok
// Memiliki 3 State: Loading, Error, dan Data Loaded (Kartu & Tabel)
// Dilengkapi Fitur Pencarian Real-Time dan Pemfilteran Kategori & Status
// ==========================================================================

// Objek ini fungsinya menyimpan seluruh status (state) data, indikator loading, error, serta filter mini dashboard
const miniDashboardState = {
    data: null,
    isLoaded: false,
    isLoading: false,
    error: null,
    activeTab: 'cards', // 'cards' | 'table'
    searchQuery: '',
    categoryFilter: 'all',
    statusFilter: 'all'
};

// Fungsi asinkronus ini fungsinya memuat data akademik eksternal dari endpoint JSON lokal menggunakan Fetch API
async function fetchMiniDashboardData() {
    // Baris ini mengaktifkan status loading dan membersihkan pesan error lama
    setMiniDashboardLoading(true);
    miniDashboardState.error = null;

    try {
        // Menggunakan file JSON lokal di data/academic-dashboard.json
        const endpointUrl = 'data/academic-dashboard.json';
        // Baris ini melakukan HTTP GET request dengan konfigurasi tanpa cache
        const response = await fetch(endpointUrl, {
            headers: {
                'Accept': 'application/json'
            },
            cache: 'no-store'
        });

        // Baris ini memeriksa apakah status respons HTTP berada pada rentang sukses 200-299
        if (!response.ok) {
            throw new Error(`Gagal memuat endpoint '${endpointUrl}'. Status HTTP: ${response.status} (${response.statusText || 'Respon Gagal'})`);
        }

        // Baris ini mengonversi badan respons HTTP menjadi objek JSON Javascript
        const jsonData = await response.json();

        // Baris ini memvalidasi integritas struktur dasar data JSON yang diterima
        if (!jsonData || !jsonData.courses || !Array.isArray(jsonData.courses)) {
            throw new Error('Format data JSON tidak valid: properti courses tidak ditemukan atau bukan array.');
        }

        // Baris ini menyimpan data ke dalam state dan memicu render antarmuka
        miniDashboardState.data = jsonData;
        miniDashboardState.isLoaded = true;
        setMiniDashboardLoading(false);
        renderMiniDashboard();
        announce('Data mini dashboard akademik berhasil dimuat');
    } catch (error) {
        // Baris ini menangani error jaringan atau parsing dan menampilkan state error ke antarmuka pengguna
        console.error('Error saat memuat Mini Dashboard:', error);
        setMiniDashboardError(error.message || 'Terjadi kesalahan tidak terduga saat memuat data akademik.');
        announce('Gagal memuat data mini dashboard akademik');
    }
}

// Fungsi ini fungsinya mengatur tampilan status proses pemuatan (loading state) pada elemen DOM
function setMiniDashboardLoading(isLoading) {
    miniDashboardState.isLoading = isLoading;
    const loadingEl = document.getElementById('mini-dashboard-loading');
    const errorEl = document.getElementById('mini-dashboard-error');
    const contentEl = document.getElementById('mini-dashboard-content');
    const toolbarEl = document.getElementById('mini-dashboard-toolbar');
    const studentBarEl = document.getElementById('mini-dashboard-student-bar');

    // Baris ini menampilkan indikator animasi loading dan menyembunyikan konten jika isLoading bernilai true
    if (isLoading) {
        if (loadingEl) loadingEl.style.display = 'flex';
        if (errorEl) errorEl.style.display = 'none';
        if (contentEl) contentEl.style.display = 'none';
        if (toolbarEl) toolbarEl.style.display = 'none';
        if (studentBarEl) studentBarEl.style.display = 'none';
    } else {
        if (loadingEl) loadingEl.style.display = 'none';
    }
}

// Fungsi ini fungsinya menampilkan status gagal/kesalahan (error state) lengkap dengan tombol coba lagi
function setMiniDashboardError(errorMessage) {
    miniDashboardState.isLoading = false;
    miniDashboardState.error = errorMessage;

    const loadingEl = document.getElementById('mini-dashboard-loading');
    const errorEl = document.getElementById('mini-dashboard-error');
    const errorMsgEl = document.getElementById('mini-dashboard-error-message');
    const contentEl = document.getElementById('mini-dashboard-content');
    const toolbarEl = document.getElementById('mini-dashboard-toolbar');
    const studentBarEl = document.getElementById('mini-dashboard-student-bar');

    // Sembunyikan konten normal dan loading saat error terjadi
    if (loadingEl) loadingEl.style.display = 'none';
    if (contentEl) contentEl.style.display = 'none';
    if (toolbarEl) toolbarEl.style.display = 'none';
    if (studentBarEl) studentBarEl.style.display = 'none';

    // Baris ini memunculkan kotak pesan error dengan teks penjelasan
    if (errorEl) {
        errorEl.style.display = 'flex';
        if (errorMsgEl) {
            errorMsgEl.textContent = errorMessage;
        }
    }
}

// Fungsi ini fungsinya merender seluruh isi data mini dashboard ke antarmuka (profil, kartu, tabel, dan filter)
function renderMiniDashboard() {
    if (!miniDashboardState.data) return;

    const data = miniDashboardState.data;
    const studentBarEl = document.getElementById('mini-dashboard-student-bar');
    const toolbarEl = document.getElementById('mini-dashboard-toolbar');
    const contentEl = document.getElementById('mini-dashboard-content');
    const cardsContainer = document.getElementById('mini-dashboard-cards-container');
    const tableContainer = document.getElementById('mini-dashboard-table-container');
    const tableBody = document.getElementById('mini-dashboard-table-body');
    const emptyEl = document.getElementById('mini-dashboard-empty');
    const countIndicator = document.getElementById('mini-dash-count-indicator');

    // 1. Render Baris Ringkasan Profil Mahasiswa & Agregat
    if (studentBarEl && data.student && data.summary) {
        studentBarEl.style.display = 'grid';
        studentBarEl.innerHTML = `
            <div class="student-meta-item">
                <span class="student-meta-label">Mahasiswa</span>
                <span class="student-meta-value">${safeStr(data.student.name)}</span>
            </div>
            <div class="student-meta-item">
                <span class="student-meta-label">Program &amp; Semester</span>
                <span class="student-meta-value">${safeStr(data.student.semester)}</span>
            </div>
            <div class="student-meta-item">
                <span class="student-meta-label">Beban Akademik</span>
                <span class="student-meta-value student-meta-highlight">${safeStr(data.student.total_sks)} SKS (${data.summary.total_courses} Mata Kuliah)</span>
            </div>
            <div class="student-meta-item">
                <span class="student-meta-label">Penyelesaian Tugas</span>
                <span class="student-meta-value student-meta-highlight">${data.summary.completed_assignments} / ${data.summary.total_assignments} Selesai (${data.summary.overall_completion_rate}%)</span>
            </div>
            <div class="student-meta-item">
                <span class="student-meta-label">Tenggat Kritis</span>
                <span class="student-meta-value" style="color: #f87171;">${data.summary.critical_deadlines} Tugas Mendekati Batas</span>
            </div>
        `;
    }

    // 2. Tampilkan Toolbar dan Content
    if (toolbarEl) toolbarEl.style.display = 'flex';
    if (contentEl) contentEl.style.display = 'block';

    // 3. Filter data berdasarkan kata kunci pencarian, kategori, dan status mata kuliah
    const rawCourses = data.courses || [];
    const query = miniDashboardState.searchQuery.toLowerCase().trim();
    const cat = miniDashboardState.categoryFilter;
    const stat = miniDashboardState.statusFilter;

    // Baris ini menyaring array mata kuliah berdasarkan kriteria pencarian dan dropdown filter
    const filtered = rawCourses.filter(c => {
        // Filter Pencarian Teks
        const matchesQuery = !query ||
            (c.name && c.name.toLowerCase().includes(query)) ||
            (c.code && c.code.toLowerCase().includes(query)) ||
            (c.lecturer && c.lecturer.toLowerCase().includes(query)) ||
            (c.room && c.room.toLowerCase().includes(query));

        // Filter Kategori
        const matchesCategory = (cat === 'all') || (c.category === cat);

        // Filter Status
        const matchesStatus = (stat === 'all') || (c.status === stat);

        return matchesQuery && matchesCategory && matchesStatus;
    });

    // Baris ini memperbarui indikator jumlah data yang sedang ditampilkan
    if (countIndicator) {
        countIndicator.textContent = `Menampilkan ${filtered.length} dari ${rawCourses.length} mata kuliah`;
    }

    // 4. Periksa jika hasil filter kosong dan tampilkan status empty jika tidak ada data yang cocok
    if (filtered.length === 0) {
        if (cardsContainer) cardsContainer.style.display = 'none';
        if (tableContainer) tableContainer.style.display = 'none';
        if (emptyEl) emptyEl.style.display = 'flex';
        return;
    }

    if (emptyEl) emptyEl.style.display = 'none';

    // 5. Render sesuai mode tampilan aktif (Kartu vs Tabel)
    if (miniDashboardState.activeTab === 'cards') {
        // Mode Tampilan Kartu
        if (cardsContainer) {
            cardsContainer.style.display = 'grid';
            cardsContainer.innerHTML = filtered.map(c => {
                const catClass = c.category === 'Praktikum' ? 'cat-praktikum' : (c.category === 'Teori' ? 'cat-teori' : 'cat-proyek');
                const statClass = c.status === 'Aktif' ? 'status-aktif' : (c.status === 'Tuntas' ? 'status-tuntas' : 'status-perhatian');

                return `
                    <article class="mini-course-card" id="card-${safeStr(c.id)}">
                        <div class="mini-card-header">
                            <div class="mini-card-code-wrap">
                                <span class="mini-course-code">${safeStr(c.code)}</span>
                                <span class="mini-course-category-badge ${catClass}">${safeStr(c.category)}</span>
                            </div>
                            <span class="mini-status-pill ${statClass}">${safeStr(c.status)}</span>
                        </div>

                        <h3 class="mini-course-title">${safeStr(c.name)}</h3>

                        <div class="mini-course-meta">
                            <div class="mini-meta-row">
                                <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <span>${safeStr(c.lecturer)}</span>
                            </div>
                            <div class="mini-meta-row">
                                <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                <span>${safeStr(c.sks)} SKS | ${safeStr(c.schedule)} (${safeStr(c.room)})</span>
                            </div>
                        </div>

                        <div class="mini-course-progress-wrap">
                            <div class="mini-progress-header">
                                <span>Progres Tugas Kuliah</span>
                                <span><strong>${c.tasks_completed}/${c.tasks_total}</strong> (${c.progress_percent}%)</span>
                            </div>
                            <div class="mini-progress-track">
                                <div class="mini-progress-fill" style="width: ${c.progress_percent}%;"></div>
                            </div>
                        </div>

                        <div class="mini-next-task-alert">
                            <span class="mini-task-label">Tugas Terdekat</span>
                            <span class="mini-task-name" title="${safeStr(c.next_task_title)}">${safeStr(c.next_task_title)}</span>
                            <span class="mini-task-deadline">Tenggat: ${formatDateTimeSimple(c.next_deadline)}</span>
                        </div>
                    </article>
                `;
            }).join('');
        }
        if (tableContainer) tableContainer.style.display = 'none';
    } else {
        // Mode Tabel Semantik
        if (tableContainer) {
            tableContainer.style.display = 'block';
            if (tableBody) {
                tableBody.innerHTML = filtered.map(c => {
                    const catClass = c.category === 'Praktikum' ? 'cat-praktikum' : (c.category === 'Teori' ? 'cat-teori' : 'cat-proyek');
                    const statClass = c.status === 'Aktif' ? 'status-aktif' : (c.status === 'Tuntas' ? 'status-tuntas' : 'status-perhatian');

                    return `
                        <tr>
                            <td>
                                <strong style="color: var(--text-main); display: block;">${safeStr(c.name)}</strong>
                                <span style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace;">${safeStr(c.code)}</span>
                            </td>
                            <td><span class="mini-course-category-badge ${catClass}">${safeStr(c.category)}</span></td>
                            <td>${safeStr(c.sks)} SKS</td>
                            <td style="color: var(--text-muted);">${safeStr(c.lecturer)}</td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 0.25rem; min-width: 120px;">
                                    <span style="font-size: 0.725rem; font-weight: 600;">${c.tasks_completed}/${c.tasks_total} (${c.progress_percent}%)</span>
                                    <div class="mini-progress-track" style="height: 5px;">
                                        <div class="mini-progress-fill" style="width: ${c.progress_percent}%;"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-weight: 600; color: var(--text-main); font-size: 0.78rem;">${safeStr(c.next_task_title)}</span>
                                    <span style="font-size: 0.72rem; color: #fca5a5;">${formatDateTimeSimple(c.next_deadline)}</span>
                                </div>
                            </td>
                            <td><span class="mini-status-pill ${statClass}">${safeStr(c.status)}</span></td>
                        </tr>
                    `;
                }).join('');
            }
        }
        if (cardsContainer) cardsContainer.style.display = 'none';
    }
}

// Fungsi ini fungsinya memformat string ISO tanggal ke format baca ringkas bahasa Indonesia
function formatDateTimeSimple(isoString) {
    if (!isoString) return 'Tidak ada';
    try {
        const d = new Date(isoString);
        if (isNaN(d.getTime())) return isoString;
        const options = { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' };
        return d.toLocaleDateString('id-ID', options);
    } catch {
        return isoString;
    }
}

// Fungsi ini fungsinya menginisialisasi seluruh event listener interaktif pada fitur Mini Dashboard
function initMiniDashboard() {
    const miniContainer = document.getElementById('mini-dashboard-container');
    if (!miniContainer) return; // Jangan jalankan jika elemen dinonaktifkan di tampilan

    // Baris ini memasang event listener untuk tombol Muat Ulang Data
    const refreshBtn = document.getElementById('btn-refresh-mini-dashboard');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            fetchMiniDashboardData();
        });
    }

    // Baris ini memasang event listener untuk tombol Coba Lagi pada tampilan error
    const retryBtn = document.getElementById('btn-mini-dashboard-retry');
    if (retryBtn) {
        retryBtn.addEventListener('click', () => {
            fetchMiniDashboardData();
        });
    }

    // Baris ini memasang switcher tab mode tampilan: Kartu vs Tabel
    const tabCards = document.getElementById('tab-mini-cards');
    const tabTable = document.getElementById('tab-mini-table');

    if (tabCards && tabTable) {
        tabCards.addEventListener('click', () => {
            miniDashboardState.activeTab = 'cards';
            tabCards.classList.add('active');
            tabCards.setAttribute('aria-selected', 'true');
            tabTable.classList.remove('active');
            tabTable.setAttribute('aria-selected', 'false');
            renderMiniDashboard();
        });

        tabTable.addEventListener('click', () => {
            miniDashboardState.activeTab = 'table';
            tabTable.classList.add('active');
            tabTable.setAttribute('aria-selected', 'true');
            tabCards.classList.remove('active');
            tabCards.setAttribute('aria-selected', 'false');
            renderMiniDashboard();
        });
    }

    // Baris ini memasang input event untuk pencarian data secara instan (real-time)
    const searchInput = document.getElementById('mini-dash-search');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            miniDashboardState.searchQuery = e.target.value;
            renderMiniDashboard();
        });
    }

    // Baris ini memasang change event untuk filter kategori mata kuliah
    const filterCat = document.getElementById('mini-dash-filter-category');
    if (filterCat) {
        filterCat.addEventListener('change', (e) => {
            miniDashboardState.categoryFilter = e.target.value;
            renderMiniDashboard();
        });
    }

    // Baris ini memasang change event untuk filter status mata kuliah
    const filterStat = document.getElementById('mini-dash-filter-status');
    if (filterStat) {
        filterStat.addEventListener('change', (e) => {
            miniDashboardState.statusFilter = e.target.value;
            renderMiniDashboard();
        });
    }

    // Baris ini mereset seluruh query pencarian dan dropdown filter ke opsi default
    const resetFilterBtn = document.getElementById('btn-mini-reset-filter');
    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', () => {
            miniDashboardState.searchQuery = '';
            miniDashboardState.categoryFilter = 'all';
            miniDashboardState.statusFilter = 'all';

            if (searchInput) searchInput.value = '';
            if (filterCat) filterCat.value = 'all';
            if (filterStat) filterStat.value = 'all';

            renderMiniDashboard();
        });
    }

    // Baris ini memanggil pemuatan data perdana saat mini dashboard diinisialisasi
    fetchMiniDashboardData();
}

// Variabel objek Date penanda bulan dan tahun kalender yang sedang aktif ditampilkan
let currentCalendarDate = new Date();

// Fungsi ini fungsinya merender tampilan grid kalender bulanan serta menandai tugas pada tanggal tenggatnya
function renderCalendar() {
    const grid = document.getElementById('calendar-grid-cells');
    const titleMonth = document.getElementById('calendar-month-year');
    if (!grid) return;

    const year = currentCalendarDate.getFullYear();
    const month = currentCalendarDate.getMonth();

    // Baris ini memperbarui judul bulan dan tahun di header kalender
    const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    if (titleMonth) titleMonth.textContent = `${monthNames[month]} ${year}`;

    grid.innerHTML = '';

    // Baris ini membuat sel-sel nama hari (Min, Sen, Sel, dst)
    const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    days.forEach(d => {
        const dh = document.createElement('div');
        dh.className = 'cal-day-name';
        dh.textContent = d;
        grid.appendChild(dh);
    });

    const firstDayIndex = new Date(year, month, 1).getDay();
    const lastDayDate = new Date(year, month + 1, 0).getDate();

    // Baris ini merender sel-sel kosong untuk mengisi awalan minggu sebelum tanggal 1
    for (let i = 0; i < firstDayIndex; i++) {
        const cell = document.createElement('div');
        cell.className = 'cal-cell';
        cell.style.opacity = '0.3';
        grid.appendChild(cell);
    }

    const today = new Date();
    // Baris ini mengelompokkan data tugas berdasarkan format tanggal YYYY-MM-DD
    const tasksByDate = groupTasksByDate(tasks);

    // Baris ini merender setiap sel tanggal dari tanggal 1 sampai akhir bulan
    for (let day = 1; day <= lastDayDate; day++) {
        const cell = document.createElement('div');
        cell.className = 'cal-cell';

        // Baris ini menandai sel hari ini jika tanggal, bulan, dan tahun cocok dengan waktu sistem
        const isToday = (today.getDate() === day && today.getMonth() === month && today.getFullYear() === year);
        if (isToday) cell.classList.add('today');

        const cellDateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const dayTasks = tasksByDate[cellDateStr] || [];

        // Baris ini merender pill penanda untuk setiap tugas yang jatuh tempo pada tanggal tersebut
        let tasksPillHtml = '';
        dayTasks.forEach(t => {
            let color = '#3b82f6';
            if (t.status === 'done') color = '#10b981';
            else if (t.status === 'overdue') color = '#f97316';
            else if (t.status === 'todo') color = '#d946ef';

            tasksPillHtml += `
                <span class="cal-task-pill" style="background: ${color}; cursor: pointer;" onclick="window.openDetailDirect('${t.id}')" title="${escapeHtml(t.title)} (${escapeHtml(t.course)})">
                    ${escapeHtml(t.title)}
                </span>
            `;
        });

        cell.innerHTML = `
            <span class="cal-cell-num">${day}</span>
            ${tasksPillHtml}
        `;

        grid.appendChild(cell);
    }
}

// ==========================================================================
// 15. SWITCHER TAMPILAN KANBAN / LIST
// ==========================================================================

// Fungsi ini fungsinya beralih ke mode tampilan daftar tabel (List View) dan menyimpan preferensi ke localStorage
function activateListView() {
    const tabKanban = document.getElementById('tab-view-kanban');
    const tabList = document.getElementById('tab-view-list');
    const kanbanContainer = document.getElementById('kanban-view-container');
    const listContainer = document.getElementById('list-view-container');

    if (tabList && tabKanban) {
        // Baris ini menandai tombol tab list sebagai aktif dan aria-selected true
        tabList.classList.add('active');
        tabList.setAttribute('aria-selected', 'true');
        tabKanban.classList.remove('active');
        tabKanban.setAttribute('aria-selected', 'false');

        // Baris ini menyembunyikan kontainer papan Kanban dan menampilkan kontainer daftar tabel
        if (kanbanContainer) kanbanContainer.style.display = 'none';
        if (listContainer) listContainer.style.display = 'block';
        // Baris ini menyimpan preferensi mode list ke penyimpanan lokal browser
        localStorage.setItem(STORAGE_KEY_VIEW_MODE, 'list');
        renderListView();
    }
}

// Fungsi ini fungsinya beralih ke mode tampilan papan kolom kartu (Kanban View) dan menyimpan preferensi ke localStorage
function activateKanbanView() {
    const tabKanban = document.getElementById('tab-view-kanban');
    const tabList = document.getElementById('tab-view-list');
    const kanbanContainer = document.getElementById('kanban-view-container');
    const listContainer = document.getElementById('list-view-container');

    if (tabKanban && tabList) {
        // Baris ini menandai tombol tab Kanban sebagai aktif dan aria-selected true
        tabKanban.classList.add('active');
        tabKanban.setAttribute('aria-selected', 'true');
        tabList.classList.remove('active');
        tabList.setAttribute('aria-selected', 'false');

        // Baris ini menampilkan papan Kanban dan menyembunyikan daftar tabel
        if (kanbanContainer) kanbanContainer.style.display = 'block';
        if (listContainer) listContainer.style.display = 'none';
        // Baris ini menyimpan preferensi mode kanban ke penyimpanan lokal browser
        localStorage.setItem(STORAGE_KEY_VIEW_MODE, 'kanban');
    }
}

// ==========================================================================
// 16. INISIALISASI APLIKASI & EVENT LISTENERS
// ==========================================================================

// Fungsi ini fungsinya menginisialisasi seluruh konfigurasi awal, me-render tampilan awal, dan memasang event listener
function initApp() {
    // Baris-baris ini menginisialisasi preferensi tema, kontrol filter/sort, dropzone, live validation, dan preset tanggal
    initPreferences();
    initFilterAndSortControls();
    initDropzones();
    initLiveFieldValidation();
    initDeadlinePresets();

    // Baris-baris ini merender data awal ke seluruh tampilan (Filter, Kanban, List, Dashboard, Kalender)
    updateFilterOptions();
    renderBoard();
    renderListView();
    updateDashboard();
    renderCalendar();

    // Baris ini memasang event listener untuk tombol saklar tema cepat pada header
    const themeToggleBtn = document.getElementById('btn-theme-toggle');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', cycleTheme);
    }

    // Baris ini menangani perubahan pilihan tema dari dropdown pengaturan
    const themeSelect = document.getElementById('select-theme');
    if (themeSelect) {
        themeSelect.addEventListener('change', (e) => {
            applyTheme(e.target.value);
            announce(`Tema diubah ke ${e.target.value}`);
        });
    }

    // Baris ini menangani perubahan pilihan tingkat kerapatan spasi (density) dari dropdown pengaturan
    const densitySelect = document.getElementById('select-density');
    if (densitySelect) {
        densitySelect.addEventListener('change', (e) => {
            applyDensity(e.target.value);
            announce(`Kerapatan tampilan diubah ke ${e.target.value}`);
        });
    }

    // Baris ini menyimpan preferensi efek suara feedback audio ke localStorage
    const soundCheck = document.getElementById('check-enable-sound');
    if (soundCheck) {
        soundCheck.addEventListener('change', (e) => {
            localStorage.setItem(STORAGE_KEY_SOUND, e.target.checked ? 'true' : 'false');
        });
    }

    // Baris ini memasang event klik pada tombol switcher tampilan Kanban dan List
    const tabKanban = document.getElementById('tab-view-kanban');
    const tabList = document.getElementById('tab-view-list');
    if (tabKanban) {
        tabKanban.addEventListener('click', () => {
            activateKanbanView();
            announce('Tampilan diubah ke Kanban view');
        });
    }
    if (tabList) {
        tabList.addEventListener('click', () => {
            activateListView();
            announce('Tampilan diubah ke List view');
        });
    }

    // Baris ini memasang event klik pada setiap tombol tab navigasi utama
    navTabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-target');
            if (target) switchView(target);
        });
    });

    // Baris ini menangani klik pada brand logo untuk beralih langsung ke Dashboard tanpa reload penuh
    const brandLogo = document.querySelector('.brand-logo');
    if (brandLogo) {
        brandLogo.addEventListener('click', (e) => {
            e.preventDefault();
            switchView('view-dashboard');
        });
    }

    // Baris ini menangani tombol "Tambah Tugas" pada toolbar utama
    const topNewTaskBtn = document.getElementById('btn-new-task-trigger');
    if (topNewTaskBtn) {
        topNewTaskBtn.addEventListener('click', (e) => {
            e.preventDefault();
            lastActiveTrigger = topNewTaskBtn;
            switchView('view-tasks');
            openTaskModal(null);
        });
    }

    // Baris ini menangani tombol CTA "Tambah Tugas" pada bilah navigasi header
    const navFormBtn = document.getElementById('nav-btn-open-form');
    if (navFormBtn) {
        navFormBtn.addEventListener('click', (e) => {
            e.preventDefault();
            lastActiveTrigger = navFormBtn;
            switchView('view-tasks');
            openTaskModal(null);
        });
    }

    // Baris ini memasang penutupan modal formulir tugas saat tombol tutup diklik
    if (closeTaskModalBtn) closeTaskModalBtn.addEventListener('click', closeTaskModal);
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            resetTaskForm();
            closeTaskModal();
        });
    }

    // Baris ini menangani klik di luar backdrop dialog atau tombol Escape untuk menutup modal
    if (taskModal) {
        taskModal.addEventListener('click', (e) => {
            if (e.target === taskModal) closeTaskModal();
        });
        taskModal.addEventListener('cancel', () => {
            [document.getElementById('btn-new-task-trigger'),
             document.getElementById('nav-btn-open-form')]
                .forEach(btn => { if (btn) btn.setAttribute('aria-expanded', 'false'); });
        });
    }

    // Baris ini menangani pintasan keyboard Ctrl+Enter atau Cmd+Enter untuk submit formulir tugas seketika
    if (taskForm) {
        taskForm.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                taskForm.requestSubmit();
            }
        });
    }

    // Baris ini menangani event pengiriman (submit) formulir tugas beserta validasi komprehensif
    if (taskForm) {
        taskForm.addEventListener('submit', (e) => {
            e.preventDefault();

            // Baris ini mengambil data formulir dan memeriksa apakah mode buat baru atau edit
            const rawData = collectFormData();
            const editId = document.getElementById('task-id').value;
            const isNew = !editId;

            clearAllFieldErrors();
            hideErrorSummary();

            // Baris ini menjalankan validasi menyeluruh pada seluruh kolom input
            const result = validateTaskFormData(rawData, { isNew });

            // Baris ini memeriksa apakah formulir memiliki kesalahan validasi
            if (!result.isValid) {
                // Tampilkan pesan error pada masing-masing field
                Object.entries(result.errors).forEach(([key, msg]) => showFieldError(key, msg));
                // Tampilkan kotak ringkasan error di bagian atas formulir
                showErrorSummary(result.errors);
                announce('Formulir mengandung kesalahan. Mohon periksa kembali isian Anda.');
                return;
            }

            try {
                // Baris ini menyusun objek tugas yang sudah tervalidasi dan tersanitasi
                const validatedTask = validateTask(result.sanitizedData);

                if (!isNew) {
                    // Mode perbarui tugas yang sudah ada
                    const idx = findTaskIndex(tasks, editId);
                    if (idx > -1) {
                        tasks[idx] = validatedTask;
                        announce(`Tugas "${validatedTask.title}" berhasil diperbarui`);
                    }
                } else {
                    // Mode tambahkan tugas baru ke dalam daftar
                    tasks.push(validatedTask);
                    announce(`Tugas baru "${validatedTask.title}" berhasil ditambahkan`);
                }

                // Baris ini menyimpan perubahan ke localStorage, mereset formulir, dan menutup modal
                saveTasks();
                resetTaskForm();
                closeTaskModal();
            } catch (err) {
                // Baris ini menangani error data tugas tak terduga
                if (err instanceof TaskDataError) {
                    showErrorSummary({ _general: err.message });
                    announce(`Validasi gagal: ${err.message}`);
                } else {
                    console.error('Terjadi kesalahan:', err);
                    showErrorSummary({ _general: 'Terjadi kesalahan yang tidak diharapkan.' });
                }
            }
        });
    }

    // Baris ini menangani tombol penempelan (paste) teks instruksi dari clipboard perangkat pengguna
    const pasteBtn = document.getElementById('btn-paste-clipboard');
    if (pasteBtn) {
        pasteBtn.addEventListener('click', async () => {
            try {
                // Baris ini mencoba membaca clipboard menggunakan Async Clipboard API modern
                if (navigator.clipboard && navigator.clipboard.readText) {
                    const text = await navigator.clipboard.readText();
                    if (text) {
                        const textarea = document.getElementById('input-notes');
                        textarea.value = (textarea.value ? textarea.value + '\n\n' : '') + text;
                        announce('Teks berhasil ditempel dari clipboard');
                        return;
                    }
                }
                // Fallback menggunakan prompt dialog jika clipboard API tidak diizinkan
                const promptText = prompt('Tempel instruksi e-learning di sini:');
                if (promptText) {
                    const textarea = document.getElementById('input-notes');
                    textarea.value = (textarea.value ? textarea.value + '\n\n' : '') + promptText;
                }
            } catch {
                const promptText = prompt('Tempel instruksi e-learning di sini:');
                if (promptText) {
                    const textarea = document.getElementById('input-notes');
                    textarea.value = (textarea.value ? textarea.value + '\n\n' : '') + promptText;
                }
            }
        });
    }

    // Baris ini memasang event listener untuk menutup modal detail rincian tugas
    if (closeDetailModalBtn) closeDetailModalBtn.addEventListener('click', closeDetailModal);
    if (closeDetailSecondaryBtn) closeDetailSecondaryBtn.addEventListener('click', closeDetailModal);
    if (detailModal) {
        detailModal.addEventListener('click', (e) => {
            if (e.target === detailModal) closeDetailModal();
        });
    }

    // Baris ini menangani penyalinan isi catatan tugas ke clipboard dari modal detail
    if (copyDetailNotesBtn) {
        copyDetailNotesBtn.addEventListener('click', async () => {
            const task = findTaskById(tasks, activeDetailTaskId);
            if (!task || !task.instructions) {
                alert('Tidak ada instruksi untuk disalin.');
                return;
            }
            try {
                await navigator.clipboard.writeText(task.instructions);
                const label = document.getElementById('copy-notes-label');
                if (label) {
                    const original = label.textContent;
                    label.textContent = 'Tersalin!';
                    setTimeout(() => { label.textContent = original; }, 1800);
                }
                announce('Catatan tugas berhasil disalin ke clipboard');
            } catch {
                alert('Gagal menyalin catatan ke clipboard');
            }
        });
    }

    // Baris ini membuka formulir edit langsung dari modal rincian tugas
    if (editFromDetailBtn) {
        editFromDetailBtn.addEventListener('click', () => {
            const id = activeDetailTaskId;
            closeDetailModal();
            if (id) editTask(id);
        });
    }

    // Baris ini memasang event listener untuk membuka dan menutup modal ringkasan cepat (summary)
    if (quickSummaryBtn) quickSummaryBtn.addEventListener('click', openSummaryModal);
    if (closeSummaryBtn) closeSummaryBtn.addEventListener('click', closeSummaryModal);
    if (closeSummaryBottomBtn) closeSummaryBottomBtn.addEventListener('click', closeSummaryModal);
    if (summaryModal) {
        summaryModal.addEventListener('click', (e) => {
            if (e.target === summaryModal) closeSummaryModal();
        });
    }

    // Baris ini memasang event listener untuk tombol navigasi bulan pada kalender
    const prevMonthBtn = document.getElementById('btn-prev-month');
    const nextMonthBtn = document.getElementById('btn-next-month');
    const todayMonthBtn = document.getElementById('btn-today-month');

    // Tombol bulan sebelumnya
    if (prevMonthBtn) {
        prevMonthBtn.addEventListener('click', () => {
            currentCalendarDate.setMonth(currentCalendarDate.getMonth() - 1);
            renderCalendar();
        });
    }
    // Tombol bulan berikutnya
    if (nextMonthBtn) {
        nextMonthBtn.addEventListener('click', () => {
            currentCalendarDate.setMonth(currentCalendarDate.getMonth() + 1);
            renderCalendar();
        });
    }
    // Tombol kembali ke bulan saat ini
    if (todayMonthBtn) {
        todayMonthBtn.addEventListener('click', () => {
            currentCalendarDate = new Date();
            renderCalendar();
        });
    }

    // Elemen modal dan kontrol profil mahasiswa & preferensi aplikasi
    const settingsModal = document.getElementById('settings-modal');
    const openSettingsBtn = document.getElementById('btn-open-settings');
    const openProfileBtn = document.getElementById('btn-open-profile');
    const closeSettingsBtn = document.getElementById('btn-close-settings');
    const googleSsoBtn = document.getElementById('btn-google-sso-modal');
    const profileUserName = document.getElementById('profile-user-name');
    const notifBadge = document.getElementById('notif-badge');

    // Fungsi ini fungsinya membuka modal dialog pengaturan dan profil pengguna
    function openSettingsModal() {
        if (settingsModal) {
            if (typeof settingsModal.showModal === 'function') settingsModal.showModal();
            else settingsModal.setAttribute('open', '');
        }
    }

    // Fungsi ini fungsinya menutup modal dialog pengaturan dan profil pengguna
    function closeSettingsModal() {
        if (settingsModal) {
            if (typeof settingsModal.close === 'function') settingsModal.close();
            else settingsModal.removeAttribute('open');
        }
    }

    // Baris ini memasang event klik untuk tombol buka dan tutup modal pengaturan
    if (openSettingsBtn) openSettingsBtn.addEventListener('click', openSettingsModal);
    if (openProfileBtn) openProfileBtn.addEventListener('click', openSettingsModal);
    if (closeSettingsBtn) closeSettingsBtn.addEventListener('click', closeSettingsModal);

    // Baris ini menangani simulasi autentikasi Google SSO Mahasiswa
    if (googleSsoBtn) {
        googleSsoBtn.addEventListener('click', () => {
            const mock = { name: 'Muhammad Dzakir Dzakwan (SSO Aktif)', email: 'dzakir.dzakwan@mahasiswa.ac.id' };
            localStorage.setItem(STORAGE_KEY_AUTH, JSON.stringify(mock));
            if (profileUserName) profileUserName.textContent = mock.name;
            closeSettingsModal();
            alert('Berhasil masuk via Google SSO Mahasiswa!');
            announce('Berhasil masuk menggunakan Google SSO');
        });
    }

    // Baris ini menangani permintaan izin Web Notification API dari browser pengguna
    const notifDashboardBtn = document.getElementById('btn-dashboard-enable-notif');
    if (notifDashboardBtn) {
        notifDashboardBtn.addEventListener('click', async () => {
            if (!('Notification' in window)) {
                alert('Browser Anda belum mendukung Web Notification API.');
                return;
            }
            try {
                // Meminta izin notifikasi browser
                const perm = await Notification.requestPermission();
                if (perm === 'granted') {
                    localStorage.setItem(STORAGE_KEY_NOTIF, 'granted');
                    if (notifBadge) notifBadge.classList.add('active');
                    new Notification('TaskTrack: Notifikasi Aktif', {
                        body: 'Pengingat deadline otomatis H-1 dan H-3 jam sebelum waktu pengumpulan telah diaktifkan.',
                        icon: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect width="32" height="32" rx="8" fill="%23141720"/><path d="M10 16.5L14 20.5L22 11" stroke="%23facc15" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>'
                    });
                    alert('Izin notifikasi berhasil diaktifkan!');
                }
            } catch (e) {
                console.error('Izin notifikasi gagal', e);
            }
        });
    }

    // Baris ini mengaktifkan badge notifikasi jika izin browser sebelumnya sudah diberikan
    if (window.Notification && Notification.permission === 'granted') {
        if (notifBadge) notifBadge.classList.add('active');
    }

    // Baris ini memanggil inisialisasi mini dashboard akademik jika wadah elemen tersedia di DOM
    if (document.getElementById('mini-dashboard-container')) {
        initMiniDashboard();
    }

    // Baris ini memastikan saat pertama kali membuka atau memuat TaskTrack, tampilan awal yang aktif adalah Dashboard
    switchView('view-dashboard');
}

// Baris ini memastikan initApp dijalankan tepat saat struktur DOM halaman selesai dimuat
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}
