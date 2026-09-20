<?php
/**
 * Reusable Component 3: TaskCard
 * Menampilkan kartu tugas semantik (<article class="task-card">) pada kolom Kanban.
 *
 * @param array $task
 */
function renderTaskCard(array $task): void {
    $id = htmlspecialchars($task['id'] ?? '');
    $title = htmlspecialchars($task['title'] ?? '');
    $course = htmlspecialchars($task['course'] ?? '');
    $deadline = htmlspecialchars($task['deadline'] ?? '');
    $status = htmlspecialchars($task['status'] ?? 'todo');
    $urgency = htmlspecialchars($task['urgency'] ?? 'safe');
    $lmsUrl = htmlspecialchars($task['lms_url'] ?? '');
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
        <span class="course-badge" title="<?php echo $course; ?>"><?php echo $course; ?></span>
        <span class="urgency-pill <?php echo $u['pill']; ?>"><?php echo $u['label']; ?></span>
    </header>
    <h4 id="title-<?php echo $id; ?>" class="task-card-title"><?php echo $title; ?></h4>
    <div class="task-meta">
        <div class="task-deadline-time">
            <span class="meta-icon" aria-hidden="true">⏰</span>
            <time datetime="<?php echo $deadline; ?>" class="deadline-timer"><?php echo $u['time']; ?></time>
        </div>
        <?php if (!empty($instructions)): ?>
            <p class="task-notes-snippet" title="<?php echo $instructions; ?>"><?php echo $instructions; ?></p>
        <?php endif; ?>
    </div>
    <footer class="task-card-footer">
        <?php if (!empty($lmsUrl)): ?>
            <a href="<?php echo $lmsUrl; ?>" target="_blank" rel="noopener noreferrer" class="btn-lms-shortcut" title="Buka tautan pengumpulan di LMS Kampus" aria-label="Buka halaman LMS untuk tugas <?php echo $title; ?>">
                <span class="lms-icon" aria-hidden="true">🔗</span>
                <span>Portal LMS</span>
            </a>
        <?php else: ?>
            <span class="btn-lms-shortcut" style="opacity:0.5; cursor:not-allowed;" title="Tidak ada URL LMS">
                <span class="lms-icon" aria-hidden="true">🔗</span>
                <span>Tanpa LMS</span>
            </span>
        <?php endif; ?>
        <div class="task-actions">
            <?php if ($status === 'todo'): ?>
                <button type="button" class="btn-action btn-move" data-id="<?php echo $id; ?>" data-next="inprogress" title="Pindah ke Sedang Dikerjakan" aria-label="Pindahkan tugas ke Sedang Dikerjakan">➡️</button>
            <?php elseif ($status === 'inprogress'): ?>
                <button type="button" class="btn-action btn-move" data-id="<?php echo $id; ?>" data-next="todo" title="Kembalikan ke To Do" aria-label="Kembalikan ke To Do">⬅️</button>
                <button type="button" class="btn-action btn-move" data-id="<?php echo $id; ?>" data-next="done" title="Pindah ke Selesai" aria-label="Pindahkan tugas ke Selesai">✅</button>
            <?php elseif ($status === 'done'): ?>
                <button type="button" class="btn-action btn-move" data-id="<?php echo $id; ?>" data-next="inprogress" title="Buka Kembali Tugas" aria-label="Buka kembali ke Sedang Dikerjakan">↩️</button>
            <?php endif; ?>
            <button type="button" class="btn-action btn-edit" data-id="<?php echo $id; ?>" title="Edit Rincian Tugas" aria-label="Edit tugas <?php echo $title; ?>">✏️</button>
            <button type="button" class="btn-action btn-delete" data-id="<?php echo $id; ?>" title="Hapus Tugas" aria-label="Hapus tugas <?php echo $title; ?>">🗑️</button>
        </div>
    </footer>
</article>
<?php
}
