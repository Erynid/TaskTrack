<?php
/**
 * Reusable Component 2: FeatureCard
 * Menampilkan kartu fitur keunggulan landing page dengan ikon, judul, deskripsi, dan tag manfaat.
 *
 * @param array $props [
 *   'icon' => string,
 *   'title' => string,
 *   'desc' => string,
 *   'tag' => string,
 *   'tag_class' => string
 * ]
 */
function renderFeatureCard(array $props): void {
    $icon = $props['icon'] ?? '⚡';
    $title = htmlspecialchars($props['title'] ?? '');
    $desc = htmlspecialchars($props['desc'] ?? '');
    $tag = htmlspecialchars($props['tag'] ?? 'Fitur');
    $tagClass = htmlspecialchars($props['tag_class'] ?? 'tag-primary');
?>
<div class="feature-card" role="article">
    <div class="feature-card-header">
        <div class="feature-icon-bubble" aria-hidden="true">
            <?php echo $icon; ?>
        </div>
        <span class="feature-tag <?php echo $tagClass; ?>"><?php echo $tag; ?></span>
    </div>
    <h3 class="feature-title"><?php echo $title; ?></h3>
    <p class="feature-desc"><?php echo $desc; ?></p>
</div>
<?php
}
