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
function renderFeatureCard(array $props): void {
    $iconSvg = $props['icon'] ?? '';
    $title = htmlspecialchars($props['title'] ?? '');
    $desc = htmlspecialchars($props['desc'] ?? '');
    $tag = htmlspecialchars($props['tag'] ?? 'Fitur');
    $tagClass = htmlspecialchars($props['tag_class'] ?? 'tag-primary');
    $colSpan = htmlspecialchars($props['col_span'] ?? '');
?>
<div class="feature-card <?php echo $colSpan; ?>" role="article">
    <div class="feature-card-header">
        <div class="feature-icon-bubble" aria-hidden="true">
            <?php echo $iconSvg; ?>
        </div>
        <span class="feature-tag <?php echo $tagClass; ?>"><?php echo $tag; ?></span>
    </div>
    <h3 class="feature-title"><?php echo $title; ?></h3>
    <p class="feature-desc"><?php echo $desc; ?></p>
</div>
<?php
}
