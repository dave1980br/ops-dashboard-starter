<?php
declare(strict_types=1);

require __DIR__ . '/../src/Config/config.php';
require __DIR__ . '/../src/Core/Db.php';
require __DIR__ . '/../src/Core/Router.php';
require __DIR__ . '/../src/Core/Response.php';
require __DIR__ . '/../src/Core/View.php';
require __DIR__ . '/../src/Core/Csrf.php';
require __DIR__ . '/../src/Core/Auth.php';

require __DIR__ . '/../src/Controllers/AuthController.php';
require __DIR__ . '/../src/Controllers/DashboardController.php';
require __DIR__ . '/../src/Controllers/TasksController.php';
require __DIR__ . '/../src/Controllers/AuditController.php';
require __DIR__ . '/../src/Controllers/DocsController.php';

require __DIR__ . '/../src/Middleware/RequireAuth.php';

session_start();

// Hosted under /demos/ops-dashboard
$basePath = APP_BASE_PATH;

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?? '/';

if (strpos($path, $basePath) === 0) {
    $_SERVER['REQUEST_URI'] = substr($uri, strlen($basePath)) ?: '/';
}

$router = new Router();

// Public routes
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

// Protected routes
$router->get('/dashboard', [DashboardController::class, 'index'], [RequireAuth::class]);

$router->get('/tasks', [TasksController::class, 'index'], [RequireAuth::class]);
$router->get('/tasks/export', [TasksController::class, 'exportCsv'], [RequireAuth::class]);
$router->get('/tasks/create', [TasksController::class, 'create'], [RequireAuth::class]);
$router->post('/tasks/create', [TasksController::class, 'store'], [RequireAuth::class]);
$router->get('/tasks/edit', [TasksController::class, 'edit'], [RequireAuth::class]); // ?id=123
$router->post('/tasks/edit', [TasksController::class, 'update'], [RequireAuth::class]);
$router->post('/tasks/delete', [TasksController::class, 'delete'], [RequireAuth::class]);
$router->post('/tasks/bulk', [TasksController::class, 'bulk'], [RequireAuth::class]);

$router->get('/audit', [AuditController::class, 'index'], [RequireAuth::class]);

$router->get('/docs', [DocsController::class, 'index'], [RequireAuth::class]);

// Default route
$router->get('/', function (): void {
    Response::redirect('/dashboard');
});

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);