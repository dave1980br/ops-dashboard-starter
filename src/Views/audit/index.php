<?php
declare(strict_types=1);

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$entityType = (string)($filters['entity_type'] ?? '');
$action = (string)($filters['action'] ?? '');
$actor = (string)($filters['actor'] ?? '');
$entityId = (string)($filters['entity_id'] ?? '');

function buildQuery(array $overrides = []): string {
    $base = [
        'entity_type' => $_GET['entity_type'] ?? '',
        'action' => $_GET['action'] ?? '',
        'actor' => $_GET['actor'] ?? '',
        'entity_id' => $_GET['entity_id'] ?? '',
        'page' => $_GET['page'] ?? '',
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

function prettyJson(?string $json): string {
    if (!$json) return '';
    $decoded = json_decode($json, true);
    if ($decoded === null) return $json;
    return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h3 mb-0">Audit Log</h1>
  <div class="small text-muted">
    Showing <?= (int)count($rows) ?> of <?= (int)$total ?>
  </div>
</div>

<form method="get" action="<?= APP_BASE_PATH ?>/audit" class="card mb-3">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-12 col-md-3">
        <label class="form-label">Entity Type</label>
        <select class="form-select" name="entity_type">
          <option value="">All</option>
          <?php foreach ($entityTypes as $t): ?>
            <option value="<?= h($t) ?>" <?= $entityType === $t ? 'selected' : '' ?>><?= h($t) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12 col-md-3">
        <label class="form-label">Action</label>
        <select class="form-select" name="action">
          <option value="">All</option>
          <?php foreach ($actions as $a): ?>
            <option value="<?= h($a) ?>" <?= $action === $a ? 'selected' : '' ?>><?= h($a) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12 col-md-3">
        <label class="form-label">Actor (name/email)</label>
        <input class="form-control" type="text" name="actor" value="<?= h($actor) ?>" placeholder="Admin User">
      </div>

      <div class="col-12 col-md-2">
        <label class="form-label">Entity ID</label>
        <input class="form-control" type="text" name="entity_id" value="<?= h($entityId) ?>" placeholder="123">
      </div>

      <div class="col-12 col-md-1 d-grid">
        <button class="btn btn-outline-primary" type="submit">Go</button>
      </div>
    </div>

    <div class="mt-2">
      <a href="<?= APP_BASE_PATH ?>/audit" class="small">Clear filters</a>
    </div>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead class="table-light">
      <tr>
        <th style="width: 90px;">ID</th>
        <th style="width: 190px;">When</th>
        <th style="width: 220px;">Actor</th>
        <th style="width: 120px;">Entity</th>
        <th style="width: 130px;">Action</th>
        <th>Details</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($rows) === 0): ?>
        <tr>
          <td colspan="6" class="text-center text-muted py-4">No audit records found.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td class="fw-semibold"><?= (int)$r['audit_log_id'] ?></td>
            <td class="small text-muted"><?= h((string)$r['created_at']) ?></td>
            <td>
              <div class="fw-semibold"><?= h((string)$r['actor_name']) ?></div>
              <div class="small text-muted"><?= h((string)$r['actor_email']) ?></div>
            </td>
            <td>
              <div class="fw-semibold"><?= h((string)$r['entity_type']) ?></div>
              <div class="small text-muted">#<?= h((string)$r['entity_id']) ?></div>
            </td>
            <td><span class="badge bg-secondary"><?= h((string)$r['action']) ?></span></td>
            <td>
              <?php $pj = prettyJson($r['details_json'] ?? null); ?>
              <?php if ($pj === ''): ?>
                <span class="text-muted small">—</span>
              <?php else: ?>
                <pre class="mb-0 small" style="white-space: pre-wrap;"><?= h($pj) ?></pre>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($totalPages > 1): ?>
  <nav class="mt-3">
    <ul class="pagination">
      <?php
        $prev = $page - 1;
        $next = $page + 1;
      ?>
      <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= APP_BASE_PATH ?>/audit<?= buildQuery(['page' => $prev <= 1 ? '' : (string)$prev]) ?>">Previous</a>
      </li>

      <?php
        $start = max(1, $page - 3);
        $end = min($totalPages, $page + 3);
        for ($i = $start; $i <= $end; $i++):
      ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
          <a class="page-link" href="<?= APP_BASE_PATH ?>/audit<?= buildQuery(['page' => $i === 1 ? '' : (string)$i]) ?>"><?= (int)$i ?></a>
        </li>
      <?php endfor; ?>

      <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= APP_BASE_PATH ?>/audit<?= buildQuery(['page' => (string)$next]) ?>">Next</a>
      </li>
    </ul>
  </nav>
<?php endif; ?>