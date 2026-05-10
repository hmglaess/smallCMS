<?php

/**
 * Format file size for display
 *
 * @param int $bytes File size in bytes
 * @return string Formatted size string (e.g., "1.5 MB", "2.3 KB", "123 bytes")
 */
function formatFileSize($bytes): string {
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 1) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 1) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * @var array $files File data array injected into this view
 */

?>

<h2>Dateiverwaltung</h2>

<div class="file-manager-controls">
    <div id="breadcrumbs">
        <span class="breadcrumb-item" onclick="loadDirectory('')">📁 assets/</span>
        <span id="current-path"></span>
    </div>
    <p>Verwalten Sie Ihre Bilder, CSS- und JavaScript-Dateien.</p>
    <div class="file-actions">
        <button class="btn btn-secondary" onclick="goBack()" id="back-button">← Zurück</button>
        <button class="btn" onclick="showUploadDialog()">📁 Datei hochladen</button>
        <button class="btn" onclick="createFolder()">📂 Ordner erstellen</button>
    </div>
</div>

<div class="file-manager">
    <?php if (empty($files)): ?>
        <p class="no-files">Keine Dateien gefunden. Laden Sie Dateien hoch, um zu beginnen.</p>
    <?php else: ?>
        <?php foreach ($files as $name => $file): ?>
            <div class="file-item <?php echo $file['type'] === 'directory' ? 'directory' : 'file'; ?>">
                <div class="file-icon">
                    <?php if ($file['type'] === 'directory'): ?>
                        📂
                    <?php else: ?>
                        <?php 
                        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                        $icons = [
                            'jpg' => '🖼️', 'jpeg' => '🖼️', 'png' => '🖼️', 'gif' => '🖼️', 'svg' => '🖼️',
                            'css' => '🎨', 'js' => '⚙️', 'json' => '📋', 'html' => '🌐',
                            'php' => '🐘', 'md' => '📝', 'txt' => '📄'
                        ];
                        echo $icons[$extension] ?? '📄';
                        ?>
                    <?php endif; ?>
                </div>
                <div class="file-name"><?php echo htmlspecialchars($name); ?></div>
                <div class="file-size">
                    <?php if ($file['type'] === 'directory'): ?>
                        Ordner
                    <?php else: ?>
                        <?php echo formatFileSize($file['size']); ?>
                    <?php endif; ?>
                </div>
                <div class="file-actions">
                    <?php if ($file['type'] === 'directory'): ?>
                        <button class="btn btn-secondary btn-small" data-action="open" data-name="<?php echo htmlspecialchars($name); ?>">
                            🔍 Öffnen
                        </button>
                        <?php 
                        // Protected folders that cannot be deleted
                        $protectedFolders = ['css', 'img', 'js'];
                        if (!in_array(strtolower($name), $protectedFolders)): ?>
                            <button class="btn btn-danger btn-small" data-action="delete" data-name="<?php echo htmlspecialchars($name); ?>">
                                🗑️ Löschen
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="btn btn-danger btn-small" data-action="delete" data-name="<?php echo htmlspecialchars($name); ?>">
                            🗑️ Löschen
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div id="upload-dialog">
    <div>
        <h3>Datei hochladen</h3>
        <form id="upload-form">
            <div class="form-group">
                <input type="file" id="file-upload">
            </div>
            <div class="upload-dialog-actions">
                <button type="button" class="btn btn-secondary" onclick="hideUploadDialog()">
                    Abbrechen
                </button>
                <button type="button" class="btn" onclick="uploadFile()">
                    Hochladen
                </button>
            </div>
        </form>
    </div>
</div>