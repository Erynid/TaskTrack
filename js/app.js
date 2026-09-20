/**
 * TaskTrack - Frontend Interactive Application Logic
 * Fitur:
 * - Autentikasi Pengguna (Google SSO, Email/Password, Remember Me)
 * - Papan Kanban 3 Kolom dengan Drag & Drop Native (HTML5 API)
 * - CRUD Manajemen Tugas (Tambah, Tampil, Edit, Hapus)
 * - Auto-sorting Deadline (Tenggat terdekat di urutan atas)
 * - Warna Indikator Kritis Waktu (Merah <24 jam, Kuning <3 hari, Hijau >3 hari, Abu-abu Selesai)
 * - Link Pengumpulan Tugas LMS dengan Pintasan Tab Baru
 * - Quick Input Teks Instruksi E-Learning & Paste Clipboard
 * - Filter Mata Kuliah & Live Search
 * - Web Push Notification Permission Opt-in & Peringatan H-1 / H-3 Jam
 * - Pengumuman Aksesibilitas Screen Reader (aria-live announcer)
 */

(function () {
    'use strict';

    // -------------------------------------------------------------------------
    // 1. Inisialisasi Data & Penyimpanan Lokal (LocalStorage / Default Seeds)
    // -------------------------------------------------------------------------
    const STORAGE_KEY_TASKS = 'tasktrack_tasks_data';
    const STORAGE_KEY_AUTH = 'tasktrack_auth_session';
    const STORAGE_KEY_NOTIF_OPTIN = 'tasktrack_notif_optin';

    // Sampel Tugas Awal
    const defaultTasks = [
        {
            id: 'task-1',
            title: 'Implementasi Arsitektur MVC & Routing Native',
            course: 'Pemrograman Web Lanjut',
            deadline: new Date(Date.now() + 18 * 3600 * 1000).toISOString().slice(0, 16),
            status: 'todo',
            lms_url: 'https://lms.universitas.ac.id/mod/assign/view.php?id=101',
            instructions: 'Buat struktur folder MVC murni menggunakan PHP native tanpa framework. Sertakan file index.php, Router.php, dan Controller dasar sesuai modul praktikum 4.'
        },
        {
            id: 'task-2',
            title: 'Normalisasi Basis Data Relasional 3NF',
            course: 'Sistem Basis Data',
            deadline: new Date(Date.now() + 52 * 3600 * 1000).toISOString().slice(0, 16),
            status: 'inprogress',
            lms_url: 'https://lms.universitas.ac.id/mod/assign/view.php?id=204',
            instructions: 'Lakukan perancangan ERD dan normalisasi tabel transaksi klinik hingga bentuk 3NF beserta DDL script MySQL.'
        },
        {
            id: 'task-3',
            title: 'Analisis Kebutuhan Sistem & Pembuatan SRS',
            course: 'Rekayasa Perangkat Lunak',
            deadline: new Date(Date.now() + 130 * 3600 * 1000).toISOString().slice(0, 16),
            status: 'inprogress',
            lms_url: 'https://lms.universitas.ac.id/mod/assign/view.php?id=305',
            instructions: 'Susun dokumen SRS standar IEEE 830 mencakup use case diagram, activity diagram, dan non-functional requirements.'
        },
        {
            id: 'task-4',
            title: 'Konfigurasi Subnetting & Routing Statis Cisco',
            course: 'Jaringan Komputer',
            deadline: new Date(Date.now() - 24 * 3600 * 1000).toISOString().slice(0, 16),
            status: 'done',
            lms_url: 'https://lms.universitas.ac.id/mod/assign/view.php?id=402',
            instructions: 'Praktikum Packet Tracer menghubungkan 3 router dengan routing statis dan konfigurasi DHCP server.'
        }
    ];

    let tasks = loadTasks();

    function loadTasks() {
        const stored = localStorage.getItem(STORAGE_KEY_TASKS);
        if (stored) {
            try {
                return JSON.parse(stored);
            } catch (e) {
                console.error('Gagal membaca data dari localStorage', e);
            }
        }
        localStorage.setItem(STORAGE_KEY_TASKS, JSON.stringify(defaultTasks));
        return defaultTasks;
    }

    function saveTasks() {
        localStorage.setItem(STORAGE_KEY_TASKS, JSON.stringify(tasks));
        renderBoard();
        updateMetrics();
    }

    // -------------------------------------------------------------------------
    // 2. Aksesibilitas Live Announcer (Screen Reader Helper)
    // -------------------------------------------------------------------------
    function announce(message) {
        const announcer = document.getElementById('a11y-announcer');
        if (announcer) {
            announcer.textContent = '';
            setTimeout(() => {
                announcer.textContent = message;
            }, 50);
        }
    }

    // -------------------------------------------------------------------------
    // 3. Kalkulasi Urgensi Deadline & Warna Indikator Kritis
    // -------------------------------------------------------------------------
    function calculateUrgency(deadlineStr, status) {
        if (status === 'done') {
            return {
                level: 'done',
                label: 'Selesai',
                pillClass: 'pill-done',
                cardClass: 'urgency-done',
                humanTime: 'Tuntas Terkumpul'
            };
        }

        const now = new Date();
        const deadline = new Date(deadlineStr);
        const diffMs = deadline - now;
        const diffHours = diffMs / (1000 * 60 * 60);

        if (diffMs <= 0) {
            return {
                level: 'critical',
                label: 'Terlewat / Kritis',
                pillClass: 'pill-critical',
                cardClass: 'urgency-critical',
                humanTime: 'Tenggat Waktu Lewat!'
            };
        } else if (diffHours < 24) {
            const h = Math.floor(diffHours);
            const m = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
            return {
                level: 'critical',
                label: 'Kritis (< 24 Jam)',
                pillClass: 'pill-critical',
                cardClass: 'urgency-critical',
                humanTime: `Sisa ${h} jam ${m} mnt lagi`
            };
        } else if (diffHours < 72) {
            const days = Math.floor(diffHours / 24);
            const hours = Math.floor(diffHours % 24);
            return {
                level: 'warning',
                label: '< 3 Hari',
                pillClass: 'pill-warning',
                cardClass: 'urgency-warning',
                humanTime: `Sisa ${days} hari ${hours} jam`
            };
        } else {
            const days = Math.floor(diffHours / 24);
            return {
                level: 'safe',
                label: '> 3 Hari (Aman)',
                pillClass: 'pill-safe',
                cardClass: 'urgency-safe',
                humanTime: `Sisa ${days} hari lagi`
            };
        }
    }

    // -------------------------------------------------------------------------
    // 4. Render Papan Kanban 3 Kolom & Auto-Sorting
    // -------------------------------------------------------------------------
    const dropzones = {
        todo: document.getElementById('dropzone-todo'),
        inprogress: document.getElementById('dropzone-inprogress'),
        done: document.getElementById('dropzone-done')
    };

    const counters = {
        todo: document.getElementById('count-todo'),
        inprogress: document.getElementById('count-inprogress'),
        done: document.getElementById('count-done')
    };

    function renderBoard() {
        const filterCourse = document.getElementById('filter-course-select')?.value || 'all';
        const searchQuery = (document.getElementById('search-task-input')?.value || '').toLowerCase().trim();

        // Kosongkan dropzone
        Object.values(dropzones).forEach(zone => {
            if (zone) zone.innerHTML = '';
        });

        // Filter tugas berdasarkan pilihan mata kuliah dan kata kunci
        const filteredTasks = tasks.filter(task => {
            const matchesCourse = (filterCourse === 'all' || task.course === filterCourse);
            const matchesSearch = (!searchQuery || 
                task.title.toLowerCase().includes(searchQuery) || 
                (task.instructions && task.instructions.toLowerCase().includes(searchQuery)) ||
                task.course.toLowerCase().includes(searchQuery)
            );
            return matchesCourse && matchesSearch;
        });

        // Auto-sorting: Tenggat terdekat di urutan teratas dalam setiap kolom
        filteredTasks.sort((a, b) => new Date(a.deadline) - new Date(b.deadline));

        const columnCounts = { todo: 0, inprogress: 0, done: 0 };

        filteredTasks.forEach(task => {
            const urgency = calculateUrgency(task.deadline, task.status);
            columnCounts[task.status] = (columnCounts[task.status] || 0) + 1;

            const card = document.createElement('article');
            card.className = `task-card ${urgency.cardClass}`;
            card.id = task.id;
            card.draggable = true;
            card.setAttribute('role', 'listitem');
            card.setAttribute('aria-labelledby', `title-${task.id}`);
            card.setAttribute('tabindex', '0');

            const lmsButtonHtml = task.lms_url ? `
                <a href="${escapeHtml(task.lms_url)}" target="_blank" rel="noopener noreferrer" class="btn-lms-shortcut" title="Buka tautan pengumpulan di LMS Kampus" aria-label="Buka halaman LMS untuk tugas ${escapeHtml(task.title)}">
                    <span class="lms-icon" aria-hidden="true">🔗</span>
                    <span>Portal LMS</span>
                </a>
            ` : `
                <span class="btn-lms-shortcut" style="opacity:0.5; cursor:not-allowed;" title="Tidak ada URL LMS">
                    <span class="lms-icon" aria-hidden="true">🔗</span>
                    <span>Tanpa LMS</span>
                </span>
            `;

            // Tombol navigasi status
            let moveButtonHtml = '';
            if (task.status === 'todo') {
                moveButtonHtml = `<button type="button" class="btn-action btn-move" data-id="${task.id}" data-next="inprogress" title="Pindah ke Sedang Dikerjakan" aria-label="Pindahkan tugas ke Sedang Dikerjakan">➡️</button>`;
            } else if (task.status === 'inprogress') {
                moveButtonHtml = `
                    <button type="button" class="btn-action btn-move" data-id="${task.id}" data-next="todo" title="Kembalikan ke To Do" aria-label="Kembalikan ke To Do">⬅️</button>
                    <button type="button" class="btn-action btn-move" data-id="${task.id}" data-next="done" title="Pindah ke Selesai" aria-label="Pindahkan tugas ke Selesai">✅</button>
                `;
            } else if (task.status === 'done') {
                moveButtonHtml = `<button type="button" class="btn-action btn-move" data-id="${task.id}" data-next="inprogress" title="Buka Kembali Tugas" aria-label="Buka kembali ke Sedang Dikerjakan">↩️</button>`;
            }

            card.innerHTML = `
                <header class="task-card-header">
                    <span class="course-badge" title="${escapeHtml(task.course)}">${escapeHtml(task.course)}</span>
                    <span class="urgency-pill ${urgency.pillClass}">${urgency.label}</span>
                </header>
                <h4 id="title-${task.id}" class="task-card-title">${escapeHtml(task.title)}</h4>
                <div class="task-meta">
                    <div class="task-deadline-time">
                        <span class="meta-icon" aria-hidden="true">⏰</span>
                        <time datetime="${task.deadline}" class="deadline-timer">${urgency.humanTime}</time>
                    </div>
                    ${task.instructions ? `<p class="task-notes-snippet" title="${escapeHtml(task.instructions)}">${escapeHtml(task.instructions)}</p>` : ''}
                </div>
                <footer class="task-card-footer">
                    ${lmsButtonHtml}
                    <div class="task-actions">
                        ${moveButtonHtml}
                        <button type="button" class="btn-action btn-edit" data-id="${task.id}" title="Edit Rincian Tugas" aria-label="Edit tugas ${escapeHtml(task.title)}">✏️</button>
                        <button type="button" class="btn-action btn-delete" data-id="${task.id}" title="Hapus Tugas" aria-label="Hapus tugas ${escapeHtml(task.title)}">🗑️</button>
                    </div>
                </footer>
            `;

            // Event Drag and Drop untuk kartu
            setupDragAndDrop(card, task.id);

            // Masukkan ke kolom yang sesuai
            if (dropzones[task.status]) {
                dropzones[task.status].appendChild(card);
            }
        });

        // Perbarui counter setiap kolom
        if (counters.todo) counters.todo.textContent = columnCounts.todo;
        if (counters.inprogress) counters.inprogress.textContent = columnCounts.inprogress;
        if (counters.done) counters.done.textContent = columnCounts.done;

        // Pesan jika kolom kosong
        ['todo', 'inprogress', 'done'].forEach(colKey => {
            const zone = dropzones[colKey];
            if (zone && zone.children.length === 0) {
                const emptyNotice = document.createElement('div');
                emptyNotice.className = 'empty-column-notice';
                emptyNotice.style.padding = '30px 10px';
                emptyNotice.style.textAlign = 'center';
                emptyNotice.style.color = '#94a3b8';
                emptyNotice.style.fontSize = '0.85rem';
                emptyNotice.textContent = 'Belum ada tugas di kolom ini.';
                zone.appendChild(emptyNotice);
            }
        });

        attachCardActionListeners();
    }

    // -------------------------------------------------------------------------
    // 5. Drag & Drop Interaktif (HTML5 Drag and Drop API)
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

                const taskIndex = tasks.findIndex(t => t.id === id);
                if (taskIndex > -1 && tasks[taskIndex].status !== statusKey) {
                    const oldStatus = tasks[taskIndex].status;
                    tasks[taskIndex].status = statusKey;
                    saveTasks();
                    announce(`Tugas "${tasks[taskIndex].title}" dipindahkan ke kolom ${statusKey}`);
                }
            });
        });
    }

    // -------------------------------------------------------------------------
    // 6. Action Listeners (Pindah Tombol, Edit, Hapus)
    // -------------------------------------------------------------------------
    function attachCardActionListeners() {
        // Tombol Pindah Status Manual (Aksesibel Keyboard)
        document.querySelectorAll('.btn-move').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const nextStatus = btn.getAttribute('data-next');
                const task = tasks.find(t => t.id === id);
                if (task && nextStatus) {
                    task.status = nextStatus;
                    saveTasks();
                    announce(`Tugas "${task.title}" dipindahkan ke kolom ${nextStatus}`);
                }
            });
        });

        // Tombol Edit
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                editTask(id);
            });
        });

        // Tombol Hapus
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                deleteTask(id);
            });
        });
    }

    function editTask(id) {
        const task = tasks.find(t => t.id === id);
        if (!task) return;

        document.getElementById('task-id').value = task.id;
        document.getElementById('task-title').value = task.title;
        document.getElementById('task-course').value = task.course;
        document.getElementById('task-deadline').value = task.deadline;
        document.getElementById('task-status').value = task.status;
        document.getElementById('task-lms-url').value = task.lms_url || '';
        document.getElementById('task-instructions').value = task.instructions || '';

        document.getElementById('save-button-text').textContent = 'Perbarui Tugas';
        const formSection = document.getElementById('task-management');
        if (formSection) {
            formSection.scrollIntoView({ behavior: 'smooth' });
            document.getElementById('task-title').focus();
        }
        announce(`Formulir diisi dengan rincian tugas "${task.title}" untuk diperbarui`);
    }

    function deleteTask(id) {
        const task = tasks.find(t => t.id === id);
        if (!task) return;

        if (confirm(`Apakah Anda yakin ingin menghapus tugas "${task.title}"?`)) {
            tasks = tasks.filter(t => t.id !== id);
            saveTasks();
            announce(`Tugas "${task.title}" telah dihapus`);
        }
    }

    // -------------------------------------------------------------------------
    // 7. Form Manajemen Tugas (Submit & Reset)
    // -------------------------------------------------------------------------
    const taskForm = document.getElementById('task-form');
    const resetBtn = document.getElementById('btn-reset-form');

    if (taskForm) {
        taskForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const title = document.getElementById('task-title').value.trim();
            const course = document.getElementById('task-course').value;
            const deadline = document.getElementById('task-deadline').value;
            const status = document.getElementById('task-status').value;
            const lmsUrl = document.getElementById('task-lms-url').value.trim();
            const instructions = document.getElementById('task-instructions').value.trim();
            const editId = document.getElementById('task-id').value;

            if (!title || !course || !deadline) {
                alert('Mohon lengkapi Judul Tugas, Mata Kuliah, dan Tanggal Deadline.');
                return;
            }

            if (editId) {
                // Mode Update
                const taskIndex = tasks.findIndex(t => t.id === editId);
                if (taskIndex > -1) {
                    tasks[taskIndex] = {
                        ...tasks[taskIndex],
                        title,
                        course,
                        deadline,
                        status,
                        lms_url: lmsUrl,
                        instructions
                    };
                    announce(`Tugas "${title}" berhasil diperbarui`);
                }
            } else {
                // Mode Create
                const newTask = {
                    id: 'task-' + Date.now(),
                    title,
                    course,
                    deadline,
                    status,
                    lms_url: lmsUrl,
                    instructions
                };
                tasks.push(newTask);
                announce(`Tugas baru "${title}" berhasil ditambahkan ke papan Kanban`);
            }

            saveTasks();
            resetTaskForm();

            // Gulir kembali ke papan Kanban untuk melihat tugas
            const kanbanSection = document.getElementById('kanban-section');
            if (kanbanSection) {
                kanbanSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            resetTaskForm();
            announce('Formulir input tugas telah dibersihkan');
        });
    }

    function resetTaskForm() {
        if (!taskForm) return;
        taskForm.reset();
        document.getElementById('task-id').value = '';
        document.getElementById('save-button-text').textContent = 'Simpan ke Papan Kanban';
    }

    // -------------------------------------------------------------------------
    // 8. Quick Input Teks Instruksi: Salin dari Clipboard
    // -------------------------------------------------------------------------
    const pasteClipboardBtn = document.getElementById('btn-paste-clipboard');
    if (pasteClipboardBtn) {
        pasteClipboardBtn.addEventListener('click', async () => {
            try {
                if (navigator.clipboard && navigator.clipboard.readText) {
                    const text = await navigator.clipboard.readText();
                    if (text) {
                        const textarea = document.getElementById('task-instructions');
                        textarea.value = (textarea.value ? textarea.value + '\n\n' : '') + text;
                        announce('Teks instruksi berhasil ditempel dari clipboard');
                        return;
                    }
                }
                const promptText = prompt('Tempel instruksi e-learning di sini:');
                if (promptText) {
                    const textarea = document.getElementById('task-instructions');
                    textarea.value = (textarea.value ? textarea.value + '\n\n' : '') + promptText;
                }
            } catch (err) {
                const promptText = prompt('Tempel instruksi e-learning di sini:');
                if (promptText) {
                    const textarea = document.getElementById('task-instructions');
                    textarea.value = (textarea.value ? textarea.value + '\n\n' : '') + promptText;
                }
            }
        });
    }

    // -------------------------------------------------------------------------
    // 9. Metrik Dashboard
    // -------------------------------------------------------------------------
    function updateMetrics() {
        const total = tasks.length;
        let urgentCount = 0;
        let progressCount = 0;
        let doneCount = 0;

        tasks.forEach(t => {
            if (t.status === 'done') {
                doneCount++;
            } else {
                if (t.status === 'inprogress') progressCount++;
                const urgency = calculateUrgency(t.deadline, t.status);
                if (urgency.level === 'critical') urgentCount++;
            }
        });

        const statTotal = document.getElementById('stat-total-tasks');
        const statUrgent = document.getElementById('stat-urgent-tasks');
        const statProgress = document.getElementById('stat-progress-tasks');
        const statDone = document.getElementById('stat-done-tasks');

        if (statTotal) statTotal.textContent = total;
        if (statUrgent) statUrgent.textContent = urgentCount;
        if (statProgress) statProgress.textContent = progressCount;
        if (statDone) statDone.textContent = doneCount;
    }

    // -------------------------------------------------------------------------
    // 10. Filter Mata Kuliah & Pencarian Cepat
    // -------------------------------------------------------------------------
    const filterSelect = document.getElementById('filter-course-select');
    const searchInput = document.getElementById('search-task-input');

    if (filterSelect) {
        filterSelect.addEventListener('change', () => {
            renderBoard();
            announce(`Papan disaring berdasarkan mata kuliah: ${filterSelect.options[filterSelect.selectedIndex].text}`);
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            renderBoard();
        });
    }

    // -------------------------------------------------------------------------
    // 11. Web Push Notification & Opt-in Prompt
    // -------------------------------------------------------------------------
    const notifBanner = document.getElementById('notification-banner');
    const enablePushBtn = document.getElementById('btn-enable-push');
    const dismissBannerBtn = document.getElementById('btn-dismiss-banner');
    const notifPromptHeaderBtn = document.getElementById('btn-notification-prompt');
    const notifBadge = document.getElementById('notif-badge');

    function checkNotificationStatus() {
        const hasOptedIn = localStorage.getItem(STORAGE_KEY_NOTIF_OPTIN);
        if (hasOptedIn === 'dismissed') {
            if (notifBanner) notifBanner.classList.add('hidden');
        } else if (hasOptedIn === 'granted' || (window.Notification && Notification.permission === 'granted')) {
            if (notifBanner) notifBanner.classList.add('hidden');
            if (notifBadge) notifBadge.classList.add('active');
        }
    }

    if (enablePushBtn) {
        enablePushBtn.addEventListener('click', requestNotificationPermission);
    }

    if (notifPromptHeaderBtn) {
        notifPromptHeaderBtn.addEventListener('click', () => {
            requestNotificationPermission(true);
        });
    }

    if (dismissBannerBtn) {
        dismissBannerBtn.addEventListener('click', () => {
            if (notifBanner) notifBanner.classList.add('hidden');
            localStorage.setItem(STORAGE_KEY_NOTIF_OPTIN, 'dismissed');
            announce('Pemberitahuan notifikasi ditutup');
        });
    }

    async function requestNotificationPermission(isManualClick = false) {
        if (!('Notification' in window)) {
            alert('Browser Anda tidak mendukung Web Notification API. Sistem akan menggunakan dialog peringatan.');
            triggerDeadlineAlerts();
            return;
        }

        try {
            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                localStorage.setItem(STORAGE_KEY_NOTIF_OPTIN, 'granted');
                if (notifBanner) notifBanner.classList.add('hidden');
                if (notifBadge) notifBadge.classList.add('active');
                
                new Notification('TaskTrack — Notifikasi Aktif!', {
                    body: 'Peringatan deadline H-1 hari & H-3 jam sebelum waktu pengumpulan telah diaktifkan.',
                    icon: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect width="32" height="32" rx="8" fill="%234f46e5"/><path d="M9 16.5L14 21.5L23 10.5" stroke="white" stroke-width="3" stroke-linecap="round"/></svg>'
                });

                announce('Izin notifikasi diberikan. Peringatan deadline telah aktif.');
                triggerDeadlineAlerts();
            } else {
                localStorage.setItem(STORAGE_KEY_NOTIF_OPTIN, 'denied');
                if (isManualClick) {
                    alert('Izin notifikasi tidak diaktifkan oleh browser.');
                }
            }
        } catch (e) {
            console.error('Kesalahan izin notifikasi', e);
        }
    }

    // Simulasi Peringatan H-1 & H-3 Jam
    function triggerDeadlineAlerts() {
        const now = new Date();
        const urgentTasks = tasks.filter(t => t.status !== 'done').filter(t => {
            const diffHours = (new Date(t.deadline) - now) / (1000 * 60 * 60);
            return diffHours > 0 && diffHours <= 24;
        });

        if (urgentTasks.length > 0) {
            const task = urgentTasks[0];
            const diffHours = Math.round((new Date(task.deadline) - now) / (1000 * 60 * 60));
            const alertMsg = `⚠️ PERINGATAN DEADLINE: Tugas "${task.title}" (${task.course}) tenggat waktu ${diffHours} jam lagi! Segera selesaikan dan unggah ke LMS.`;
            
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('TaskTrack — Deadline Mendekat!', {
                    body: alertMsg,
                    icon: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect width="32" height="32" rx="8" fill="%23ef4444"/><path d="M16 8v8M16 22h.01" stroke="white" stroke-width="3" stroke-linecap="round"/></svg>'
                });
            }
        }
    }

    // -------------------------------------------------------------------------
    // 12. Modal Dialog Autentikasi Pengguna
    // -------------------------------------------------------------------------
    const authModal = document.getElementById('auth-modal');
    const openAuthBtn = document.getElementById('btn-open-auth');
    const closeAuthBtn = document.getElementById('btn-close-auth');
    const googleSsoBtn = document.getElementById('btn-google-sso');
    const authForm = document.getElementById('auth-form');
    const userDisplayName = document.getElementById('user-display-name');

    function checkAuthSession() {
        const session = localStorage.getItem(STORAGE_KEY_AUTH) || sessionStorage.getItem(STORAGE_KEY_AUTH);
        if (session) {
            try {
                const userData = JSON.parse(session);
                if (userDisplayName && userData.name) {
                    userDisplayName.textContent = userData.name;
                }
            } catch (e) {}
        }
    }

    if (openAuthBtn && authModal) {
        openAuthBtn.addEventListener('click', () => {
            if (typeof authModal.showModal === 'function') {
                authModal.showModal();
            } else {
                authModal.setAttribute('open', '');
            }
            openAuthBtn.setAttribute('aria-expanded', 'true');
            announce('Jendela dialog autentikasi dibuka');
        });
    }

    if (closeAuthBtn && authModal) {
        closeAuthBtn.addEventListener('click', () => {
            if (typeof authModal.close === 'function') {
                authModal.close();
            } else {
                authModal.removeAttribute('open');
            }
            if (openAuthBtn) openAuthBtn.setAttribute('aria-expanded', 'false');
            announce('Jendela dialog autentikasi ditutup');
        });
    }

    // Google SSO Mock
    if (googleSsoBtn) {
        googleSsoBtn.addEventListener('click', () => {
            const mockUser = {
                name: 'Ahmad Fauzi (Google SSO)',
                email: 'ahmad.fauzi@mahasiswa.ac.id',
                type: 'google'
            };
            localStorage.setItem(STORAGE_KEY_AUTH, JSON.stringify(mockUser));
            if (userDisplayName) userDisplayName.textContent = mockUser.name;
            if (authModal && typeof authModal.close === 'function') authModal.close();
            announce('Berhasil masuk menggunakan Google SSO');
            alert('Berhasil masuk menggunakan akun Google SSO Mahasiswa!');
        });
    }

    // Form Login Biasa
    if (authForm) {
        authForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const email = document.getElementById('login-email').value;
            const rememberMe = document.getElementById('remember-me').checked;
            const shortName = email.split('@')[0];

            const userData = {
                name: shortName,
                email: email,
                type: 'email'
            };

            if (rememberMe) {
                localStorage.setItem(STORAGE_KEY_AUTH, JSON.stringify(userData));
            } else {
                sessionStorage.setItem(STORAGE_KEY_AUTH, JSON.stringify(userData));
            }

            if (userDisplayName) userDisplayName.textContent = shortName;
            if (authModal && typeof authModal.close === 'function') authModal.close();
            announce(`Berhasil masuk sebagai ${shortName}`);
            alert(`Selamat datang kembali, ${shortName}!`);
        });
    }

    // -------------------------------------------------------------------------
    // 13. Pintasan Keyboard Global & Mobile Menu Toggle
    // -------------------------------------------------------------------------
    const menuToggleBtn = document.getElementById('btn-menu-toggle');
    const primaryNav = document.getElementById('primary-nav');

    if (menuToggleBtn && primaryNav) {
        menuToggleBtn.addEventListener('click', () => {
            const isOpen = primaryNav.classList.toggle('open');
            menuToggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            announce(isOpen ? 'Menu navigasi dibuka' : 'Menu navigasi ditutup');
        });

        // Tutup menu otomatis saat link navigasi diklik pada mobile
        primaryNav.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (primaryNav.classList.contains('open')) {
                    primaryNav.classList.remove('open');
                    menuToggleBtn.setAttribute('aria-expanded', 'false');
                }
            });
        });
    }

    document.addEventListener('keydown', (e) => {
        // Alt + N: Loncat langsung ke Tambah Tugas
        if (e.altKey && (e.key === 'n' || e.key === 'N')) {
            e.preventDefault();
            const formSection = document.getElementById('task-management');
            if (formSection) {
                formSection.scrollIntoView({ behavior: 'smooth' });
                document.getElementById('task-title')?.focus();
            }
        }
    });

    // -------------------------------------------------------------------------
    // 14. Inisialisasi Aplikasi Saat DOM Selesai Dimuat
    // -------------------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', () => {
        initDropzones();
        renderBoard();
        updateMetrics();
        checkNotificationStatus();
        checkAuthSession();

        // Cek peringatan deadline saat awal masuk dashboard
        setTimeout(() => {
            triggerDeadlineAlerts();
        }, 1500);
    });

})();
