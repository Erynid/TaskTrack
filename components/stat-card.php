<?php
/**
 * Reusable Component: StatMetricCard (Sleek Dark Theme)
 * Menampilkan kartu ringkasan metrik untuk dashboard dengan ikon SVG stroke bersih.
 * Sesuai panduan .agents: zero raw emojis.
 *
 * @param array $props
 */
// Fungsi ini untuk merender komponen kartu metrik statistik ke HTML
function renderStatCard(array $props): void {
    // Baris-baris ini untuk mengamankan nilai properti dengan htmlspecialchars agar terhindar dari XSS
    $id = htmlspecialchars($props['id'] ?? '');
    $label = htmlspecialchars($props['label'] ?? '');
    $value = htmlspecialchars((string)($props['value'] ?? '0'));
    $desc = htmlspecialchars($props['desc'] ?? '');
    $iconSvg = $props['icon_svg'] ?? '';
    $color = htmlspecialchars($props['color'] ?? '#f1f5f9');
?>
<!-- Kontainer kartu metrik dengan atribut aksesibilitas role group -->
<div class="metric-card" role="group" aria-label="<?php echo $label; ?>">
    <div class="metric-card-header">
        <!-- Baris ini menampilkan judul label kartu metrik -->
        <span><?php echo $label; ?></span>
        <?php if (!empty($iconSvg)): ?>
            <!-- Menampilkan ikon SVG pendukung jika disediakan pada properti -->
            <span class="metric-header-icon" aria-hidden="true"><?php echo $iconSvg; ?></span>
        <?php endif; ?>
    </div>
    <!-- Baris ini menampilkan nilai angka metrik dengan ID khusus untuk pembaruan via JavaScript -->
    <div class="metric-card-value" id="<?php echo $id; ?>" style="color: <?php echo $color; ?>;">
        <?php echo $value; ?>
    </div>
    <!-- Baris ini menampilkan teks keterangan tambahan di bawah angka -->
    <div class="metric-card-desc"><?php echo $desc; ?></div>
</div>
<?php
}
