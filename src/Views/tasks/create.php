<?php
declare(strict_types=1);

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$title = (string)($form['title'] ?? '');
$description = (string)($form['description'] ?? '');
$status = (string)($form['status'] ?? 'new');
$priority = (string)($form['priority'] ?? 'normal');
$assignedTo = (string)($form['assigned_to_user_id'] ?? '');
$dueDate = (string)($form['due_date'] ?? '');
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h3 mb-0">Create Task</h1>
  <a class="btn btn-outline-secondary" href="<?= APP_BASE_PATH ?>/tasks">Back</a>
</div>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= h((string)$error) ?></div>
<?php endif; ?>

<form method="post" action="<?= APP_BASE_PATH ?>/tasks/create" class="card">
  <input type="hidden" name="_csrf" value="<?= h((string)$csrf) ?>">

  <div class="card-body">
    <div class="mb-3">
      <label class="form-label">Title</label>
      <input class="form-control" type="text" name="title" value="<?= h($title) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea class="form-control" name="description" rows="4"><?= h($description) ?></textarea>
    </div>

    <div class="row g-3">
      <div class="col-12 col-md-4">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
          <?php foreach ($statuses as $s): ?>
            <option value="<?= h($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= h($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label">Priority</label>
        <select class="form-select" name="priority">
          <?php foreach ($priorities as $p): ?>
            <option value="<?= h($p) ?>" <?= $priority === $p ? 'selected' : '' ?>><?= h($p) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label">Due date</label>
        <input class="form-control" type="date" name="due_date" value="<?= h($dueDate) ?>">
      </div>
    </div>

    <div class="mt-3">
      <label class="form-label">Assigned to</label>
      <select class="form-select" name="assigned_to_user_id">
        <option value="">Unassigned</option>
        <?php foreach ($users as $u): ?>
          <option value="<?= (int)$u['user_id'] ?>" <?= $assignedTo !== '' && (int)$assignedTo === (int)$u['user_id'] ? 'selected' : '' ?>>
            <?= h((string)$u['display_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="card-footer d-flex justify-content-end gap-2">
    <a class="btn btn-outline-secondary" href="<?= APP_BASE_PATH ?>/tasks">Cancel</a>
    <button class="btn btn-primary" type="submit">Create</button>
  </div>
</form>