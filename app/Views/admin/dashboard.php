<?php
/**
 * @var array $stats
 */
?>

<div class="stats-grid">
    <div class="stat-card">
        <h3><?php echo $stats['totalPages']; ?></h3>
        <p>Seiten insgesamt</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $stats['menuItems']; ?></h3>
        <p>Menüeinträge</p>
    </div>
    <div class="stat-card">
        <h3>🕒</h3>
        <p>Letzte Aktualisierung: <?php echo $stats['lastUpdated']; ?></p>
    </div>
</div>

<div style="margin: 30px 0;">
    <h2>Willkommen im Admin-Bereich</h2>
    <p>Hier können Sie Ihre Website verwalten. Wählen Sie einen Bereich aus dem linken Menü aus, um zu beginnen.</p>
</div>

<div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px;">
    <h3>Schnellzugriff</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-top: 15px;">
        <a href="/admin/pages" class="btn" style="display: block; text-align: center;">
            📄 Seiten verwalten
        </a>
        <a href="/admin/menu" class="btn" style="display: block; text-align: center;">
            📑 Menü bearbeiten
        </a>
        <a href="/admin/files" class="btn" style="display: block; text-align: center;">
            📁 Dateien verwalten
        </a>
        <a href="/admin/settings" class="btn" style="display: block; text-align: center;">
            ⚙️ Einstellungen
        </a>
    </div>
</div>

<div style="margin-top: 30px; padding: 15px; background-color: #fff3cd; border-radius: 5px; border-left: 4px solid #ffc107;">
    <strong>🔧 Entwicklungsmodus aktiv</strong>
    <p style="margin: 10px 0 0; font-size: 0.9em;">
        Die Authentifizierung ist derzeit deaktiviert. Bitte implementieren Sie die Authentifizierung, bevor Sie die Website produktiv verwenden.
    </p>
</div>