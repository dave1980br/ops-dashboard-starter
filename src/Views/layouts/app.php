<?php
declare(strict_types=1);

$user = Auth::user();

$uri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($uri, PHP_URL_PATH) ?? '';

function navActive(string $needle, string $path): string {
    return (strpos($path, $needle) !== false) ? 'active' : '';
}
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars(APP_NAME) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= APP_BASE_PATH ?>/dashboard"><?= htmlspecialchars(APP_NAME) ?></a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link <?= navActive('/dashboard', $path) ?>" href="<?= APP_BASE_PATH ?>/dashboard">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= navActive('/tasks', $path) ?>" href="<?= APP_BASE_PATH ?>/tasks">Tasks</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= navActive('/audit', $path) ?>" href="<?= APP_BASE_PATH ?>/audit">Audit</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= navActive('/docs', $path) ?>" href="<?= APP_BASE_PATH ?>/docs">Docs</a>
        </li>
      </ul>

      <div class="d-flex align-items-center text-white">
        <?php if ($user): ?>
          <span class="me-3"><?= htmlspecialchars((string)$user['display_name']) ?></span>
          <form method="post" action="<?= APP_BASE_PATH ?>/logout" class="m-0">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
            <button class="btn btn-sm btn-outline-light" type="submit">Logout</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<div class="container py-4">
  <?= $content ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>