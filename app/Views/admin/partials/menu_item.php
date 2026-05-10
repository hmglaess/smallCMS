<?php
/**
 * @var array $item
 * @var int $depth
 */
$depth = $depth ?? 0;
?>

<div class="menu-item" style="margin-left: <?php echo $depth * 20; ?>px;">
    <div class="menu-item-flex">
        <span class="handle">☰</span>
        <div class="menu-item-inputs">
            <input type="text" placeholder="Menütitel" 
                   class="menu-item-input" style="width: 200px;" 
                   value="<?php echo htmlspecialchars($item['title']); ?>">
            <select class="menu-item-select" 
                    onchange="updateMenuItemPageId(this)">
                <option value="">-- Seite wählen --</option>
                <?php if (isset($availablePageIds) && is_array($availablePageIds)): ?>
                    <?php foreach ($availablePageIds as $pageId): ?>
                        <option value="<?php echo htmlspecialchars($pageId); ?>" 
                                <?php echo $item['id'] === $pageId ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($pageId); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <input type="hidden" name="page_id" value="<?php echo htmlspecialchars($item['id']); ?>">
            <input type="number" placeholder="Position" 
                   class="menu-item-position" 
                   value="<?php echo htmlspecialchars($item['position']); ?>">
        </div>
        <div class="menu-item-controls">
            <button class="btn btn-secondary menu-item-btn" 
                    onclick="addSubmenu(this)">
                ➕ Untermenü
            </button>
            <button class="btn btn-danger menu-item-btn" 
                    onclick="removeMenuItem(this)">
                🗑️
            </button>
        </div>
    </div>
    
    <?php if (!empty($item['submenu']) && $depth < 2): // Limit to 1 level of nesting to prevent memory issues ?>
        <div style="margin-top: 10px; padding-left: 20px; border-left: 2px solid #eee;">
            <?php foreach ($item['submenu'] as $subItem): ?>
                <div class="menu-item" style="margin-left: 20px;">
                    <div style="display: flex; align-items: center;">
                        <span class="handle">☰</span>
                        <div style="flex: 1;">
                            <input type="text" placeholder="Menütitel" 
                                   style="width: 200px; margin-right: 10px; padding: 5px;" 
                                   value="<?php echo htmlspecialchars($subItem['title']); ?>">
                            <select style="width: 150px; margin-right: 10px; padding: 5px;" 
                                    onchange="updateMenuItemPageId(this)">
                                <option value="">-- Seite wählen --</option>
                                <?php if (isset($availablePageIds) && is_array($availablePageIds)): ?>
                                    <?php foreach ($availablePageIds as $pageId): ?>
                                        <option value="<?php echo htmlspecialchars($pageId); ?>" 
                                                <?php echo $subItem['id'] === $pageId ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($pageId); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <input type="hidden" name="page_id" value="<?php echo htmlspecialchars($subItem['id']); ?>">
                            <input type="number" placeholder="Position" 
                                   class="menu-item-position" 
                                   value="<?php echo htmlspecialchars($subItem['position']); ?>">
                        </div>
                        <div class="menu-item-controls">
                            <button class="btn btn-danger menu-item-btn" 
                                    onclick="removeMenuItem(this)">
                                🗑️
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>