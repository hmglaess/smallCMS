<?php
/**
 * @var App\Services\SidebarService $sidebarService
 * @var array $sidebarLeft
 * @var array $sidebarRight
 */

// Initialize variables if not set
$sidebarLeft = $sidebarLeft ?? [];
$sidebarRight = $sidebarRight ?? [];
?>

<h2>Seitenbereichsverwaltung</h2>

<div class="sidebar-management-container">
    <!-- Modal für Widget-Bearbeitung -->
    <div id="widgetEditModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Widget bearbeiten</h3>
                <span class="close" onclick="closeWidgetModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="widgetTitle">Titel:</label>
                    <input type="text" id="widgetTitle" class="form-control">
                </div>
                <div class="form-group">
                    <label for="widgetType">Typ:</label>
                    <select id="widgetType" class="form-control">
                        <option value="html">HTML (Rich Text)</option>
                        <option value="text">Einfacher Text</option>
                        <option value="link">Link</option>
                        <option value="custom">Benutzerdefiniert</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Inhalt:</label>
                    <div id="widgetEditor" style="height: 300px;"></div>
                    <textarea id="widgetContent" style="display: none;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeWidgetModal()">Abbrechen</button>
                <button class="btn btn-primary" onclick="saveWidgetChanges()">Speichern</button>
            </div>
        </div>
    </div>

    <!-- Linker Seitenbereich -->
    <div class="sidebar-editor-section">
        <h3>Linker Seitenbereich</h3>
        <div class="sidebar-widget-list" id="left-sidebar-list">
            <?php if (empty($sidebarLeft)): ?>
                <p class="empty-message">Keine Widgets im linken Seitenbereich. Fügen Sie neue Widgets hinzu.</p>
            <?php else: ?>
                <?php foreach ($sidebarLeft as $index => $widget): ?>
                    <div class="sidebar-widget-item" data-side="left" data-index="<?php echo $index; ?>">
                        <div class="widget-preview">
                            <h4><?php echo htmlspecialchars($widget['title'] ?? 'Unbenanntes Widget'); ?></h4>
                            <div class="widget-content-preview">
                                <?php 
                                $preview = $widget['content'] ?? '';
                                $type = $widget['type'] ?? 'html'; // Standardwert 'html'
                                if ($type === 'html') {
                                    echo strip_tags($preview, '<p><br><strong><em><u><a>');
                                } else {
                                    echo nl2br(htmlspecialchars($preview));
                                }
                                ?>
                            </div>
                        </div>
                        <div class="widget-actions">
                            <button class="btn btn-small btn-edit" onclick="editWidget('left', <?php echo $index; ?>)">
                                ✏️ Bearbeiten
                            </button>
                            <button class="btn btn-small btn-danger" onclick="deleteWidget('left', <?php echo $index; ?>)">
                                🗑️ Löschen
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <button class="btn btn-add-widget" onclick="addWidget('left')">
            ➕ Widget hinzufügen
        </button>
    </div>

    <!-- Rechter Seitenbereich -->
    <div class="sidebar-editor-section">
        <h3>Rechter Seitenbereich</h3>
        <div class="sidebar-widget-list" id="right-sidebar-list">
            <?php if (empty($sidebarRight)): ?>
                <p class="empty-message">Keine Widgets im rechten Seitenbereich. Fügen Sie neue Widgets hinzu.</p>
            <?php else: ?>
                <?php foreach ($sidebarRight as $index => $widget): ?>
                    <div class="sidebar-widget-item" data-side="right" data-index="<?php echo $index; ?>">
                        <div class="widget-preview">
                            <h4><?php echo htmlspecialchars($widget['title'] ?? 'Unbenanntes Widget'); ?></h4>
                            <div class="widget-content-preview">
                                <?php 
                                $preview = $widget['content'] ?? '';
                                $type = $widget['type'] ?? 'html'; // Standardwert 'html'
                                if ($type === 'html') {
                                    echo strip_tags($preview, '<p><br><strong><em><u><a>');
                                } else {
                                    echo nl2br(htmlspecialchars($preview));
                                }
                                ?>
                            </div>
                        </div>
                        <div class="widget-actions">
                            <button class="btn btn-small btn-edit" onclick="editWidget('right', <?php echo $index; ?>)">
                                ✏️ Bearbeiten
                            </button>
                            <button class="btn btn-small btn-danger" onclick="deleteWidget('right', <?php echo $index; ?>)">
                                🗑️ Löschen
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <button class="btn btn-add-widget" onclick="addWidget('right')">
            ➕ Widget hinzufügen
        </button>
    </div>

    <!-- Speicherstatus -->
    <div class="sidebar-save-section">
        <button class="btn btn-save-all" onclick="saveAllSidebars()">
            💾 Alle Änderungen speichern
        </button>
        <span class="save-status" id="save-status"></span>
    </div>
</div>

<!-- Quill CSS -->
<link href="/assets/css/quill/quill.snow.css" rel="stylesheet">

<!-- JavaScript für die Widget-Verwaltung -->
<script>
    // Aktueller Zustand der Sidebars
    let currentSidebars = {
        left: <?php echo json_encode($sidebarLeft); ?>,
        right: <?php echo json_encode($sidebarRight); ?>
    };

    // Aktuell bearbeitetes Widget
    let currentEditing = {
        side: null,
        index: null
    };

    // Quill Editor Initialisierung
    let quillEditor = null;

    // Modal Funktionen
    function openWidgetModal() {
        console.log('openWidgetModal aufgerufen');
        
        try {
            document.getElementById('widgetEditModal').style.display = 'block';
            console.log('Modal angezeigt');
            
            // Quill Editor initialisieren, wenn noch nicht geschehen
            if (!quillEditor) {
                console.log('Initialisiere Quill Editor...');
                
                // Überprüfen, ob das Quill-Element existiert
                const editorElement = document.getElementById('widgetEditor');
                if (!editorElement) {
                    console.error('Quill Editor Element nicht gefunden!');
                    return;
                }
                
                // Überprüfen, ob Quill global verfügbar ist
                if (typeof Quill === 'undefined') {
                    console.error('Quill ist nicht definiert!');
                    return;
                }
                
                quillEditor = new Quill('#widgetEditor', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ 'header': [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            ['blockquote', 'code-block'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            [{ 'script': 'sub'}, { 'script': 'super' }],
                            [{ 'indent': '-1'}, { 'indent': '+1' }],
                            [{ 'direction': 'rtl' }],
                            [{ 'size': ['small', false, 'large', 'huge'] }],
                            [{ 'color': [] }, { 'background': [] }],
                            [{ 'font': [] }],
                            [{ 'align': [] }],
                            ['link', 'image', 'video'],
                            ['clean']
                        ]
                    },
                    placeholder: 'Widget-Inhalt hier eingeben...'
                });
                
                console.log('Quill Editor initialisiert:', quillEditor);
                
                // Quill Inhalt mit Textarea synchronisieren
                quillEditor.on('text-change', function() {
                    document.getElementById('widgetContent').value = quillEditor.root.innerHTML;
                });
            }
        } catch (error) {
            console.error('Fehler beim Öffnen des Modals:', error);
        }
    }

    function closeWidgetModal() {
        document.getElementById('widgetEditModal').style.display = 'none';
    }

    // Widget Funktionen
    function addWidget(side) {
        console.log('addWidget called for side:', side);
        
        // Neues Widget erstellen
        const newWidget = {
            title: 'Neues Widget',
            type: 'html',
            content: '<p>Widget-Inhalt hier eingeben...</p>'
        };
        
        // Widget zum aktuellen Seitenbereich hinzufügen
        currentSidebars[side].push(newWidget);
        console.log('Added new widget. Total widgets:', currentSidebars[side].length);
        
        // Widget-Liste aktualisieren
        updateWidgetList(side);
        
        // Änderungen sofort auf dem Server speichern
        console.log('Saving new widget to server...');
        saveAllSidebars();
        
        // Neues Widget direkt bearbeiten
        const index = currentSidebars[side].length - 1;
        console.log('Editing new widget at index:', index);
        editWidget(side, index);
    }

    function editWidget(side, index) {
        if (!currentSidebars[side] || !currentSidebars[side][index]) {
            alert('Widget nicht gefunden!');
            return;
        }
        
        currentEditing.side = side;
        currentEditing.index = index;
        
        const widget = currentSidebars[side][index];
        
        // Modal öffnen und Daten laden
        document.getElementById('widgetTitle').value = widget.title || 'Neues Widget';
        document.getElementById('widgetType').value = widget.type || 'html';
        
        // Quill Editor Inhalt setzen
        if (widget.type === 'html') {
            if (quillEditor) {
                quillEditor.root.innerHTML = widget.content || '<p>Widget-Inhalt hier eingeben...</p>';
                document.getElementById('widgetContent').value = widget.content || '<p>Widget-Inhalt hier eingeben...</p>';
            }
        } else {
            if (quillEditor) {
                quillEditor.root.innerHTML = '<p>' + (widget.content || 'Widget-Inhalt hier eingeben...') + '</p>';
                document.getElementById('widgetContent').value = widget.content || 'Widget-Inhalt hier eingeben...';
            }
        }
        
        openWidgetModal();
    }

    function deleteWidget(side, index) {
        console.log('deleteWidget called with:', side, index);
        
        if (typeof currentSidebars[side] === 'undefined') {
            console.error('Side not found:', side);
            alert('Seitenbereich nicht gefunden: ' + side);
            return;
        }
        
        if (typeof currentSidebars[side][index] === 'undefined') {
            console.error('Widget not found at index:', index);
            alert('Widget nicht gefunden');
            return;
        }
        
        if (confirm('Möchten Sie dieses Widget wirklich löschen?')) {
            try {
                console.log('Deleting widget:', side, index);
                console.log('Widget to delete:', currentSidebars[side][index]);
                console.log('Before deletion:', currentSidebars[side].length, 'widgets');
                
                // Widget entfernen
                const deletedWidget = currentSidebars[side].splice(index, 1);
                console.log('Deleted widget:', deletedWidget);
                
                console.log('After deletion:', currentSidebars[side].length, 'widgets');
                
                // Widget-Liste aktualisieren
                console.log('Updating widget list...');
                updateWidgetList(side);
                
                // Änderungen sofort auf dem Server speichern
                console.log('Saving changes to server...');
                saveAllSidebars();
                
            } catch (error) {
                console.error('Fehler beim Löschen des Widgets:', error);
                alert('Fehler beim Löschen des Widgets: ' + error.message);
            }
        } else {
            console.log('Widget deletion cancelled by user');
        }
    }

    function saveWidgetChanges() {
        const side = currentEditing.side;
        const index = currentEditing.index;
        
        console.log('saveWidgetChanges called for:', side, index);
        
        if (side === null || index === null) {
            alert('Kein Widget zum Speichern ausgewählt!');
            return;
        }
        
        const widget = currentSidebars[side][index];
        
        // Daten aus dem Formular lesen
        widget.title = document.getElementById('widgetTitle').value;
        widget.type = document.getElementById('widgetType').value;
        
        // Inhalt aus Quill oder Textarea
        widget.content = document.getElementById('widgetContent').value;
        
        console.log('Widget updated:', widget);
        
        // Modal schließen
        closeWidgetModal();
        
        // Liste aktualisieren
        updateWidgetList(side);
        
        // Änderungen sofort auf dem Server speichern
        console.log('Saving widget changes to server...');
        saveAllSidebars();
    }

    function updateWidgetList(side) {
        try {
            const containerId = side + '-sidebar-list';
            const container = document.getElementById(containerId);
            
            if (!container) {
                console.error('Container not found:', containerId);
                return;
            }
            
            console.log('Updating widget list for', side, 'with', currentSidebars[side]?.length || 0, 'widgets');
            
            if (!currentSidebars[side] || currentSidebars[side].length === 0) {
                container.innerHTML = '<p class="empty-message">Keine Widgets in diesem Seitenbereich. Fügen Sie neue Widgets hinzu.</p>';
                return;
            }
            
            let html = '';
            currentSidebars[side].forEach((widget, index) => {
                const type = widget.type || 'html'; // Standardwert
                const title = widget.title || 'Unbenanntes Widget';
                const content = widget.content || '';
                const preview = type === 'html' 
                    ? stripTags(content, '<p><br><strong><em><u><a>')
                    : nl2br(escapeHtml(content));
                
                console.log('Adding widget to list:', title, 'index:', index);
                
                html += `
                <div class="sidebar-widget-item" data-side="${side}" data-index="${index}">
                    <div class="widget-preview">
                        <h4>${escapeHtml(title)}</h4>
                        <div class="widget-content-preview">${preview}</div>
                    </div>
                    <div class="widget-actions">
                        <button class="btn btn-small btn-edit" onclick="window.deleteWidget && deleteWidget('${side}', ${index})">
                            ✏️ Bearbeiten
                        </button>
                        <button class="btn btn-small btn-danger" onclick="window.deleteWidget && deleteWidget('${side}', ${index})">
                            🗑️ Löschen
                        </button>
                    </div>
                </div>
            `;
            });
            
            container.innerHTML = html;
            console.log('Widget list updated successfully. HTML length:', html.length);
            
        } catch (error) {
            console.error('Fehler beim Aktualisieren der Widget-Liste:', error);
            alert('Fehler beim Aktualisieren der Widget-Liste: ' + error.message);
        }
    }

    function saveAllSidebars() {
        console.log('saveAllSidebars called');
        console.log('Left sidebar widgets:', currentSidebars.left);
        console.log('Right sidebar widgets:', currentSidebars.right);
        
        const statusElement = document.getElementById('save-status');
        statusElement.textContent = 'Speichern...';
        statusElement.style.color = 'blue';
        
        fetch('/admin/save-sidebars', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                sidebarLeft: currentSidebars.left,
                sidebarRight: currentSidebars.right
            })
        })
        .then(response => {
            console.log('Server response received:', response);
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Save result:', data);
            if (data.success) {
                showSaveStatus('Seitenbereiche erfolgreich gespeichert!', 'green');
            } else {
                console.error('Server error:', data.error || 'Unknown error');
                showSaveStatus('Fehler beim Speichern: ' + (data.error || 'Unbekannter Fehler'), 'red');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showSaveStatus('Fehler beim Speichern: ' + error.message, 'red');
        });
    }

    function showSaveStatus(message, color) {
        console.log('showSaveStatus:', message);
        const statusElement = document.getElementById('save-status');
        if (statusElement) {
            statusElement.textContent = message;
            statusElement.style.color = color;
        } else {
            console.error('Status element not found');
        }
    }

    // Hilfsfunktionen
    function stripTags(html, allowedTags) {
        const tmp = document.createElement('div');
        tmp.innerHTML = html;
        
        const tags = tmp.getElementsByTagName('*');
        const unwantedTags = [];
        
        for (let i = 0; i < tags.length; i++) {
            if (allowedTags.indexOf(tags[i].nodeName.toLowerCase()) === -1) {
                unwantedTags.push(tags[i]);
            }
        }
        
        for (let i = 0; i < unwantedTags.length; i++) {
            unwantedTags[i].outerHTML = unwantedTags[i].innerHTML;
        }
        
        return tmp.innerHTML;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // JavaScript-Version von PHP's nl2br
    function nl2br(text) {
        return text.replace(/\n/g, '<br>');
    }

    // Modal schließen beim Klick außerhalb
    window.onclick = function(event) {
        const modal = document.getElementById('widgetEditModal');
        if (event.target === modal) {
            closeWidgetModal();
        }
    }
</script>

<!-- Quill JS -->
<script src="/assets/js/quill/quill.min.js"></script>

<style>
    /* Modal Stile */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.5);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 800px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }

    .modal-body {
        margin-bottom: 20px;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 10px;
        border-top: 1px solid #eee;
    }

    .close {
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover {
        color: black;
    }

    /* Widget Liste Stile */
    .sidebar-management-container {
        display: flex;
        flex-direction: column;
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .sidebar-editor-section {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .sidebar-widget-list {
        display: grid;
        gap: 1em;
        margin-bottom: 1.25em;
    }

    .sidebar-widget-item {
        border: 0.0625rem solid #8A2BE2; /* deepviolet */
        border-radius: 1.25rem;
        overflow: hidden;
        transition: all 0.2s;
        background: transparent;
    }

    .sidebar-widget-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-color: #ccc;
    }

    .widget-preview {
        padding: 1em;
        background: transparent;
    }

    .widget-preview h4 {
        margin-top: 0;
        margin-bottom: 10px;
        color: #333;
    }

    .widget-content-preview {
        color: #666;
        max-height: 100px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .widget-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.5em;
        padding: 0.625em 1em;
        background: transparent;
        border-top: 0.0625rem solid #8A2BE2;
    }

    .btn-edit {
        background-color: #4CAF50;
        color: white;
    }

    .btn-danger {
        background-color: #f44336;
        color: white;
    }

    .btn-add-widget {
        background-color: #2196F3;
        color: white;
        padding: 0.625em 1em;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
        font-size: 0.875rem;
    }

    .btn-add-widget:hover {
        background-color: #0b7dda;
    }

    .btn-save-all {
        background-color: #4CAF50;
        color: white;
        padding: 0.75em 1.5em;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
        font-size: 1rem;
        font-weight: bold;
    }

    .btn-save-all:hover {
        background-color: #45a049;
    }

    .empty-message {
        color: #999;
        font-style: italic;
        text-align: center;
        padding: 20px;
    }

    .save-status {
        margin-left: 15px;
        font-weight: bold;
    }

    .sidebar-save-section {
        text-align: center;
        margin-top: 20px;
    }

    /* Formular Stile */
    .form-group {
        margin-bottom: 1em;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .form-control {
        width: 100%;
        padding: 0.5em;
        border: 0.0625rem solid #ddd;
        border-radius: 0.25rem;
        box-sizing: border-box;
    }

    select.form-control {
        padding: 0.625em;
    }

    /* Quill Editor Stile */
    .ql-container {
        border: 0.0625rem solid #ddd;
        border-radius: 0.25rem;
        min-height: 12.5rem;
    }

    .ql-toolbar {
        border: 0.0625rem solid #ddd;
        border-bottom: none;
        border-radius: 0.25rem 0.25rem 0 0;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .modal-content {
            width: 95%;
            margin: 10px auto;
        }

        .widget-actions {
            flex-direction: column;
            gap: 5px;
        }

        .btn-small {
            width: 100%;
        }
    }
</style>