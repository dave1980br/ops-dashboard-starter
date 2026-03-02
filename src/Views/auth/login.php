<?php
declare(strict_types=1);
?>
<div class="container py-5" style="max-width: 420px;">
  <div class="card shadow-sm">
    <div class="card-body p-4">
      <h1 class="h4 mb-3">Sign in</h1>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" action="<?= APP_BASE_PATH ?>/login">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input class="form-control" type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Password</label>
          <input class="form-control" type="password" name="password" required>
        </div>

        <button class="btn btn-primary w-100" type="submit">Sign in</button>
      </form>
    </div>
  </div>
</div>