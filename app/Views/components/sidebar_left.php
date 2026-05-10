<?php
/**
 * @var string $title
 * @var string $content
 * @var string $siteName
 * @var App\Services\SidebarService $sidebarService
 */
?>

<aside class="sidebar-left" aria-label="Inhalt links">
    <?php if (isset($sidebarService)):
            foreach ($sidebarService->getLeftSidebar() as $widget): ?>
            <div class="widget">
                <?php if (isset($widget['title'])): ?>
                    <h3><?= htmlspecialchars($widget['title']) ?></h3>
                <?php endif; 
                if (isset($widget['content'])){
                     echo $widget['content'];
                }?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Standard-Widgets für linke Seitenleiste -->
        <div class="widget">
            <h3>🎵 Nächste Events</h3>
            <div id="events-container">
                <!-- Events werden dynamisch geladen -->
                <p>10. Januar 2026<br>
                Queens 45 BC
                </p>
                <div class="image-container">
                    <img id="myImage" src="/assets/img/Catharina_DiscoFoxSpecialParty_4_50.png" alt="Klick mich zum Vergrößern">
                </div>
            </div>

            <div id="lightbox" class="lightbox">
                <img id="largeImage" src="/assets/img/Catharina_DiscoFoxSpecialParty_4_50.png" alt="Vergrößertes Bild">
                <button type="button" onclick="closeLightbox()">Schließen</button>
            </div>

        </div>
        <div class="widget">
            <h3>📍 Locations</h3>
            <p></p>
        </div>
    <?php endif; ?>
</aside>