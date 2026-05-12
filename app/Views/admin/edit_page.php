<?php
/**
 * @var \App\Models\Page $page
 * @var string $pageId
 */
?>

<h2>Seite bearbeiten: <?php echo htmlspecialchars($page->getTitle()); ?></h2>

<div class="edit-page-container">
    <form id="editPageForm" class="edit-page-form">
        <div class="form-group">
            <label for="page-title">Seitentitel</label>
            <input type="text" id="page-title" name="title" 
                   value="<?php echo htmlspecialchars($page->getTitle()); ?>" 
                   class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="page-content">Seiteninhalt</label>
            <div id="editor-container" style="height: 400px;">
                <?php echo $page->getContent(); ?>
            </div>
            <textarea id="page-content" name="content" style="display: none;"><?php echo htmlspecialchars($page->getContent()); ?></textarea>
        </div>
        
        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                Abbrechen
            </button>
            <button type="button" class="btn btn-primary" onclick="savePage()">
                Speichern
            </button>
        </div>
    </form>
</div>

<script>
// Initialize Quill editor when DOM is loaded
let quillEditor;

document.addEventListener('DOMContentLoaded', function() {
    // Check if Quill is available
    if (typeof Quill === 'undefined') {
        console.error('Quill editor is not loaded!');
        alert('Fehler: Der Quill-Editor konnte nicht geladen werden. Bitte laden Sie die Seite neu.');
        return;
    }

    // Initialize Quill editor
    try {
        quillEditor = new Quill('#editor-container', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    ['link', 'image'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'align': [] }],
                    ['clean']
                ]
            },
            placeholder: 'Geben Sie den Seiteninhalt ein...',
        });

        console.log('Quill editor initialized successfully');

        // Set initial content from the hidden textarea
        const initialContent = document.getElementById('page-content').value;
        if (initialContent) {
            quillEditor.clipboard.dangerouslyPasteHTML(initialContent);
        }

        // Update hidden textarea when editor content changes
        quillEditor.on('text-change', function() {
            const htmlContent = quillEditor.root.innerHTML;
            document.getElementById('page-content').value = htmlContent;
            console.log('Editor content updated, length:', htmlContent.length);
        });

        // Custom image handler
        const toolbar = quillEditor.getModule('toolbar');
        toolbar.addHandler('image', function() {
            const range = quillEditor.getSelection();
            
            // Fetch existing images from the server
            fetch('/admin/get-images')
                .then(response => response.json())
                .then(images => {
                    if (images.length === 0) {
                        alert('Keine Bilder gefunden.');
                        return;
                    }
                    
                    // Create a modal to display the images
                    const modal = document.createElement('div');
                    modal.style.position = 'fixed';
                    modal.style.top = '50%';
                    modal.style.left = '50%';
                    modal.style.transform = 'translate(-50%, -50%)';
                    modal.style.background = 'white';
                    modal.style.padding = '20px';
                    modal.style.boxShadow = '0 0 10px rgba(0,0,0,0.1)';
                    modal.style.zIndex = '1000';
                    
                    const closeButton = document.createElement('button');
                    closeButton.innerText = 'Schließen';
                    closeButton.onclick = function() {
                        document.body.removeChild(modal);
                    };
                    modal.appendChild(closeButton);
                    
                    // Display the images
                    images.forEach(image => {
                        const imgElement = document.createElement('img');
                        imgElement.src = image.url;
                        imgElement.style.width = '100px';
                        imgElement.style.margin = '10px';
                        imgElement.onclick = function() {
                            quillEditor.insertEmbed(range.index, 'image', image.url, Quill.sources.USER);
                            document.body.removeChild(modal);
                        };
                        modal.appendChild(imgElement);
                    });
                    
                    document.body.appendChild(modal);
                })
                .catch(error => {
                    console.error('Error fetching images:', error);
                    alert('Fehler beim Laden der Bilder.');
                });
        });

    } catch (error) {
        console.error('Failed to initialize Quill editor:', error);
        alert('Fehler beim Initialisieren des Editors: ' + error.message);
    }
});

/**
 * Create a new page with user input
 */
function createNewPage() {
    const pageTitle = prompt('Bitte geben Sie den Titel der neuen Seite ein:');
    
    if (!pageTitle || pageTitle.trim() === '') {
        alert('Bitte geben Sie einen gültigen Seitentitel ein.');
        return;
    }
    
    // Redirect to the new page editor
    window.location.href = '/admin/pages/new?title=' + encodeURIComponent(pageTitle.trim());
}

/**
 * Save the page content
 */
function savePage() {
    const title = document.getElementById('page-title').value.trim();
    const content = quillEditor.root.innerHTML;
    
    if (!title) {
        alert('Bitte geben Sie einen Seitentitel ein.');
        return;
    }
    
    const pageId = '<?php echo htmlspecialchars($pageId); ?>';
    
    // Debug: Log the content to see what's being sent
    console.log('Saving page with title:', title);
    console.log('Content length:', content.length);
    console.log('Content preview:', content.substring(0, 100));
    
    // Get the ID from the URL if it's a new page
    const urlParams = new URLSearchParams(window.location.search);
    const newPageId = urlParams.get('id');
    const actualPageId = pageId === 'new' && newPageId ? newPageId : pageId;
    
    console.log('Using page ID:', actualPageId);
    
    fetch('/admin/pages/save/' + encodeURIComponent(actualPageId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            title: title,
            content: content
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        
        // First check if response has content
        return response.text().then(text => {
            console.log('Raw response:', text);
            
            if (!text.trim()) {
                throw new Error('Empty response from server');
            }
            
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Failed to parse JSON:', e);
                console.error('Response text:', text);
                throw new Error('Invalid JSON response from server');
            }
        });
    })
    .then(data => {
        if (data.success) {
            alert('Seite erfolgreich gespeichert!');
            // Redirect back to pages list
            window.location.href = '/admin/pages';
        } else {
            alert('Fehler beim Speichern: ' + (data.message || 'Unbekannter Fehler'));
        }
    })
    .catch(error => {
        console.error('Error saving page:', error);
        alert('Fehler beim Speichern der Seite: ' + error.message);
    });
}
</script>

<style>
.edit-page-container {
    max-width: 1000px;
    margin: 0 auto;
}

.edit-page-form {
    background-color: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1em;
}

.form-actions {
    margin-top: 30px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

#editor-container {
    background-color: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 10px;
}

.ql-toolbar {
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
}

.ql-container {
    border-bottom-left-radius: 4px;
    border-bottom-right-radius: 4px;
    min-height: 300px;
}
</style>