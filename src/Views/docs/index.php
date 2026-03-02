<?php
declare(strict_types=1);

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h3 mb-1">Docs</h1>
    <div class="text-muted">What this demo is, what it proves, and how it can be extended for a real business.</div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="<?= APP_BASE_PATH ?>/audit">Audit</a>
    <a class="btn btn-primary" href="<?= APP_BASE_PATH ?>/tasks">Tasks</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-7">
    <div class="card">
      <div class="card-body">
        <h2 class="h5">Overview</h2>
        <p class="mb-2">
          This is a lightweight ops/admin dashboard built in PHP + MySQL with Bootstrap.
          It demonstrates common internal-tool patterns: queues, workflow statuses, filtering, exports, and audit logging.
        </p>

        <h2 class="h5 mt-4">Key Features</h2>
        <ul class="mb-0">
          <li><strong>Tasks Queue:</strong> CRUD, shareable filters, pagination, bulk status updates</li>
          <li><strong>CSV Export:</strong> filtered export for reporting and handoffs</li>
          <li><strong>Audit Log:</strong> structured JSON event history with filtering</li>
          <li><strong>Security basics:</strong> sessions + CSRF protection</li>
        </ul>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-body">
        <h2 class="h5">Typical Customizations (Real Client Work)</h2>
        <ul class="mb-0">
          <li>Additional modules (inventory queue, order exceptions, returns processing, approvals)</li>
          <li>Role-based access control (RBAC) per module/action</li>
          <li>Background jobs (cron/worker) for sync/reconciliation</li>
          <li>Webhook ingestion (Stripe) with idempotency + event replay tooling</li>
          <li>Integrations (eBay / marketplaces) for inventory, offers, and orders</li>
          <li>Notifications (email/SMS) and SLA escalation for blocked items</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-5">
    <div class="card">
      <div class="card-body">
        <h2 class="h5">Live Demo Links</h2>
        <ul class="mb-0">
          <li><a href="<?= APP_BASE_PATH ?>/dashboard">Dashboard</a></li>
          <li><a href="<?= APP_BASE_PATH ?>/tasks">Tasks</a></li>
          <li><a href="<?= APP_BASE_PATH ?>/audit">Audit Log</a></li>
        </ul>

        <hr>

        <h2 class="h5">Demo Credentials</h2>
        <div class="small text-muted">Admin login (demo only)</div>
        <div class="mt-1">
          <code>admin@example.com</code> / <code>admin123</code>
        </div>
      </div>
    </div>
  </div>
</div>