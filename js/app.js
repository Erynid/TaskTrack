/**
 * TaskTrack - Frontend Interactive Application Logic
 * Sleek Dark UI Edition (Zero Raw Emojis & Strict Stroke Icon Discipline)
 *
 * Fitur:
 * - Papan Kanban 4 Kolom (Planned, In progress, Completed, Overdue / < 24h)
 * - Drag & Drop Native (HTML5 API) antar 4 dropzone
 * - CRUD Manajemen Tugas Lengkap (Tambah, Tampil, Edit, Hapus)
 * - Auto-sorting Deadline (Tenggat terdekat di urutan atas)
 * - Switcher Tampilan: Kanban view vs List view
 * - Navigasi Tab Utama: Dashboard, Tasks, Calendar
 * - Kalender Interaktif dengan Penanda Deadline Tugas
 * - Salin Teks Instruksi dari Clipboard
 * - Filter Kategori Mata Kuliah & Live Search
 * - Web Push Notification & Modal Pengaturan / Google SSO
 */

import taskProcessor, {
    TaskDataError,
    validateTask,
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

    // SVG Icon Templates
    const ICONS = {
        calendar: '<svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>',
        clock: '<svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
        flag: '<svg class="icon-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>',
        check: '<svg class="icon-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>',
        link: '<svg class="icon-svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>',
        arrowRight: '<svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>',
        arrowLeft: '<svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
        rotate: '<svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>',
        edit: '<svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>',
        delete: '<svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>'
    };

    // Data Default Sesuai Referensi
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

    let tasks = loadTasks();

    function loadTasks() {
        const stored = localStorage.getItem(STORAGE_KEY_TASKS);
        if (stored) {
            // Gunakan parseTasksFromJSON dari taskProcessor dengan validasi skema dan try...catch
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

    // -------------------------------------------------------------------------
    // Aksesibilitas Live Announcer
    // -------------------------------------------------------------------------
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

    // -------------------------------------------------------------------------
    // Format Waktu & Urgensi (Tanpa Emoji)
    // -------------------------------------------------------------------------
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

    // -------------------------------------------------------------------------
    // Render 4 Kolom Kanban
    // -------------------------------------------------------------------------
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
        const filterCourse = document.getElementById('filter-course-select')?.value || 'all';
        const searchQuery = (document.getElementById('search-task-input')?.value || '').toLowerCase().trim();

        // Bersihkan seluruh dropzone
        Object.values(dropzones).forEach(z => { if (z) z.innerHTML = ''; });

        // Pengolahan data: filter (.filter) dan urutkan (.sort) menggunakan modul taskProcessor
        const filtered = filterTasks(tasks, {
            course: filterCourse,
            search: searchQuery
        });
        const sorted = sortTasks(filtered, 'deadline', 'asc');

        const counts = { todo: 0, inprogress: 0, done: 0, overdue: 0 };

        sorted.forEach(task => {
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

            // Priority label & SVG
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

            // LMS Link Button
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

            // Move Actions
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
                <h3 class="task-card-title" id="title-${task.id}">${escapeHtml(task.title)}</h3>
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
                        <button type="button" class="btn-action btn-edit" data-id="${task.id}" title="Edit Tugas" aria-label="Edit tugas">${ICONS.edit}</button>
                        <button type="button" class="btn-action btn-delete" data-id="${task.id}" title="Hapus Tugas" aria-label="Hapus tugas">${ICONS.delete}</button>
                    </div>
                </div>
            `;

            setupDragAndDrop(card, task.id);

            if (dropzones[targetStatus]) {
                dropzones[targetStatus].appendChild(card);
            }
        });

        // Update Counter
        if (counters.todo) counters.todo.textContent = counts.todo;
        if (counters.inprogress) counters.inprogress.textContent = counts.inprogress;
        if (counters.done) counters.done.textContent = counts.done;
        if (counters.overdue) counters.overdue.textContent = counts.overdue;

        // Empty state notice
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

    // -------------------------------------------------------------------------
    // Render List View (Bebas Emoji)
    // -------------------------------------------------------------------------
    function renderListView() {
        const rowsContainer = document.getElementById('list-view-rows');
        if (!rowsContainer) return;

        const filterCourse = document.getElementById('filter-course-select')?.value || 'all';
        const searchQuery = (document.getElementById('search-task-input')?.value || '').toLowerCase().trim();

        // Pengolahan data: filter (.filter) dan urutkan (.sort) menggunakan modul taskProcessor
        const filtered = filterTasks(tasks, {
            course: filterCourse,
            search: searchQuery
        });
        const sorted = sortTasks(filtered, 'deadline', 'asc');

        if (sorted.length === 0) {
            rowsContainer.innerHTML = '<div class="empty-column-notice">Tidak ada tugas yang sesuai dengan pencarian atau filter.</div>';
            return;
        }

        rowsContainer.innerHTML = sorted.map(t => {
            const timeInfo = formatDeadline(t.deadline, t.status);
            const lmsPill = t.lms_url ? 
                `<a href="${escapeHtml(t.lms_url)}" target="_blank" class="btn-lms-link">${ICONS.link} <span>${escapeHtml(t.lms_label || 'LMS')}</span></a>` : 
                `<span style="color: var(--text-muted); font-size: 0.75rem;">-</span>`;

            return `
                <div class="task-list-row">
                    <div>
                        <div class="list-title">${escapeHtml(t.title)}</div>
                        <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">${escapeHtml(t.status)}</span>
                    </div>
                    <div style="font-size: 0.85rem; color: #a3b8aa;">${escapeHtml(t.course)}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">${timeInfo.dateStr} (${timeInfo.timeStr})</div>
                    <div><span class="priority-badge priority-${t.priority || 'med'}">${ICONS.flag} <span>${escapeHtml(t.priority || 'Medium')}</span></span></div>
                    <div>${lmsPill}</div>
                    <div style="text-align: right; display: flex; justify-content: flex-end; gap: 0.4rem;">
                        <button type="button" class="btn-action btn-edit" data-id="${t.id}" title="Edit">${ICONS.edit}</button>
                        <button type="button" class="btn-action btn-delete" data-id="${t.id}" title="Hapus">${ICONS.delete}</button>
                    </div>
                </div>
            `;
        }).join('');

        rowsContainer.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', () => editTask(btn.getAttribute('data-id')));
        });
        rowsContainer.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', () => deleteTask(btn.getAttribute('data-id')));
        });
    }

    // -------------------------------------------------------------------------
    // Drag & Drop HTML5 API (4 Kolom)
    // -------------------------------------------------------------------------
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

    // -------------------------------------------------------------------------
    // Action Listeners (Move, Edit, Delete)
    // -------------------------------------------------------------------------
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

    // -------------------------------------------------------------------------
    // Form Input & CRUD Submit (Task Modal Dialog)
    // -------------------------------------------------------------------------
    const taskForm = document.getElementById('task-form');
    const resetBtn = document.getElementById('btn-reset-form');
    const taskModal = document.getElementById('task-modal');
    const closeTaskModalBtn = document.getElementById('btn-close-task-modal');
    let lastActiveTrigger = null;

    function openTaskModal(task = null) {
        if (!taskModal) return;

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

        const triggerBtn = document.getElementById('btn-new-task-trigger');
        if (triggerBtn) {
            triggerBtn.setAttribute('aria-expanded', 'false');
            if (lastActiveTrigger) {
                lastActiveTrigger.focus();
                lastActiveTrigger = null;
            } else {
                triggerBtn.focus();
            }
        }
    }

    if (closeTaskModalBtn) {
        closeTaskModalBtn.addEventListener('click', closeTaskModal);
    }

    if (taskModal) {
        taskModal.addEventListener('click', (e) => {
            if (e.target === taskModal) {
                closeTaskModal();
            }
        });
        taskModal.addEventListener('cancel', () => {
            const triggerBtn = document.getElementById('btn-new-task-trigger');
            if (triggerBtn) triggerBtn.setAttribute('aria-expanded', 'false');
        });
    }

    if (taskForm) {
        taskForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const editId = document.getElementById('task-id').value;
            const title = document.getElementById('input-judul').value.trim();
            const course = document.getElementById('input-matkul').value.trim();
            const deadline = document.getElementById('input-deadline').value;
            const priority = document.getElementById('input-priority').value;
            const status = document.getElementById('input-status').value;
            const lmsUrl = document.getElementById('input-url').value.trim();
            const lmsLabel = document.getElementById('input-url-label').value.trim();
            const notes = document.getElementById('input-notes').value.trim();

            // Gunakan validateTask dari modul taskProcessor dengan error handling try...catch
            try {
                const validatedTask = validateTask({
                    id: editId || ('task-' + Date.now()),
                    title,
                    course,
                    deadline,
                    priority: priority || 'medium',
                    status: status || 'todo',
                    lms_url: lmsUrl,
                    lms_label: lmsLabel || (lmsUrl ? 'LMS' : ''),
                    instructions: notes
                });

                if (editId) {
                    const idx = findTaskIndex(tasks, editId);
                    if (idx > -1) {
                        tasks[idx] = validatedTask;
                        announce(`Tugas "${title}" berhasil diperbarui`);
                    }
                } else {
                    tasks.push(validatedTask);
                    announce(`Tugas baru "${title}" berhasil ditambahkan`);
                }

                saveTasks();
                resetTaskForm();
                closeTaskModal();
            } catch (err) {
                if (err instanceof TaskDataError) {
                    alert(`Validasi Gagal: ${err.message}`);
                    announce(`Validasi gagal: ${err.message}`);
                } else {
                    console.error('Terjadi kesalahan saat memproses tugas:', err);
                    alert('Terjadi kesalahan yang tidak diharapkan.');
                }
            }
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            resetTaskForm();
            closeTaskModal();
        });
    }

    function resetTaskForm() {
        if (!taskForm) return;
        taskForm.reset();
        document.getElementById('task-id').value = '';
        document.getElementById('save-btn-label').textContent = 'Simpan Kartu Tugas';
    }

    // Quick Paste Clipboard
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
            } catch (err) {
                const promptText = prompt('Tempel instruksi e-learning di sini:');
                if (promptText) {
                    const textarea = document.getElementById('input-notes');
                    textarea.value = (textarea.value ? textarea.value + '\n\n' : '') + promptText;
                }
            }
        });
    }

    // Tombol "+ New task" di Top Toolbar
    const topNewTaskBtn = document.getElementById('btn-new-task-trigger');
    if (topNewTaskBtn) {
        topNewTaskBtn.addEventListener('click', (e) => {
            e.preventDefault();
            lastActiveTrigger = topNewTaskBtn;
            switchView('view-tasks');
            openTaskModal(null);
        });
    }

    // -------------------------------------------------------------------------
    // View Switcher (Kanban view vs List view)
    // -------------------------------------------------------------------------
    const tabKanban = document.getElementById('tab-view-kanban');
    const tabList = document.getElementById('tab-view-list');
    const kanbanContainer = document.getElementById('kanban-view-container');
    const listContainer = document.getElementById('list-view-container');

    if (tabKanban && tabList) {
        tabKanban.addEventListener('click', () => {
            tabKanban.classList.add('active');
            tabKanban.setAttribute('aria-selected', 'true');
            tabList.classList.remove('active');
            tabList.setAttribute('aria-selected', 'false');

            if (kanbanContainer) kanbanContainer.style.display = 'block';
            if (listContainer) listContainer.style.display = 'none';
            announce('Tampilan diubah ke Kanban view');
        });

        tabList.addEventListener('click', () => {
            tabList.classList.add('active');
            tabList.setAttribute('aria-selected', 'true');
            tabKanban.classList.remove('active');
            tabKanban.setAttribute('aria-selected', 'false');

            if (kanbanContainer) kanbanContainer.style.display = 'none';
            if (listContainer) listContainer.style.display = 'block';
            renderListView();
            announce('Tampilan diubah ke List view');
        });
    }

    // -------------------------------------------------------------------------
    // Navigasi Utama Tab Pill (Dashboard, Tasks, Calendar)
    // -------------------------------------------------------------------------
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

    navTabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-target');
            if (target) switchView(target);
        });
    });

    // -------------------------------------------------------------------------
    // Filter & Search Controls
    // -------------------------------------------------------------------------
    const filterBtn = document.getElementById('btn-filter-toggle');
    const filterPopover = document.getElementById('filter-popover');
    const filterSelect = document.getElementById('filter-course-select');
    const searchInput = document.getElementById('search-task-input');
    const filterCurrentLabel = document.getElementById('filter-current-label');

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
            const val = filterSelect.value;
            if (filterCurrentLabel) {
                filterCurrentLabel.textContent = val === 'all' ? 'Filter' : val;
            }
            renderBoard();
            renderListView();
            if (filterPopover) filterPopover.classList.remove('show');
            announce(`Filter diterapkan: ${val}`);
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            renderBoard();
            renderListView();
        });
    }

    function updateFilterOptions() {
        if (!filterSelect) return;
        const current = filterSelect.value || 'all';
        // Dapatkan daftar unik mata kuliah menggunakan modul taskProcessor (.map + filter + Set)
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

    // -------------------------------------------------------------------------
    // Dashboard View Statistics
    // -------------------------------------------------------------------------
    function updateDashboard() {
        // Kalkulasi statistik komprehensif menggunakan modul taskProcessor (.reduce)
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
                            <button type="button" class="btn-new-task" style="padding: 0.35rem 0.8rem; font-size: 0.75rem;" onclick="window.editTaskDirect('${t.id}')">
                                Buka
                            </button>
                        </div>
                    `;
                }).join('');
            }
        }
    }

    window.editTaskDirect = function(id) {
        editTask(id);
    };

    // -------------------------------------------------------------------------
    // Calendar View
    // -------------------------------------------------------------------------
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
        // Kelompokkan tugas berdasarkan tanggal menggunakan modul taskProcessor (.reduce)
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
                    <span class="cal-task-pill" style="background: ${color};" title="${escapeHtml(t.title)} (${escapeHtml(t.course)})">
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

    // -------------------------------------------------------------------------
    // Settings & User Profile Modal
    // -------------------------------------------------------------------------
    const settingsModal = document.getElementById('settings-modal');
    const openSettingsBtn = document.getElementById('btn-open-settings');
    const openProfileBtn = document.getElementById('btn-open-profile');
    const closeSettingsBtn = document.getElementById('btn-close-settings');
    const googleSsoBtn = document.getElementById('btn-google-sso-modal');
    const profileUserName = document.getElementById('profile-user-name');
    const notifBadge = document.getElementById('notif-badge');

    function openModal() {
        if (settingsModal) {
            if (typeof settingsModal.showModal === 'function') settingsModal.showModal();
            else settingsModal.setAttribute('open', '');
        }
    }

    function closeModal() {
        if (settingsModal) {
            if (typeof settingsModal.close === 'function') settingsModal.close();
            else settingsModal.removeAttribute('open');
        }
    }

    if (openSettingsBtn) openSettingsBtn.addEventListener('click', openModal);
    if (openProfileBtn) openProfileBtn.addEventListener('click', openModal);
    if (closeSettingsBtn) closeSettingsBtn.addEventListener('click', closeModal);

    if (googleSsoBtn) {
        googleSsoBtn.addEventListener('click', () => {
            const mock = { name: 'Muhammad Dzakir Dzakwan (SSO Aktif)', email: 'dzakir.dzakwan@mahasiswa.ac.id' };
            localStorage.setItem(STORAGE_KEY_AUTH, JSON.stringify(mock));
            if (profileUserName) profileUserName.textContent = mock.name;
            closeModal();
            alert('Berhasil masuk via Google SSO Mahasiswa!');
            announce('Berhasil masuk menggunakan Google SSO');
        });
    }

    // Web Push Notification Opt-In
    const notifDashboardBtn = document.getElementById('btn-dashboard-enable-notif');
    if (notifDashboardBtn) {
        notifDashboardBtn.addEventListener('click', requestNotificationPermission);
    }

    async function requestNotificationPermission() {
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
    }

    // -------------------------------------------------------------------------
    // Inisialisasi Aplikasi Saat Memuat Halaman
    // -------------------------------------------------------------------------
    function initApp() {
        updateFilterOptions();
        initDropzones();
        renderBoard();
        renderListView();
        updateDashboard();
        renderCalendar();

        if (window.Notification && Notification.permission === 'granted') {
            if (notifBadge) notifBadge.classList.add('active');
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initApp);
    } else {
        initApp();
    }

