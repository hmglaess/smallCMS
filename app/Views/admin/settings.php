<?php
/**
 * @var array $settings
 */
?>

<h2>Einstellungen</h2>

<div style="margin: 20px 0; padding: 15px; background-color: #e3f2fd; border-radius: 5px; border-left: 4px solid #2196f3;">
    <strong>ℹ️ Informationen</strong>
    <p style="margin: 10px 0 0; font-size: 0.9em;">
        Änderungen werden gespeichert, wenn Sie auf "Einstellungen speichern" klicken.
    </p>
</div>

<form style="margin-top: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h3>Allgemeine Einstellungen</h3>
        
        <div class="form-group">
            <label for="site-title">Website-Titel</label>
            <input type="text" id="site-title" name="site_title" 
                   value="<?php echo htmlspecialchars($settings['site']['title'] ?? 'DiscoFox Berlin'); ?>" 
                   placeholder="Website-Titel">
        </div>
        
        <div class="form-group">
            <label for="site-description">Website-Beschreibung</label>
            <textarea id="site-description" name="site_description" 
                      placeholder="Website-Beschreibung">
<?php echo htmlspecialchars($settings['site']['description'] ?? 'Discofox lernen und tanzen in Berlin - Workshops und Events'); ?>
            </textarea>
        </div>
    </div>
    
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h3>Theme-Einstellungen</h3>
        
        <div class="form-group">
            <label for="primary-color">Primärfarbe</label>
            <input type="color" id="primary-color" name="primary_color" 
                   value="<?php echo htmlspecialchars($settings['theme']['primary_color'] ?? '#3498db'); ?>">
        </div>
        
        <div class="form-group">
            <label for="secondary-color">Sekundärfarbe</label>
            <input type="color" id="secondary-color" name="secondary_color" 
                   value="<?php echo htmlspecialchars($settings['theme']['secondary_color'] ?? '#2c3e50'); ?>">
        </div>
    </div>
    
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h3>SEO-Einstellungen</h3>
        
        <div class="form-group">
            <label for="meta-keywords">Meta-Keywords (durch Komma getrennt)</label>
            <input type="text" id="meta-keywords" name="meta_keywords" 
                   value="<?php echo htmlspecialchars($settings['seo']['keywords'] ?? 'Discofox, Berlin, Tanzen, Workshop, Party'); ?>" 
                   placeholder="Meta-Keywords">
        </div>
        
        <div class="form-group">
            <label for="meta-author">Meta-Autor</label>
            <input type="text" id="meta-author" name="meta_author" 
                   value="<?php echo htmlspecialchars($settings['seo']['author'] ?? 'DiscoFox Berlin'); ?>" 
                   placeholder="Meta-Autor">
        </div>
    </div>
    
    <div style="margin-top: 30px; padding: 15px; background-color: #e8f5e9; border-radius: 5px;">
        <button type="submit" class="btn" style="background-color: #2e7d32;">
            💾 Einstellungen speichern
        </button>
        <span style="margin-left: 10px; color: #2e7d32;" id="settings-save-status"></span>
    </div>
</form>

<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Collect form data
        const formData = {
            site: {
                title: document.getElementById('site-title').value,
                description: document.getElementById('site-description').value
            },
            theme: {
                primary_color: document.getElementById('primary-color').value,
                secondary_color: document.getElementById('secondary-color').value
            },
            seo: {
                keywords: document.getElementById('meta-keywords').value,
                author: document.getElementById('meta-author').value
            }
        };
        
        // Send to server
        fetch('/admin/settings/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            const statusElement = document.getElementById('settings-save-status');
            if (data.success) {
                statusElement.textContent = data.message;
                statusElement.style.color = '#2e7d32';
                setTimeout(() => {
                    statusElement.textContent = '';
                }, 3000);
            } else {
                statusElement.textContent = 'Fehler: ' + data.message;
                statusElement.style.color = '#c62828';
            }
        })
        .catch(error => {
            const statusElement = document.getElementById('settings-save-status');
            statusElement.textContent = 'Netzwerkfehler: ' + error.message;
            statusElement.style.color = '#c62828';
        });
    });
</script>