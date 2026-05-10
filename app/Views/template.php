<?php
/**
 * @var string $title
 * @var string $content
 * @var string $siteName
 */
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" type="text/css" href="/assets/css/dfbde.css">
    <script src="/assets/js/htmx.min.js"></script>
    <script src="/assets/js/dfbde.js" defer></script>
</head>
<body>
    <div class="container">
        <header>
            <?php include __DIR__ . '/components/navigation.php'; ?>
        </header>

        <main class="has-sidebar" id="mainContent">
            <?php include __DIR__ . '/components/sidebar_left.php'; ?>

            <section class="content">
                <div id="main-content">
                    <!-- Hauptinhalt wird dynamisch geladen -->
                    <?= $content ?>
                </div>
            </section>

            <?php include __DIR__ . '/components/sidebar_right.php'; ?>
        </main>

        <?php include __DIR__ . '/components/footer.php'; ?>
    </div>


</body>
</html>