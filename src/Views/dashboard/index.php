<?php
declare(strict_types=1);

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$counts = $counts ?? [];
$recentTasks = $recentTasks ?? [];
$recentAudit = $recentAudit ?? [];

function badgeClass(string $status): string {
    return match ($status) {
        'blocked' => 'bg-danger',
        'in_progress' => 'bg-primary',
        'new' => 'bg-secondary',
        'done' => 'bg-success',
        default => 'bg-secondary',
    };
}

function priorityLabel(string $p): string {
    return match ($p) {
        'high' => 'High',
        'normal' => 'Normal',
        'low' => 'Low',
        default => $p,
    };
}
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h3 mb-1">Dashboard</h1>
    <div class="text-muted">Ops dashboard demo: tasks queue, CSV exports, and audit logging.</div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="<?= APP_BASE_PATH ?>/audit">View Audit</a>
    <a class="btn btn-primary" href="<?= APP_BASE_PATH ?>/tasks">View Tasks</a>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-lg-2">
    <div class="card">
      <div class="card-body">
        <div class="text-muted small">Total</div>
        <div class="h4 mb-0"><?= (int)($counts['total'] ?? 0) ?></div>
      </div>
    </div>
  </div>

  <div class="col-6 col-lg-2">
    <div class="card">
      <div class="card-body">
        <div class="text-muted small">New</div>
        <div class="h4 mb-0"><?= (int)($counts['new'] ?? 0) ?></div>
      </div>
    </div>
  </div>

  <div class="col-6 col-lg-2">
    <div class="card">
      <div class="card-body">
        <div class="text-muted small">In Progress</div>
        <div class="h4 mb-0"><?= (int)($counts['in_progress'] ?? 0) ?></div>
      </div>
    </div>
  </div>

  <div class="col-6 col-lg-2">
    <div class="card">
      <div class="card-body">
        <div class="text-muted small">Blocked</div>
        <div class="h4 mb-0"><?= (int)($counts['blocked'] ?? 0) ?></div>
      </div>
    </div>
  </div>

  <div class="col-6 col-lg-2">
    <div class="card">
      <div class="card-body">
        <div class="text-muted small">Done</div>
        <div class="h4 mb-0"><?= (int)($counts['done'] ?? 0) ?></div>
      </div>
    </div>
  </div>

  <div class="col-6 col-lg-2">
    <div class="card border-danger">
      <div class="card-body">
        <div class="text-muted small">Overdue</div>
        <div class="h4 mb-0"><?= (int)($counts['overdue'] ?? 0) ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-7">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="fw-semibold">Recent Tasks</div>
        <a class="small" href="<?= APP_BASE_PATH ?>/tasks">Open tasks list</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th style="width: 90px;">ID</th>
              <th>Title</th>
              <th style="width: 130px;">Status</th>
              <th style="width: 110px;">Priority</th>
              <th style="width: 140px;">Due</th>
              <th style="width: 160px;">Assigned</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($recentTasks) === 0): ?>
              <tr><td colspan="6" class="text-center text-muted py-4">No tasks yet.</td></tr>
            <?php else: ?>
              <?php foreach ($recentTasks as $t): ?>
                <tr>
                  <td class="fw-semibold"><?= (int)$t['task_id'] ?></td>
                  <td>
                    <a href="<?= APP_BASE_PATH ?>/tasks/edit?id=<?= (int)$t['task_id'] ?>" class="text-decoration-none">
                      <?= h((string)$t['title']) ?>
                    </a>
                    <div class="small text-muted">
                      Updated: <?= h((string)($t['updated_at'] ?? $t['created_at'])) ?>
                    </div>
                  </td>
                  <td><span class="badge <?= h(badgeClass((string)$t['status'])) ?>"><?= h((string)$t['status']) ?></span></td>
                  <td><?= h(priorityLabel((string)$t['priority'])) ?></td>
                  <td><?= h((string)($t['due_date'] ?? '')) ?></td>
                  <td><?= h((string)($t['assigned_to_name'] ?? '')) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-5">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="fw-semibold">Recent Audit Activity</div>
        <a class="small" href="<?= APP_BASE_PATH ?>/audit">Open audit log</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th style="width: 90px;">ID</th>
              <th>Event</th>
              <th style="width: 160px;">When</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($recentAudit) === 0): ?>
              <tr><td colspan="3" class="text-center text-muted py-4">No audit events yet.</td></tr>
            <?php else: ?>
              <?php foreach ($recentAudit as $a): ?>
                <tr>
                  <td class="fw-semibold"><?= (int)$a['audit_log_id'] ?></td>
                  <td>
                    <div class="fw-semibold"><?= h((string)$a['actor_name']) ?> · <?= h((string)$a['action']) ?></div>
                    <div class="small text-muted">
                      <?= h((string)$a['entity_type']) ?> #<?= h((string)$a['entity_id']) ?>
                    </div>
                  </td>
                  <td class="small text-muted"><?= h((string)$a['created_at']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-body">
        <div class="fw-semibold mb-1">What this demo proves</div>
        <ul class="mb-0">
          <li>Operational queue UX (filters, bulk actions, pagination)</li>
          <li>Export and reporting patterns (CSV)</li>
          <li>Change tracking (audit log with structured JSON)</li>
          <li>Clean PHP architecture without framework lock-in</li>
        </ul>
      </div>
    </div>
  </div>
</div>