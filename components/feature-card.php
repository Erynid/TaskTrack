<?php
/**
 * Reusable Component: FeatureCard
 * Menampilkan kartu fitur keunggulan landing page bergaya Linear / modern devtool.
 * Bebas emoji, berpusat pada ikon stroke bersih, dan tipografi hierarkis.
 *
 * @param array $props [
 *   'icon' => string (SVG markup),
 *   'title' => string,
 *   'desc' => string,
 *   'tag' => string,
 *   'tag_class' => string,
 *   'col_span' => string (opsional untuk bento grid)
 * ]
 */
// Fungsi ini untuk merender komponen kartu fitur keunggulan aplikasi ke dalam HTML
function renderFeatureCard(array $props): void {
    // Baris-baris ini untuk mengambil dan mengamankan properti kartu fitur dari XSS
    $iconSvg = $props['icon'] ?? '';
    $title = htmlspecialchars($props['title'] ?? '');
    $desc = htmlspecialchars($props['desc'] ?? '');
    $tag = htmlspecialchars($props['tag'] ?? 'Fitur');
    $tagClass = htmlspecialchars($props['tag_class'] ?? 'tag-primary');
    $colSpan = htmlspecialchars($props['col_span'] ?? '');
?>
<!-- Kontainer kartu fitur dengan role semantik article -->
<div class="feature-card <?php echo $colSpan; ?>" role="article">
    <div class="feature-card-header">
        <!-- Baris ini menampilkan ikon fitur dalam lingkaran pembungkus -->
        <div class="feature-icon-bubble" aria-hidden="true">
            <?php echo $iconSvg; ?>
        </div>
        <!-- Baris ini menampilkan tag atau kategori badge fitur -->
        <span class="feature-tag <?php echo $tagClass; ?>"><?php echo $tag; ?></span>
    </div>
    <!-- Baris ini menampilkan judul fitur -->
    <h3 class="feature-title"><?php echo $title; ?></h3>
    <!-- Baris ini menampilkan deskripsi rincian fitur -->
    <p class="feature-desc"><?php echo $desc; ?></p>
</div>
<?php
}
