<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'smallCMS') ?></title>
    <style>
        body { font-family: sans-serif; line-height: 1.5; padding: 2em; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <h1><?= htmlspecialchars($title ?? 'Welcome') ?></h1>
    <p><?= htmlspecialchars($content ?? '') ?></p>
</body>
</html>
