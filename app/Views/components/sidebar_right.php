<?php
/**
 * @var string $title
 * @var string $content
 * @var string $siteName
 * @var App\Services\SidebarService $sidebarService
 */
?>

<aside class="sidebar-right" aria-label="Inhalt rechts">
    <?php if (isset($sidebarService)): ?>
        <?php foreach ($sidebarService->getRightSidebar() as $widget): ?>
            <div class="widget">
                <?php if (isset($widget['title'])): ?>
                    <h3><?= htmlspecialchars($widget['title']) ?></h3>
                <?php endif; ?>
                <?php if (isset($widget['content'])): ?>
                    <?= $widget['content'] ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Standard-Widgets für rechte Seitenleiste -->
        <div class="widget">
            <h3>📞 Kontakt</h3>
            <p>
                <strong>Tel:</strong><br>
                <strong>Mail:</strong>
                info@discofoxberlin.de
            </p>
        </div>
        <div class="widget">
            <!-- Leeres Widget für zukünftige Inhalte -->
        </div>
        <div class="widget">
            <h3>💬 Social Media</h3>
            <p>
                <a href="https://www.facebook.com/groups/3810307689295251/">
                    Facebook
                </a>
            </p>
        </div>
    <?php endif; ?>
</aside>