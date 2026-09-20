<?php
/**
 * Reusable Component: TaskCard
 * Menampilkan kartu tugas semantik (<article class="task-card">) pada kolom Kanban.
 * Sesuai panduan taste-skill: tanpa emoji mentah, menggunakan SVG stroke presisi, dan bebas em-dash.
 *
 * @param array $task
 */
function renderTaskCard(array $task): void {
    $id = htmlspecialchars($task['id'] ?? '');
    $title = htmlspecialchars($task['title'] ?? '');
    $course = htmlspecialchars($task['course'] ?? 'Umum');
    $deadline = htmlspecialchars($task['deadline'] ?? '');
    $status = htmlspecialchars($task['status'] ?? 'todo');
    $urgency = htmlspecialchars($task['urgency'] ?? 'safe');
    $lmsUrl = htmlspecialchars($task['lms_url'] ?? '');
    $linkLabel = !empty($task['lms_label']) ? htmlspecialchars($task['lms_label']) : 'Buka Tautan';
    $instructions = htmlspecialchars($task['instructions'] ?? '');

    $urgencyMap = [
        'critical' => ['label' => 'Kritis (< 24 Jam)', 'pill' => 'pill-critical', 'class' => 'urgency-critical', 'time' => 'Sisa 18 Jam Lagi'],
        'warning'  => ['label' => '< 3 Hari',          'pill' => 'pill-warning',  'class' => 'urgency-warning',  'time' => 'Sisa 2 Hari 4 Jam'],
        'safe'     => ['label' => '> 3 Hari (Aman)',   'pill' => 'pill-safe',     'class' => 'urgency-safe',     'time' => 'Sisa 5 Hari 10 Jam'],
        'done'     => ['label' => 'Selesai',           'pill' => 'pill-done',     'class' => 'urgency-done',     'time' => 'Tuntas Terkumpul']
    ];

    $u = $urgencyMap[$urgency] ?? $urgencyMap['safe'];
?>
<article class="task-card <?php echo $u['class']; ?>" id="<?php echo $id; ?>" draggable="true" role="listitem" aria-labelledby="title-<?php echo $id; ?>" tabindex="0">
    <header class="task-card-header">
        <span class="course-badge" title="Kategori: <?php echo $course; ?>"><?php echo $course; ?></span>
        <span class="urgency-pill <?php echo $u['pill']; ?>"><?php echo $u['label']; ?></span>
    </header>
    <h4 id="title-<?php echo $id; ?>" class="task-card-title"><?php echo $title; ?></h4>
    <div class="task-meta">
        <div class="task-deadline-time">
            <svg class="meta-icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <time datetime="<?php echo $deadline; ?>" class="deadline-timer"><?php echo $u['time']; ?></time>
        </div>
        <?php if (!empty($instructions)): ?>
            <p class="task-notes-snippet" title="<?php echo $instructions; ?>"><?php echo $instructions; ?></p>
        <?php endif; ?>
    </div>
    <footer class="task-card-footer">
        <?php if (!empty($lmsUrl)): ?>
            <a href="<?php echo $lmsUrl; ?>" target="_blank" rel="noopener noreferrer" class="btn-lms-shortcut" title="Buka tautan: <?php echo $lmsUrl; ?>" aria-label="Buka tautan untuk tugas <?php echo $title; ?>">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                </svg>
                <span><?php echo $linkLabel; ?></span>
            </a>
        <?php else: ?>
            <span class="btn-lms-shortcut btn-lms-disabled" title="Tidak ada tautan terlampir">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                    <path d="M10.5 10.5A2 2 0 0 0 8 13v6a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-3"></path>
                </svg>
                <span>Tanpa Tautan</span>
            </span>
        <?php endif; ?>
        <div class="task-actions">
            <?php if ($status === 'todo'): ?>
                <button type="button" class="btn-action btn-move" data-id="<?php echo $id; ?>" data-next="inprogress" title="Pindah ke Sedang Dikerjakan" aria-label="Pindahkan tugas ke Sedang Dikerjakan">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </button>
            <?php elseif ($status === 'inprogress'): ?>
                <button type="button" class="btn-action btn-move" data-id="<?php echo $id; ?>" data-next="todo" title="Kembalikan ke To Do" aria-label="Kembalikan ke To Do">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </button>
                <button type="button" class="btn-action btn-move" data-id="<?php echo $id; ?>" data-next="done" title="Pindah ke Selesai" aria-label="Pindahkan tugas ke Selesai">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </button>
            <?php elseif ($status === 'done'): ?>
                <button type="button" class="btn-action btn-move" data-id="<?php echo $id; ?>" data-next="inprogress" title="Buka Kembali Tugas" aria-label="Buka kembali ke Sedang Dikerjakan">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="1 4 1 10 7 10"></polyline>
                        <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                    </svg>
                </button>
            <?php endif; ?>
            <button type="button" class="btn-action btn-edit" data-id="<?php echo $id; ?>" title="Edit Rincian Tugas" aria-label="Edit tugas <?php echo $title; ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
            </button>
            <button type="button" class="btn-action btn-delete" data-id="<?php echo $id; ?>" title="Hapus Tugas" aria-label="Hapus tugas <?php echo $title; ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
            </button>
        </div>
    </footer>
</article>
<?php
}
