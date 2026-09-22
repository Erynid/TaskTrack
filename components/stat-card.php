<?php
/**
 * Reusable Component: StatMetricCard (Sleek Dark Theme)
 * Menampilkan kartu ringkasan metrik untuk dashboard dengan ikon SVG stroke bersih.
 * Sesuai panduan .agents: zero raw emojis.
 *
 * @param array $props
 */
function renderStatCard(array $props): void {
    $id = htmlspecialchars($props['id'] ?? '');
    $label = htmlspecialchars($props['label'] ?? '');
    $value = htmlspecialchars((string)($props['value'] ?? '0'));
    $desc = htmlspecialchars($props['desc'] ?? '');
    $iconSvg = $props['icon_svg'] ?? '';
    $color = htmlspecialchars($props['color'] ?? '#f1f5f9');
?>
<div class="metric-card" role="group" aria-label="<?php echo $label; ?>">
    <div class="metric-card-header">
        <span><?php echo $label; ?></span>
        <?php if (!empty($iconSvg)): ?>
            <span class="metric-header-icon" aria-hidden="true"><?php echo $iconSvg; ?></span>
        <?php endif; ?>
    </div>
    <div class="metric-card-value" id="<?php echo $id; ?>" style="color: <?php echo $color; ?>;">
        <?php echo $value; ?>
    </div>
    <div class="metric-card-desc"><?php echo $desc; ?></div>
</div>
<?php
}
