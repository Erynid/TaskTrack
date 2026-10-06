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
let tasks = loadTasks();
let filterState = {
    course: 'all',
    search: '',
    sort: 'deadline-asc'
};

function loadTasks() {
    const stored = localStorage.getItem(STORAGE_KEY_TASKS);
    if (stored) {
        return parseTasksFromJSON(stored, defaultTasks);
    }
    localStorage.setItem(STORAGE_KEY_TASKS, JSON.stringify(defaultTasks));
    return [...defaultTasks];
}

function saveTasks() {
    localStorage.setItem(STORAGE_KEY_TASKS, JSON.stringify(tasks));
    updateFilterOptions();
    renderBoard();
    renderListView();
    updateDashboard();
    renderCalendar();
}

// 5. HELPER DOM & AKSESIBILITAS
function announce(message) {
    const announcer = document.getElementById('a11y-announcer');
    if (announcer) {
        announcer.textContent = '';
        setTimeout(() => { announcer.textContent = message; }, 50);
    }
}

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function formatDeadline(deadlineStr, status) {
    if (!deadlineStr) return { dateStr: 'Belum ditentukan', timeStr: 'Segera', isOverdue: false };

    const deadline = new Date(deadlineStr);
    const now = new Date();
    const diffMs = deadline - now;
    const diffHours = diffMs / (1000 * 60 * 60);

    const options = { month: 'short', day: 'numeric', year: 'numeric' };
    const dateStr = deadline.toLocaleDateString('en-US', options);

    if (status === 'done') {
        return { dateStr, timeStr: 'Selesai', isOverdue: false };
    }

    if (diffMs <= 0) {
        return { dateStr, timeStr: 'Tenggat Lewat', isOverdue: true };
    } else if (diffHours < 24) {
        const h = Math.max(1, Math.floor(diffHours));
        return { dateStr, timeStr: `Sisa ${h} Jam`, isOverdue: true };
    } else {
        const d = Math.round(diffHours / 24);
        return { dateStr, timeStr: `${d} days`, isOverdue: false };
    }
}

// 6. INTERAKSI 3: PREFERENSI TEMA & LOCALSTORAGE
const AVAILABLE_THEMES = ['obsidian', 'midnight', 'slate', 'light'];

function initPreferences() {
    const savedTheme = localStorage.getItem(STORAGE_KEY_THEME) || 'obsidian';
    const savedDensity = localStorage.getItem(STORAGE_KEY_DENSITY) || 'comfortable';
    const savedViewMode = localStorage.getItem(STORAGE_KEY_VIEW_MODE) || 'kanban';
    const savedSound = localStorage.getItem(STORAGE_KEY_SOUND) !== 'false';

    applyTheme(savedTheme);
    applyDensity(savedDensity);

    const themeSelect = document.getElementById('select-theme');
    if (themeSelect) themeSelect.value = savedTheme;

    const densitySelect = document.getElementById('select-density');
    if (densitySelect) densitySelect.value = savedDensity;

    const soundCheck = document.getElementById('check-enable-sound');
    if (soundCheck) soundCheck.checked = savedSound;

    if (savedViewMode === 'list') {
        activateListView();
    }
}

function applyTheme(themeName) {
    if (!AVAILABLE_THEMES.includes(themeName)) themeName = 'obsidian';
    document.documentElement.setAttribute('data-theme', themeName);
    localStorage.setItem(STORAGE_KEY_THEME, themeName);
}

function cycleTheme() {
    const current = localStorage.getItem(STORAGE_KEY_THEME) || 'obsidian';
    const currentIndex = AVAILABLE_THEMES.indexOf(current);
    const nextTheme = AVAILABLE_THEMES[(currentIndex + 1) % AVAILABLE_THEMES.length];
    applyTheme(nextTheme);

    const themeSelect = document.getElementById('select-theme');
    if (themeSelect) themeSelect.value = nextTheme;

    announce(`Tema diubah menjadi ${nextTheme}`);
}

function applyDensity(densityName) {
    const valid = densityName === 'compact' ? 'compact' : 'comfortable';
    document.documentElement.setAttribute('data-density', valid);
    localStorage.setItem(STORAGE_KEY_DENSITY, valid);
}

// 7. INTERAKSI 1: FILTER, PENCARIAN & SORTING
function getProcessedTasks() {
    const filtered = filterTasks(tasks, {
        course: filterState.course,
        search: filterState.search
    });

    const [sortField, sortOrder] = filterState.sort.split('-');
    return sortTasks(filtered, sortField, sortOrder);
}

function initFilterAndSortControls() {
    const searchInput = document.getElementById('search-task-input');
    const filterSelect = document.getElementById('filter-course-select');
    const sortSelect = document.getElementById('sort-task-select');
    const filterBtn = document.getElementById('btn-filter-toggle');
    const filterPopover = document.getElementById('filter-popover');
    const filterCurrentLabel = document.getElementById('filter-current-label');

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            filterState.search = e.target.value.toLowerCase().trim();
            renderBoard();
            renderListView();
        });
    }

    if (filterBtn && filterPopover) {
        filterBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = filterPopover.classList.toggle('show');
            filterBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        document.addEventListener('click', (e) => {
            if (!filterPopover.contains(e.target) && e.target !== filterBtn) {
                filterPopover.classList.remove('show');
                filterBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

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

    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            filterState.sort = sortSelect.value;
            renderBoard();
            renderListView();
            announce(`Urutan tugas diubah berdasarkan ${sortSelect.options[sortSelect.selectedIndex].text}`);
        });
    }
}

function updateFilterOptions() {
    const filterSelect = document.getElementById('filter-course-select');
    if (!filterSelect) return;

    const current = filterSelect.value || 'all';
    const courses = getUniqueCourses(tasks);

    filterSelect.innerHTML = '<option value="all">Semua Kategori</option>';
    courses.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c;
        opt.textContent = c;
        if (c === current) opt.selected = true;
        filterSelect.appendChild(opt);
    });

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
let activeDetailTaskId = null;

function openDetailModal(taskId) {
    const task = findTaskById(tasks, taskId);
    if (!task || !detailModal) return;

    activeDetailTaskId = taskId;
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

    if (titleEl) titleEl.textContent = task.title;
    if (courseEl) courseEl.textContent = task.course;
    if (dateEl) dateEl.textContent = timeInfo.dateStr;
    if (countEl) {
        countEl.textContent = timeInfo.timeStr;
        countEl.style.color = timeInfo.isOverdue && task.status !== 'done' ? '#f87171' : 'var(--accent-yellow)';
    }

    if (prioEl) {
        const p = (task.priority || 'medium').toLowerCase();
        prioEl.innerHTML = `<span class="priority-badge priority-${p}">${ICONS.flag} <span>${p.toUpperCase()}</span></span>`;
    }

    if (statusEl) {
        const stLabels = { todo: 'Planned', inprogress: 'In progress', done: 'Completed', overdue: 'Overdue / < 24h' };
        statusEl.innerHTML = `<span class="col-count" style="font-size: 0.8rem; padding: 0.25rem 0.65rem;">${stLabels[task.status] || task.status}</span>`;
    }

    if (lmsEl) {
        if (task.lms_url) {
            lmsEl.innerHTML = `<a href="${escapeHtml(task.lms_url)}" target="_blank" rel="noopener noreferrer" class="btn-lms-link">${ICONS.link} <span>${escapeHtml(task.lms_label || 'Buka Link')}</span></a>`;
        } else {
            lmsEl.innerHTML = '<span style="color: var(--text-muted); font-size: 0.8rem;">Tidak ada tautan</span>';
        }
    }

    if (notesEl) {
        notesEl.textContent = task.instructions || 'Tidak ada catatan atau instruksi tambahan pada tugas ini.';
    }

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

        actionsContainer.querySelectorAll('.btn-status-pill').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetStatus = btn.getAttribute('data-status');
                updateTaskStatus(taskId, targetStatus);
                openDetailModal(taskId);
            });
        });
    }

    if (typeof detailModal.showModal === 'function') {
        detailModal.showModal();
    } else {
        detailModal.setAttribute('open', '');
    }

    announce(`Membuka rincian tugas "${task.title}"`);
}

function closeDetailModal() {
    if (!detailModal) return;
    if (typeof detailModal.close === 'function') detailModal.close();
    else detailModal.removeAttribute('open');
    activeDetailTaskId = null;
}

function updateTaskStatus(taskId, newStatus) {
    const task = findTaskById(tasks, taskId);
    if (!task) return;
    task.status = newStatus;
    saveTasks();
    announce(`Status tugas "${task.title}" diperbarui ke ${newStatus}`);
}

// Modal Ringkasan Cepat
const summaryModal = document.getElementById('summary-modal');
const quickSummaryBtn = document.getElementById('btn-quick-summary');
const closeSummaryBtn = document.getElementById('btn-close-summary-modal');
const closeSummaryBottomBtn = document.getElementById('btn-close-summary-bottom');

function openSummaryModal() {
    if (!summaryModal) return;

    const stats = calculateTaskStatistics(tasks);
    const courseGroups = groupTasksByCourse(tasks);

    const sumTotal = document.getElementById('sum-total-tasks');
    const sumDone = document.getElementById('sum-completed-tasks');
    const sumRate = document.getElementById('sum-rate-tasks');
    const breakdownContainer = document.getElementById('summary-course-breakdown');

    if (sumTotal) sumTotal.textContent = stats.total;
    if (sumDone) sumDone.textContent = stats.completedCount;
    if (sumRate) sumRate.textContent = `${stats.completionRate}%`;

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

function closeSummaryModal() {
    if (!summaryModal) return;
    if (typeof summaryModal.close === 'function') summaryModal.close();
    else summaryModal.removeAttribute('open');
}

// 9. RENDER KANBAN BOARD
const dropzones = {
    todo: document.getElementById('dropzone-todo'),
    inprogress: document.getElementById('dropzone-inprogress'),
    done: document.getElementById('dropzone-done'),
    overdue: document.getElementById('dropzone-overdue')
};

const counters = {
    todo: document.getElementById('count-todo'),
    inprogress: document.getElementById('count-inprogress'),
    done: document.getElementById('count-done'),
    overdue: document.getElementById('count-overdue')
};

function renderBoard() {
    Object.values(dropzones).forEach(z => { if (z) z.innerHTML = ''; });

    const sortedTasks = getProcessedTasks();
    const counts = { todo: 0, inprogress: 0, done: 0, overdue: 0 };

    sortedTasks.forEach(task => {
        const targetStatus = task.status || 'todo';
        counts[targetStatus] = (counts[targetStatus] || 0) + 1;

        const timeInfo = formatDeadline(task.deadline, task.status);

        const card = document.createElement('article');
        card.className = 'task-card';
        card.id = task.id;
        card.draggable = true;
        card.setAttribute('role', 'listitem');
        card.setAttribute('aria-labelledby', `title-${task.id}`);
        card.setAttribute('tabindex', '0');

        if (timeInfo.isOverdue && task.status !== 'done') {
            card.style.borderLeft = '3px solid #f97316';
        }

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

        const titleHeader = card.querySelector('.task-card-title');
        if (titleHeader) {
            titleHeader.addEventListener('click', () => openDetailModal(task.id));
        }

        setupDragAndDrop(card, task.id);

        if (dropzones[targetStatus]) {
            dropzones[targetStatus].appendChild(card);
        }
    });

    if (counters.todo) counters.todo.textContent = counts.todo;
    if (counters.inprogress) counters.inprogress.textContent = counts.inprogress;
    if (counters.done) counters.done.textContent = counts.done;
    if (counters.overdue) counters.overdue.textContent = counts.overdue;

    Object.entries(dropzones).forEach(([key, zone]) => {
        if (zone && zone.children.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'empty-column-notice';
            empty.textContent = 'Belum ada tugas di kolom ini.';
            zone.appendChild(empty);
        }
    });

    attachCardActionListeners();
}

// 10. RENDER LIST VIEW
function renderListView() {
    const rowsContainer = document.getElementById('list-view-rows');
    if (!rowsContainer) return;

    const sortedTasks = getProcessedTasks();

    if (sortedTasks.length === 0) {
        rowsContainer.innerHTML = '<div class="empty-column-notice">Tidak ada tugas yang sesuai dengan pencarian atau filter.</div>';
        return;
    }

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

window.openDetailDirect = function(id) {
    openDetailModal(id);
};

// 11. DRAG AND DROP NATIVE HTML5
let draggedTaskId = null;

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

function initDropzones() {
    Object.entries(dropzones).forEach(([statusKey, zone]) => {
        if (!zone) return;

        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            zone.classList.add('dragover');
        });

        zone.addEventListener('dragleave', (e) => {
            if (!zone.contains(e.relatedTarget)) {
                zone.classList.remove('dragover');
            }
        });

        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('dragover');
            const id = e.dataTransfer.getData('text/plain') || draggedTaskId;
            if (!id) return;

            const taskIndex = findTaskIndex(tasks, id);
            if (taskIndex > -1 && tasks[taskIndex].status !== statusKey) {
                tasks[taskIndex].status = statusKey;
                saveTasks();
                announce(`Tugas "${tasks[taskIndex].title}" dipindahkan ke kolom ${statusKey}`);
            }
        });
    });
}

function attachCardActionListeners() {
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

    document.querySelectorAll('.btn-detail').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            openDetailModal(id);
        });
    });

    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            editTask(id);
        });
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            deleteTask(id);
        });
    });
}

// 12. FORM CRUD TUGAS & VALIDASI CLIENT-SIDE

// --- 12a. HELPERS VALIDASI UI ---

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

function showFieldError(fieldKey, message) {
    const map = FIELD_MAP[fieldKey];
    if (!map) return;
    const input = document.getElementById(map.inputId);
    const errorEl = document.getElementById(map.errorId);
    if (input) input.setAttribute('aria-invalid', 'true');
    if (errorEl) {
        errorEl.innerHTML = `${ICONS.alertTriangle} <span>${escapeHtml(message)}</span>`;
        errorEl.classList.add('show');
    }
}

function clearFieldError(fieldKey) {
    const map = FIELD_MAP[fieldKey];
    if (!map) return;
    const input = document.getElementById(map.inputId);
    const errorEl = document.getElementById(map.errorId);
    if (input) input.setAttribute('aria-invalid', 'false');
    if (errorEl) {
        errorEl.innerHTML = '';
        errorEl.classList.remove('show');
    }
}

function clearAllFieldErrors() {
    Object.keys(FIELD_MAP).forEach(key => clearFieldError(key));
}

function showErrorSummary(errors) {
    const summaryEl = document.getElementById('form-error-summary');
    const listEl = document.getElementById('error-summary-list');
    if (!summaryEl || !listEl) return;

    listEl.innerHTML = '';
    Object.entries(errors).forEach(([key, message]) => {
        const map = FIELD_MAP[key];
        const li = document.createElement('li');
        if (map) {
            const a = document.createElement('a');
            a.href = `#${map.inputId}`;
            a.textContent = message;
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

    summaryEl.removeAttribute('hidden');
    summaryEl.focus();
}

function hideErrorSummary() {
    const summaryEl = document.getElementById('form-error-summary');
    if (summaryEl) summaryEl.setAttribute('hidden', '');
}

function collectFormData(isNew) {
    const editId = document.getElementById('task-id').value;
    return {
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

function updateNotesCounter(value) {
    const counter = document.getElementById('notes-char-counter');
    if (!counter) return;
    const len = (value || '').length;
    counter.textContent = `${len} / 500`;
    counter.className = 'char-counter';
    if (len >= 450) counter.classList.add('counter-warning');
    if (len >= 500) counter.classList.add('counter-limit');
}

// --- 12b. FORM STATE ---
const taskForm = document.getElementById('task-form');
const resetBtn = document.getElementById('btn-reset-form');
const taskModal = document.getElementById('task-modal');
const closeTaskModalBtn = document.getElementById('btn-close-task-modal');
let lastActiveTrigger = null;

function openTaskModal(task = null) {
    if (!taskModal) return;

    clearAllFieldErrors();
    hideErrorSummary();

    if (task) {
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

        document.getElementById('form-title').textContent = 'Perbarui Kartu Tugas';
        document.getElementById('save-btn-label').textContent = 'Perbarui Kartu Tugas';
        announce(`Formulir dibuka untuk menyunting tugas "${task.title}"`);
    } else {
        resetTaskForm();
        document.getElementById('form-title').textContent = 'Tambah Tugas Kuliah Baru';
        document.getElementById('save-btn-label').textContent = 'Simpan Kartu Tugas';
        announce('Formulir tambah tugas baru dibuka');
    }

    if (typeof taskModal.showModal === 'function') {
        taskModal.showModal();
    } else {
        taskModal.setAttribute('open', '');
    }

    const triggerBtn = document.getElementById('btn-new-task-trigger');
    if (triggerBtn) triggerBtn.setAttribute('aria-expanded', 'true');
    const navFormBtn = document.getElementById('nav-btn-open-form');
    if (navFormBtn) navFormBtn.setAttribute('aria-expanded', 'true');

    setTimeout(() => {
        const inputJudul = document.getElementById('input-judul');
        if (inputJudul) inputJudul.focus();
    }, 60);
}

function closeTaskModal() {
    if (!taskModal) return;

    if (typeof taskModal.close === 'function') {
        taskModal.close();
    } else {
        taskModal.removeAttribute('open');
    }

    const allTriggers = [
        document.getElementById('btn-new-task-trigger'),
        document.getElementById('nav-btn-open-form')
    ];
    allTriggers.forEach(btn => { if (btn) btn.setAttribute('aria-expanded', 'false'); });

    if (lastActiveTrigger) {
        lastActiveTrigger.focus();
        lastActiveTrigger = null;
    }
}

function editTask(id) {
    const task = findTaskById(tasks, id);
    if (!task) return;
    lastActiveTrigger = document.activeElement;
    switchView('view-tasks');
    openTaskModal(task);
}

function deleteTask(id) {
    const task = findTaskById(tasks, id);
    if (!task) return;

    if (confirm(`Apakah Anda yakin ingin menghapus tugas "${task.title}"?`)) {
        tasks = tasks.filter(t => t.id !== id);
        saveTasks();
        announce(`Tugas "${task.title}" telah dihapus`);
    }
}

function resetTaskForm() {
    if (!taskForm) return;
    taskForm.reset();
    document.getElementById('task-id').value = '';
    document.getElementById('save-btn-label').textContent = 'Simpan Kartu Tugas';
    clearAllFieldErrors();
    hideErrorSummary();
    updateNotesCounter('');
}

// --- 12c. LIVE VALIDATION PER-FIELD (BLUR) ---
function initLiveFieldValidation() {
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

    Object.entries(blurValidators).forEach(([inputId, validator]) => {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('blur', () => {
            const error = validator(input.value);
            const fieldKey = fieldKeys[inputId];
            if (error) {
                showFieldError(fieldKey, error);
            } else {
                clearFieldError(fieldKey);
            }
        });
        input.addEventListener('input', () => {
            const error = validator(input.value);
            const fieldKey = fieldKeys[inputId];
            if (!error) clearFieldError(fieldKey);
        });
    });

    // Character counter
    const notesInput = document.getElementById('input-notes');
    if (notesInput) {
        notesInput.addEventListener('input', () => updateNotesCounter(notesInput.value));
    }
}

// --- 12d. DEADLINE PRESET PILLS ---
function initDeadlinePresets() {
    document.querySelectorAll('.btn-preset-pill').forEach(btn => {
        btn.addEventListener('click', () => {
            const days = parseInt(btn.getAttribute('data-days'), 10);
            if (!days) return;
            const now = new Date();
            now.setDate(now.getDate() + days);
            now.setHours(23, 59, 0, 0);
            const pad = (n) => String(n).padStart(2, '0');
            const localStr = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
            const deadlineInput = document.getElementById('input-deadline');
            if (deadlineInput) {
                deadlineInput.value = localStr;
                clearFieldError('deadline');
                announce(`Tenggat waktu disetel ke ${days} hari ke depan`);
            }
        });
    });
}



// 13. DASHBOARD & KALENDER
const navTabButtons = document.querySelectorAll('.nav-tab-btn');

function switchView(targetViewId) {
    navTabButtons.forEach(btn => {
        if (btn.getAttribute('data-target') === targetViewId) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    document.querySelectorAll('.app-view-section').forEach(sec => {
        if (sec.id === targetViewId) {
            sec.classList.add('active-view');
        } else {
            sec.classList.remove('active-view');
        }
    });

    if (targetViewId === 'view-dashboard') updateDashboard();
    if (targetViewId === 'view-calendar') renderCalendar();
}

function updateDashboard() {
    const stats = calculateTaskStatistics(tasks);
    const topUrgentList = getTopUrgentTasks(tasks, 4);

    const statTotal = document.getElementById('stat-total-tasks');
    const statPlanned = document.getElementById('stat-planned-tasks');
    const statProgress = document.getElementById('stat-progress-tasks');
    const statOverdue = document.getElementById('stat-overdue-tasks');

    if (statTotal) statTotal.textContent = stats.total;
    if (statPlanned) statPlanned.textContent = stats.byStatus.todo;
    if (statProgress) statProgress.textContent = stats.byStatus.inprogress;
    if (statOverdue) statOverdue.textContent = stats.overdueCount;

    const urgentContainer = document.getElementById('urgent-tasks-list');
    if (urgentContainer) {
        if (topUrgentList.length === 0) {
            urgentContainer.innerHTML = '<p style="color: var(--text-muted); font-size: 0.85rem;">Tidak ada tugas mendekati batas waktu (< 24 jam). Kondisi aman.</p>';
        } else {
            urgentContainer.innerHTML = topUrgentList.map(t => {
                const time = formatDeadline(t.deadline, t.status);
                return `
                    <div style="background: rgba(0,0,0,0.25); padding: 0.85rem 1rem; border-radius: 8px; border-left: 3px solid #f87171; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="color: #ffffff; font-size: 0.875rem;">${escapeHtml(t.title)}</strong>
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

let currentCalendarDate = new Date();

function renderCalendar() {
    const grid = document.getElementById('calendar-grid-cells');
    const titleMonth = document.getElementById('calendar-month-year');
    if (!grid) return;

    const year = currentCalendarDate.getFullYear();
    const month = currentCalendarDate.getMonth();

    const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    if (titleMonth) titleMonth.textContent = `${monthNames[month]} ${year}`;

    grid.innerHTML = '';

    const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    days.forEach(d => {
        const dh = document.createElement('div');
        dh.className = 'cal-day-name';
        dh.textContent = d;
        grid.appendChild(dh);
    });

    const firstDayIndex = new Date(year, month, 1).getDay();
    const lastDayDate = new Date(year, month + 1, 0).getDate();

    for (let i = 0; i < firstDayIndex; i++) {
        const cell = document.createElement('div');
        cell.className = 'cal-cell';
        cell.style.opacity = '0.3';
        grid.appendChild(cell);
    }

    const today = new Date();
    const tasksByDate = groupTasksByDate(tasks);

    for (let day = 1; day <= lastDayDate; day++) {
        const cell = document.createElement('div');
        cell.className = 'cal-cell';

        const isToday = (today.getDate() === day && today.getMonth() === month && today.getFullYear() === year);
        if (isToday) cell.classList.add('today');

        const cellDateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const dayTasks = tasksByDate[cellDateStr] || [];

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

// 14. INISIALISASI APLIKASI
function activateListView() {
    const tabKanban = document.getElementById('tab-view-kanban');
    const tabList = document.getElementById('tab-view-list');
    const kanbanContainer = document.getElementById('kanban-view-container');
    const listContainer = document.getElementById('list-view-container');

    if (tabList && tabKanban) {
        tabList.classList.add('active');
        tabList.setAttribute('aria-selected', 'true');
        tabKanban.classList.remove('active');
        tabKanban.setAttribute('aria-selected', 'false');

        if (kanbanContainer) kanbanContainer.style.display = 'none';
        if (listContainer) listContainer.style.display = 'block';
        localStorage.setItem(STORAGE_KEY_VIEW_MODE, 'list');
        renderListView();
    }
}

function activateKanbanView() {
    const tabKanban = document.getElementById('tab-view-kanban');
    const tabList = document.getElementById('tab-view-list');
    const kanbanContainer = document.getElementById('kanban-view-container');
    const listContainer = document.getElementById('list-view-container');

    if (tabKanban && tabList) {
        tabKanban.classList.add('active');
        tabKanban.setAttribute('aria-selected', 'true');
        tabList.classList.remove('active');
        tabList.setAttribute('aria-selected', 'false');

        if (kanbanContainer) kanbanContainer.style.display = 'block';
        if (listContainer) listContainer.style.display = 'none';
        localStorage.setItem(STORAGE_KEY_VIEW_MODE, 'kanban');
    }
}

function initApp() {
    initPreferences();
    initFilterAndSortControls();
    initDropzones();
    initLiveFieldValidation();
    initDeadlinePresets();

    updateFilterOptions();
    renderBoard();
    renderListView();
    updateDashboard();
    renderCalendar();

    // Theme Toggle Header
    const themeToggleBtn = document.getElementById('btn-theme-toggle');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', cycleTheme);
    }

    // Settings Modal Theme & Density Selects
    const themeSelect = document.getElementById('select-theme');
    if (themeSelect) {
        themeSelect.addEventListener('change', (e) => {
            applyTheme(e.target.value);
            announce(`Tema diubah ke ${e.target.value}`);
        });
    }

    const densitySelect = document.getElementById('select-density');
    if (densitySelect) {
        densitySelect.addEventListener('change', (e) => {
            applyDensity(e.target.value);
            announce(`Kerapatan tampilan diubah ke ${e.target.value}`);
        });
    }

    const soundCheck = document.getElementById('check-enable-sound');
    if (soundCheck) {
        soundCheck.addEventListener('change', (e) => {
            localStorage.setItem(STORAGE_KEY_SOUND, e.target.checked ? 'true' : 'false');
        });
    }

    // View Switcher Buttons
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

    // Nav Tab Buttons
    navTabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-target');
            if (target) switchView(target);
        });
    });

    // Top New Task Button
    const topNewTaskBtn = document.getElementById('btn-new-task-trigger');
    if (topNewTaskBtn) {
        topNewTaskBtn.addEventListener('click', (e) => {
            e.preventDefault();
            lastActiveTrigger = topNewTaskBtn;
            switchView('view-tasks');
            openTaskModal(null);
        });
    }

    // Nav CTA "Tambah Tugas" button
    const navFormBtn = document.getElementById('nav-btn-open-form');
    if (navFormBtn) {
        navFormBtn.addEventListener('click', (e) => {
            e.preventDefault();
            lastActiveTrigger = navFormBtn;
            switchView('view-tasks');
            openTaskModal(null);
        });
    }

    // Modal Close Triggers
    if (closeTaskModalBtn) closeTaskModalBtn.addEventListener('click', closeTaskModal);
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            resetTaskForm();
            closeTaskModal();
        });
    }

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

    // Ctrl+Enter shortcut to submit form
    if (taskForm) {
        taskForm.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                taskForm.requestSubmit();
            }
        });
    }

    // Form Submit (uses validateTaskFormData for inline errors)
    if (taskForm) {
        taskForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const rawData = collectFormData();
            const editId = document.getElementById('task-id').value;
            const isNew = !editId;

            clearAllFieldErrors();
            hideErrorSummary();

            const result = validateTaskFormData(rawData, { isNew });

            if (!result.isValid) {
                // Show per-field errors
                Object.entries(result.errors).forEach(([key, msg]) => showFieldError(key, msg));
                // Show summary box and focus it
                showErrorSummary(result.errors);
                announce('Formulir mengandung kesalahan. Mohon periksa kembali isian Anda.');
                return;
            }

            try {
                const validatedTask = validateTask(result.sanitizedData);

                if (!isNew) {
                    const idx = findTaskIndex(tasks, editId);
                    if (idx > -1) {
                        tasks[idx] = validatedTask;
                        announce(`Tugas "${validatedTask.title}" berhasil diperbarui`);
                    }
                } else {
                    tasks.push(validatedTask);
                    announce(`Tugas baru "${validatedTask.title}" berhasil ditambahkan`);
                }

                saveTasks();
                resetTaskForm();
                closeTaskModal();
            } catch (err) {
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

    // Paste Clipboard
    const pasteBtn = document.getElementById('btn-paste-clipboard');
    if (pasteBtn) {
        pasteBtn.addEventListener('click', async () => {
            try {
                if (navigator.clipboard && navigator.clipboard.readText) {
                    const text = await navigator.clipboard.readText();
                    if (text) {
                        const textarea = document.getElementById('input-notes');
                        textarea.value = (textarea.value ? textarea.value + '\n\n' : '') + text;
                        announce('Teks berhasil ditempel dari clipboard');
                        return;
                    }
                }
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

    // Detail Modal Listeners
    if (closeDetailModalBtn) closeDetailModalBtn.addEventListener('click', closeDetailModal);
    if (closeDetailSecondaryBtn) closeDetailSecondaryBtn.addEventListener('click', closeDetailModal);
    if (detailModal) {
        detailModal.addEventListener('click', (e) => {
            if (e.target === detailModal) closeDetailModal();
        });
    }

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

    if (editFromDetailBtn) {
        editFromDetailBtn.addEventListener('click', () => {
            const id = activeDetailTaskId;
            closeDetailModal();
            if (id) editTask(id);
        });
    }

    // Summary Modal Listeners
    if (quickSummaryBtn) quickSummaryBtn.addEventListener('click', openSummaryModal);
    if (closeSummaryBtn) closeSummaryBtn.addEventListener('click', closeSummaryModal);
    if (closeSummaryBottomBtn) closeSummaryBottomBtn.addEventListener('click', closeSummaryModal);
    if (summaryModal) {
        summaryModal.addEventListener('click', (e) => {
            if (e.target === summaryModal) closeSummaryModal();
        });
    }

    // Calendar Navigation Buttons
    const prevMonthBtn = document.getElementById('btn-prev-month');
    const nextMonthBtn = document.getElementById('btn-next-month');
    const todayMonthBtn = document.getElementById('btn-today-month');

    if (prevMonthBtn) {
        prevMonthBtn.addEventListener('click', () => {
            currentCalendarDate.setMonth(currentCalendarDate.getMonth() - 1);
            renderCalendar();
        });
    }
    if (nextMonthBtn) {
        nextMonthBtn.addEventListener('click', () => {
            currentCalendarDate.setMonth(currentCalendarDate.getMonth() + 1);
            renderCalendar();
        });
    }
    if (todayMonthBtn) {
        todayMonthBtn.addEventListener('click', () => {
            currentCalendarDate = new Date();
            renderCalendar();
        });
    }

    // Settings & Profile Dialog
    const settingsModal = document.getElementById('settings-modal');
    const openSettingsBtn = document.getElementById('btn-open-settings');
    const openProfileBtn = document.getElementById('btn-open-profile');
    const closeSettingsBtn = document.getElementById('btn-close-settings');
    const googleSsoBtn = document.getElementById('btn-google-sso-modal');
    const profileUserName = document.getElementById('profile-user-name');
    const notifBadge = document.getElementById('notif-badge');

    function openSettingsModal() {
        if (settingsModal) {
            if (typeof settingsModal.showModal === 'function') settingsModal.showModal();
            else settingsModal.setAttribute('open', '');
        }
    }

    function closeSettingsModal() {
        if (settingsModal) {
            if (typeof settingsModal.close === 'function') settingsModal.close();
            else settingsModal.removeAttribute('open');
        }
    }

    if (openSettingsBtn) openSettingsBtn.addEventListener('click', openSettingsModal);
    if (openProfileBtn) openProfileBtn.addEventListener('click', openSettingsModal);
    if (closeSettingsBtn) closeSettingsBtn.addEventListener('click', closeSettingsModal);

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

    // Web Notification Opt-In
    const notifDashboardBtn = document.getElementById('btn-dashboard-enable-notif');
    if (notifDashboardBtn) {
        notifDashboardBtn.addEventListener('click', async () => {
            if (!('Notification' in window)) {
                alert('Browser Anda belum mendukung Web Notification API.');
                return;
            }
            try {
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

    if (window.Notification && Notification.permission === 'granted') {
        if (notifBadge) notifBadge.classList.add('active');
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}
