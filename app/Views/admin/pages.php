<?php
/**
 * @var array $pages
 */
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Seitenverwaltung</h2>
    <button onclick="createNewPage()" class="btn">➕ Neue Seite</button>
</div>

<?php if (empty($pages)): ?>
    <p>Keine Seiten gefunden. Erstellen Sie eine neue Seite, um zu beginnen.</p>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Titel</th>
                <th>Typ</th>
                <th>Aktualisiert</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pages as $page): ?>
                <tr>
                    <td><?php echo htmlspecialchars($page->getId()); ?></td>
                    <td><?php echo htmlspecialchars($page->getTitle()); ?></td>
                    <td><?php echo htmlspecialchars($page->getContentType()); ?></td>
                    <td>
                        <?php if ($page->getUpdatedAt()): ?>
                            <?php echo $page->getUpdatedAt()->format('Y-m-d H:i'); ?>
                        <?php else: ?>
                            --
                        <?php endif; ?>

<script>
/**
 * Create a new page with user input
 */
function createNewPage() {
    const pageId = prompt('Bitte geben Sie die ID der neuen Seite ein (wird als Dateiname verwendet):');
    
    if (!pageId || pageId.trim() === '') {
        alert('Bitte geben Sie eine gültige Seiten-ID ein.');
        return;
    }
    
    // Redirect to the new page editor
    window.location.href = '/admin/pages/new?id=' + encodeURIComponent(pageId.trim());
}
</script>
                    </td>
                    <td>
                        <a href="/admin/pages/<?php echo htmlspecialchars($page->getId()); ?>/edit" class="btn btn-secondary" style="padding: 5px 10px; margin-right: 5px;">📝 Bearbeiten</a>
                        <a href="/admin/pages/<?php echo htmlspecialchars($page->getId()); ?>/delete" class="btn btn-danger" style="padding: 5px 10px;" onclick="return confirm('Seite wirklich löschen?')">🗑️ Löschen</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>