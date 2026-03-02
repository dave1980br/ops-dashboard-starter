# Ops Dashboard Starter (PHP + MySQL)

A lightweight ops/admin dashboard demo built in PHP (no framework) with MySQL/MariaDB and Bootstrap 5.

This project is designed as a portfolio example of patterns commonly needed for internal tools:
- operational queues
- status workflows
- filtering/pagination
- exports
- audit logging

## Live Demo

Demo: https://www.blueoaksoftware.com/demos/ops-dashboard/login
Login: admin@example.com
Password: admin123

Note: This is a demo environment. Credentials and data are intentionally simple.

## Features

Authentication
- Session-based authentication
- CSRF protection on POST actions

Tasks Queue (Ops module)
- Create / edit / delete tasks
- Shareable filters via query string:
  - status (new, in_progress, blocked, done)
  - priority (low, normal, high)
  - q (search title/description)
- Pagination
- Bulk status update for selected tasks
- CSV export (respects current filters)

Audit Logging
- Records task changes:
  - create / update / delete / bulk status changes
- Simple UI viewer at /audit
- Filter by:
  - entity type
  - action
  - actor (name/email)
  - entity id
- Stores details as JSON for structured change history

## Tech Stack
- PHP 8+
- MySQL / MariaDB
- Bootstrap 5
- PDO (prepared statements)
- Minimal front controller + router
- Designed to work under a subdirectory base path (e.g. /demos/ops-dashboard)

## Project Structure

public/               web root (index.php + .htaccess)
src/
  Config/             config constants / env usage
  Core/               Router, Db, Auth, View, Csrf, Response
  Controllers/        Auth, Dashboard, Tasks, Audit, Docs
  Middleware/         RequireAuth
  Views/              layouts + pages
database/             schema.sql + seed.sql
docker/               optional local docker setup

## Local Setup (MariaDB/MySQL)

1) Create database + tables
- Update credentials in src/Config/config.php (or set env vars)
- Then run:
  mysql -u YOURUSER -p < database/schema.sql

2) Seed demo user (optional)
- If you want the same demo credentials:
  mysql -u YOURUSER -p ops_dashboard < database/seed.sql

3) Run locally
- If you have PHP installed:
  php -S localhost:8080 -t public
- Then visit:
  http://localhost:8080/login

## Customization Ideas (Typical Client Work)
- Additional modules: inventory queue, orders queue, returns, exceptions
- Role-based access control (RBAC) per module/action
- Webhook ingestion (Stripe, marketplaces) with idempotency
- Notifications (email/SMS) for blockers and SLA breaches
- Background jobs (cron/worker) and reconciliation tooling
- Advanced exports (scheduled, templated)

## License
MIT (see LICENSE)
