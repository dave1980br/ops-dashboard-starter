<?php
declare(strict_types=1);

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$status = (string)($filters['status'] ?? '');
$priority = (string)($filters['priority'] ?? '');
$q = (string)($filters['q'] ?? '');

function buildQuery(array $overrides = []): string {
    $base = [
        'status' => $_GET['status'] ?? '',
        'priority' => $_GET['priority'] ?? '',
        'q' => $_GET['q'] ?? '',
        'page' => $_GET['page'] ?? '',
        'msg' => $_GET['msg'] ?? '',
    ];

    foreach ($base as $k => $v) {
        if ($v === '' || $v === null) unset($base[$k]);
    }

    foreach ($overrides as $k => $v) {
        if ($v === '' || $v === null) {
            unset($base[$k]);
        } else {
            $base[$k] = $v;
        }
    }

    $qs = http_build_query($base);
    return $qs ? ('?' . $qs) : '';
}

$msgMap = [
    'created' => 'Task created.',
    'updated' => 'Task updated.',
    'deleted' => 'Task deleted.',
    'notfound' => 'Task not found.',
    'none_selected' => 'Select at least one task.',
    'bad_status' => 'Invalid status.',
    'bad_action' => 'Invalid bulk action.',
    'bulk_updated' => 'Bulk update applied.',
];
$flash = $msgMap[$msg] ?? '';

$exportUrl = APP_BASE_PATH . '/tasks/export' . buildQuery(['page' => '', 'msg' => '']);
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h3 mb-0">Tasks</h1>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="<?= $exportUrl ?>">Export CSV</a>
    <a class="btn btn-primary" href="<?= APP_BASE_PATH ?>/tasks/create">Create Task</a>
  </div>
</div>

<?php if ($flash !== ''): ?>
  <div class="alert alert-success"><?= h($flash) ?></div>
<?php endif; ?>

<form method="get" action="<?= APP_BASE_PATH ?>/tasks" class="card mb-3">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-12 col-md-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
          <option value="">All</option>
          <?php foreach ($statuses as $s): ?>
            <option value="<?= h($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= h($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12 col-md-3">
        <label class="form-label">Priority</label>
        <select class="form-select" name="priority">
          <option value="">All</option>
          <?php foreach ($priorities as $p): ?>
            <option value="<?= h($p) ?>" <?= $priority === $p ? 'selected' : '' ?>><?= h($p) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label">Search</label>
        <input class="form-control" type="text" name="q" value="<?= h($q) ?>" placeholder="Title or description...">
      </div>

      <div class="col-12 col-md-2 d-grid">
        <button class="btn btn-outline-primary" type="submit">Filter</button>
      </div>
    </div>

    <div class="mt-2">
      <a href="<?= APP_BASE_PATH ?>/tasks" class="small">Clear filters</a>
    </div>
  </div>
</form>

<form method="post" action="<?= APP_BASE_PATH ?>/tasks/bulk" class="card">
  <input type="hidden" name="_csrf" value="<?= h($csrf) ?>">

  <div class="card-body d-flex flex-wrap gap-2 align-items-center">
    <div class="me-2 fw-semibold">Bulk actions:</div>

    <input type="hidden" name="action" value="set_status">

    <select class="form-select" name="new_status" style="max-width: 220px;">
      <?php foreach ($statuses as $s): ?>
        <option value="<?= h($s) ?>"><?= h($s) ?></option>
      <?php endforeach; ?>
    </select>

    <button type="submit" class="btn btn-sm btn-outline-secondary">Set status</button>

    <div class="ms-auto small text-muted">
      Showing <?= (int)count($tasks) ?> of <?= (int)$total ?>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead class="table-light">
        <tr>
          <th style="width: 44px;">
            <input class="form-check-input" type="checkbox" id="checkAll">
          </th>
          <th>Title</th>
          <th style="width: 120px;">Status</th>
          <th style="width: 110px;">Priority</th>
          <th style="width: 140px;">Due</th>
          <th style="width: 160px;">Assigned</th>
          <th style="width: 180px;">Created</th>
          <th style="width: 150px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($tasks) === 0): ?>
          <tr>
            <td colspan="8" class="text-center text-muted py-4">No tasks found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($tasks as $t): ?>
            <tr>
              <td>
                <input class="form-check-input rowCheck" type="checkbox" name="ids[]" value="<?= (int)$t['task_id'] ?>">
              </td>
              <td>
                <div class="fw-semibold"><?= h((string)$t['title']) ?></div>
                <div class="small text-muted">Created by <?= h((string)$t['created_by_name']) ?></div>
              </td>
              <td><span class="badge bg-secondary"><?= h((string)$t['status']) ?></span></td>
              <td><?= h((string)$t['priority']) ?></td>
              <td><?= h((string)($t['due_date'] ?? '')) ?></td>
              <td><?= h((string)($t['assigned_to_name'] ?? '')) ?></td>
              <td class="small text-muted">
                <?= h((string)$t['created_at']) ?>
              </td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="<?= APP_BASE_PATH ?>/tasks/edit?id=<?= (int)$t['task_id'] ?>">Edit</a>

                <form method="post" action="<?= APP_BASE_PATH ?>/tasks/delete" class="d-inline" onsubmit="return confirm('Delete this task?');">
                  <input type="hidden" name="_csrf" value="<?= h($csrf) ?>">
                  <input type="hidden" name="task_id" value="<?= (int)$t['task_id'] ?>">
                  <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</form>

<?php if ($totalPages > 1): ?>
  <nav class="mt-3">
    <ul class="pagination">
      <?php
        $prev = $page - 1;
        $next = $page + 1;
      ?>
      <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= APP_BASE_PATH ?>/tasks<?= buildQuery(['page' => $prev <= 1 ? '' : (string)$prev]) ?>">Previous</a>
      </li>

      <?php
        $start = max(1, $page - 3);
        $end = min($totalPages, $page + 3);
        for ($i = $start; $i <= $end; $i++):
      ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
          <a class="page-link" href="<?= APP_BASE_PATH ?>/tasks<?= buildQuery(['page' => $i === 1 ? '' : (string)$i]) ?>"><?= (int)$i ?></a>
        </li>
      <?php endfor; ?>

      <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= APP_BASE_PATH ?>/tasks<?= buildQuery(['page' => (string)$next]) ?>">Next</a>
      </li>
    </ul>
  </nav>
<?php endif; ?>

<script>
  (function () {
    const checkAll = document.getElementById('checkAll');
    const rowChecks = document.querySelectorAll('.rowCheck');

    if (checkAll) {
      checkAll.addEventListener('change', function () {
        rowChecks.forEach(cb => cb.checked = checkAll.checked);
      });
    }
  })();
</script>