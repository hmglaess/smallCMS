<?php
/**
 * @var array $menuStructure
 * @var array $footerMenuStructure
 * @var array $availablePageIds
 */
?>

<h2>Menüverwaltung</h2>

<div class="menu-item-container">
    <h3>Hauptmenü</h3>
    <div class="menu-editor" id="main-menu-editor">
        <div id="menu-items">
            <?php if (empty($menuStructure)): ?>
                <p>Keine Menüeinträge gefunden. Fügen Sie neue Einträge hinzu.</p>
            <?php else: ?>
                <?php foreach ($menuStructure as $item): ?>
                    <?php include __DIR__ . '/partials/menu_item.php'; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <button class="btn" style="margin-top: 15px;" onclick="addMenuItem('main')">
            ➕ Menüpunkt hinzufügen
        </button>
    </div>
</div>

<div class="menu-editor-section">
    <h3>Footer-Menü</h3>
    <div class="menu-editor" id="footer-menu-editor">
        <div id="footer-menu-items">
            <?php if (empty($footerMenuStructure)): ?>
                <p>Keine Menüeinträge gefunden. Fügen Sie neue Einträge hinzu.</p>
            <?php else: ?>
                <?php foreach ($footerMenuStructure as $item): ?>
                    <?php include __DIR__ . '/partials/menu_item.php'; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <button class="btn" style="margin-top: 15px;" onclick="addMenuItem('footer')">
            ➕ Menüpunkt hinzufügen
        </button>
    </div>
</div>

<div class="menu-save-section">
    <button class="btn menu-save-button" onclick="saveMenu('main')">
        💾 Hauptmenü speichern
    </button>
    <button class="btn menu-save-button" onclick="saveMenu('footer')">
        💾 Footer-Menü speichern
    </button>
    <span class="menu-save-status" id="menu-save-status"></span>
</div>

<script>
    // Make availablePageIds available to JavaScript
    const availablePageIds = <?php echo json_encode($availablePageIds); ?>;
</script>
<script src="/assets/js/admin.js"></script>

