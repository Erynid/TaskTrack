<?php
/**
 * Reusable Component 1: StatMetricCard
 * Menampilkan kartu statistik ringkas dengan label, nilai, dan deskripsi konteks.
 *
 * @param array $props [
 *   'id' => string,
 *   'label' => string,
 *   'value' => string|int,
 *   'desc' => string,
 *   'type' => 'total'|'urgent'|'progress'|'done',
 *   'color_class' => string (optional)
 * ]
 */
function renderStatCard(array $props): void {
    $id = htmlspecialchars($props['id'] ?? '');
    $label = htmlspecialchars($props['label'] ?? '');
    $value = htmlspecialchars((string)($props['value'] ?? '0'));
    $desc = htmlspecialchars($props['desc'] ?? '');
    $type = htmlspecialchars($props['type'] ?? 'total');
    $colorClass = htmlspecialchars($props['color_class'] ?? '');
?>
<div class="metric-card metric-<?php echo $type; ?>" role="group" aria-label="<?php echo $label; ?>">
    <span class="metric-label"><?php echo $label; ?></span>
    <span class="metric-value <?php echo $colorClass; ?>" id="<?php echo $id; ?>"><?php echo $value; ?></span>
    <span class="metric-desc"><?php echo $desc; ?></span>
</div>
<?php
}
