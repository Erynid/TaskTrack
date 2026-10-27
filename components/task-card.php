<?php
/**
 * Reusable Component: TaskCard (Sleek Dark Theme)
 * Menampilkan kartu tugas semantik (<article class="task-card">) pada 4 Kolom Kanban.
 * Sesuai panduan .agents/rules: bebas emoji mentah, menggunakan SVG stroke presisi, dan bebas em-dash.
 *
 * @param array $task
 */
// Fungsi ini untuk merender satu kartu tugas Kanban ke elemen semantik <article>
function renderTaskCard(array $task): void {
    // Baris-baris ini untuk mengamankan data properti tugas dari XSS
    $id = htmlspecialchars($task['id'] ?? 'task-' . uniqid());
    $title = htmlspecialchars($task['title'] ?? 'Tugas Baru');
    $course = htmlspecialchars($task['course'] ?? 'Umum');
    $deadline = htmlspecialchars($task['deadline'] ?? '');
    $status = htmlspecialchars($task['status'] ?? 'todo');
    $priority = strtolower($task['priority'] ?? 'medium');
    $lmsUrl = htmlspecialchars($task['lms_url'] ?? '');
    $rawLabel = $task['lms_label'] ?? 'LMS';
    // Baris ini membersihkan karakter non-ASCII pada label LMS
    $linkLabel = preg_replace('/[^\x20-\x7E]/', '', (string)$rawLabel);
    $linkLabel = trim($linkLabel);
    if (empty($linkLabel)) $linkLabel = 'LMS';
    $linkLabel = htmlspecialchars($linkLabel);
    $instructions = htmlspecialchars($task['instructions'] ?? '');

    // Bagian ini untuk memformat tanggal deadline dan menghitung selisih sisa waktu
    $timeFormatted = "Belum ditentukan";
    $remainingTime = "Segera";
    $isOverdue = false;

    if (!empty($deadline)) {
        $deadlineTs = strtotime($deadline);
        // Memformat timestamp menjadi format tanggal terbaca (contoh: Oct 20, 2026)
        $timeFormatted = date('M j, Y', $deadlineTs);
        // Menghitung selisih detik antara deadline dan waktu saat ini
        $diff = $deadlineTs - time();

        if ($status === 'done' || $status === 'completed') {
            $remainingTime = "Selesai";
        } elseif ($diff <= 0) {
            // Jika selisih minus, tandai bahwa tugas telah melewati tenggat waktu
            $remainingTime = "Tenggat Lewat";
            $isOverdue = true;
        } elseif ($diff < 86400) {
            // Jika sisa waktu kurang dari 24 jam, tampilkan hitungan jam
            $hours = max(1, round($diff / 3600));
            $remainingTime = "Sisa {$hours} Jam";
            $isOverdue = true;
        } else {
            // Jika lebih dari 24 jam, tampilkan hitungan hari
            $days = round($diff / 86400);
            $remainingTime = "{$days} days";
        }
    }

    // Bagian ini untuk menentukan kelas CSS warna badge dan ikon prioritas
    $priorityClass = 'priority-med';
    $priorityLabel = 'Medium';
    $priorityIconSvg = '<svg class="icon-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>';

    if ($status === 'done' || $status === 'completed') {
        $priorityClass = 'priority-done';
        $priorityLabel = 'Done';
        $priorityIconSvg = '<svg class="icon-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>';
    } elseif ($priority === 'high' || $isOverdue) {
        $priorityClass = 'priority-high';
        $priorityLabel = 'High';
    } elseif ($priority === 'low') {
        $priorityClass = 'priority-low';
        $priorityLabel = 'Low';
    }
?>
<!-- Elemen kartu tugas utama yang dapat digeser (draggable) dengan keyboard accessibility -->
<article class="task-card" id="<?php echo $id; ?>" draggable="true" role="listitem" aria-labelledby="title-<?php echo $id; ?>" tabindex="0" <?php if ($isOverdue && $status !== 'done') echo 'style="border-left: 3px solid #f97316;"'; ?>>
    <!-- Baris ini menampilkan judul tugas -->
    <h3 class="task-card-title" id="title-<?php echo $id; ?>"><?php echo $title; ?></h3>
    
    <!-- Bagian metadata tanggal deadline dan status sisa waktu -->
    <div class="task-card-meta">
        <!-- Menampilkan tanggal deadline -->
        <span class="meta-item">
            <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span><?php echo $timeFormatted; ?></span>
        </span>
        <!-- Menampilkan sisa waktu / peringatan jika mendesak -->
        <span class="meta-item <?php echo $isOverdue && $status !== 'done' ? 'meta-urgent' : ''; ?>">
            <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <span><?php echo $remainingTime; ?></span>
        </span>
    </div>
    
    <!-- Baris ini menampilkan nama mata kuliah -->
    <p class="task-course-subtitle"><?php echo $course; ?></p>
    
    <?php if (!empty($instructions)): ?>
        <!-- Baris ini menampilkan cuplikan teks instruksi tugas jika ada -->
        <p class="task-instructions-preview" title="<?php echo $instructions; ?>"><?php echo $instructions; ?></p>
    <?php endif; ?>

    <!-- Bagian footer kartu tugas berisi badge prioritas, link LMS, dan tombol aksi -->
    <div class="task-card-footer">
        <div style="display: flex; align-items: center; gap: 0.55rem;">
            <!-- Menampilkan badge label dan warna prioritas -->
            <span class="priority-badge <?php echo $priorityClass; ?>">
                <?php echo $priorityIconSvg; ?>
                <span><?php echo $priorityLabel; ?></span>
            </span>
            <?php if (!empty($lmsUrl)): ?>
                <!-- Tombol tautan langsung menuju portal LMS / tugas eksternal -->
                <a href="<?php echo $lmsUrl; ?>" target="_blank" rel="noopener noreferrer" class="btn-lms-link <?php echo $isOverdue ? 'btn-lms-urgent' : ''; ?>" aria-label="Buka pengumpulan untuk <?php echo $title; ?>">
                    <svg class="icon-svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <line x1="10" y1="14" x2="21" y2="3"></line>
                    </svg>
                    <span><?php echo $linkLabel; ?></span>
                </a>
            <?php endif; ?>
        </div>

        <!-- Tombol-tombol aksi interaktif untuk memindah kolom, melihat detail, mengedit, atau menghapus -->
        <div class="task-actions">
            <?php if ($status === 'todo' || $status === 'planned'): ?>
                <!-- Tombol untuk memindahkan tugas dari status Belum ke Sedang Dikerjakan -->
                <button type="button" class="btn-action btn-move" data-id="<?php echo $id; ?>" data-next="inprogress" title="Pindah ke In progress" aria-label="Pindahkan ke In progress">
                    <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </button>
            <?php elseif ($status === 'inprogress'): ?>
                <!-- Tombol untuk mengembalikan tugas ke Belum Dikerjakan -->
                <button type="button" class="btn-action btn-move" data-id="<?php echo $id; ?>" data-next="todo" title="Kembalikan ke Planned" aria-label="Kembalikan ke Planned">
                    <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                </button>
                <!-- Tombol untuk memindahkan tugas ke Selesai -->
                <button type="button" class="btn-action btn-move" data-id="<?php echo $id; ?>" data-next="done" title="Pindah ke Completed" aria-label="Pindahkan ke Completed">
                    <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </button>
            <?php elseif ($status === 'overdue'): ?>
                <!-- Tombol untuk mulai mengerjakan tugas yang lewat tenggat -->
                <button type="button" class="btn-action btn-move" data-id="<?php echo $id; ?>" data-next="inprogress" title="Kerjakan Sekarang" aria-label="Pindahkan ke In progress">
                    <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </button>
                <!-- Tombol untuk menandai tugas lewat tenggat langsung selesai -->
                <button type="button" class="btn-action btn-move" data-id="<?php echo $id; ?>" data-next="done" title="Tandai Selesai" aria-label="Tandai Selesai">
                    <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </button>
            <?php elseif ($status === 'done' || $status === 'completed'): ?>
                <!-- Tombol untuk membuka kembali tugas yang sudah selesai -->
                <button type="button" class="btn-action btn-move" data-id="<?php echo $id; ?>" data-next="inprogress" title="Buka Kembali" aria-label="Buka kembali">
                    <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                </button>
            <?php endif; ?>
            <!-- Tombol untuk membuka modal pop-up detail rincian tugas -->
            <button type="button" class="btn-action btn-detail" data-id="<?php echo $id; ?>" title="Lihat Rincian Tugas" aria-label="Lihat rincian tugas">
                <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            </button>
            <!-- Tombol untuk membuka modal edit form data tugas -->
            <button type="button" class="btn-action btn-edit" data-id="<?php echo $id; ?>" title="Edit Tugas" aria-label="Edit tugas">
                <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            </button>
            <!-- Tombol untuk menghapus kartu tugas -->
            <button type="button" class="btn-action btn-delete" data-id="<?php echo $id; ?>" title="Hapus Tugas" aria-label="Hapus tugas">
                <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
    </div>
</article>
<?php
}
