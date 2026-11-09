<?php
/**
 * Reusable Component: FeatureCard (Apple Bento Edition)
 *
 * @param array $props [
 *   'icon' => string (SVG markup),
 *   'title' => string,
 *   'desc' => string,
 *   'tag' => string,
 *   'tag_class' => string,
 *   'col_span' => string,
 *   'variant' => string ('hero' | 'medium' | 'compact' | 'wide'),
 *   'visual_html' => string (optional HTML snippet rendered inside)
 * ]
 */
function renderFeatureCard(array $props): void {
    $iconSvg = $props['icon'] ?? '';
    $title = htmlspecialchars($props['title'] ?? '');
    $desc = htmlspecialchars($props['desc'] ?? '');
    $tag = htmlspecialchars($props['tag'] ?? 'Fitur');
    $tagClass = htmlspecialchars($props['tag_class'] ?? 'tag-primary');
    $colSpan = htmlspecialchars($props['col_span'] ?? '');
    $variant = htmlspecialchars($props['variant'] ?? 'default');
    $visualHtml = $props['visual_html'] ?? '';
?>
<div class="bento-card bento-card-<?php echo $variant; ?> <?php echo $colSpan; ?>" role="article">
    <div class="bento-card-content">
        <div class="bento-header-row">
            <?php if (!empty($iconSvg)): ?>
                <div class="bento-icon-ring" aria-hidden="true">
                    <?php echo $iconSvg; ?>
                </div>
            <?php endif; ?>
            <span class="bento-badge <?php echo $tagClass; ?>"><?php echo $tag; ?></span>
        </div>
        <h3 class="bento-title"><?php echo $title; ?></h3>
        <p class="bento-desc"><?php echo $desc; ?></p>
        <?php if (!empty($visualHtml)): ?>
            <div class="bento-visual-slot">
                <?php echo $visualHtml; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
}
