<?php
/**
 * @var string $content
 * @var string $title
 * @var array $adminMenu
 */
?><!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> | Admin | DiscoFox Berlin</title>
    <link rel="stylesheet" href="/assets/css/dfbde.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="stylesheet" href="/assets/css/quill/quill.snow.css">
    <script src="/assets/js/admin.js"></script>
    <script src="/assets/js/quill/quill.min.js"></script>

</head>
<body>
    <div class="admin-container">
        <div class="admin-sidebar">
            <div class="logo">💃 discofoxberlin.dance</div>
            <ul class="admin-nav">
                <?php foreach ($adminMenu as $item): ?>
                    <li>
                        <a href="<?php echo htmlspecialchars($item['url']); ?>" 
                           <?php echo (str_contains($_SERVER['REQUEST_URI'] ?? '', $item['url']) ? 'class="active"' : ''); ?>>
                            <span class="icon"><?php echo $item['icon']; ?></span>
                            <?php echo htmlspecialchars($item['title']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="admin-main">
            <div class="admin-header">
                <h1><?php echo htmlspecialchars($title); ?></h1>
                <div>
                    <!-- User info will go here -->
                    <span style="color: #7f8c8d;">Entwicklungsmodus</span>
                </div>
            </div>
            
            <div class="admin-content">
                <?php echo $content; ?>
            </div>
        </div>
    </div>
</body>
</html>