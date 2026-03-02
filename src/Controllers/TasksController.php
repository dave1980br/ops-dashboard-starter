<?php
declare(strict_types=1);

final class TasksController
{
    private const PER_PAGE = 20;

    private const STATUSES = ['new', 'in_progress', 'blocked', 'done'];
    private const PRIORITIES = ['low', 'normal', 'high'];

    private function currentUserId(): int
    {
        $u = Auth::user();
        return (int)($u['user_id'] ?? 0);
    }

    private function audit(string $entityType, int $entityId, string $action, array $details = []): void
    {
        // Best-effort auditing: never break the request if audit insert fails.
        try {
            $pdo = Db::pdo();
            $stmt = $pdo->prepare("
                INSERT INTO audit_log (actor_user_id, entity_type, entity_id, action, details_json)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $this->currentUserId(),
                $entityType,
                $entityId,
                $action,
                $details ? json_encode($details, JSON_UNESCAPED_SLASHES) : null,
            ]);
        } catch (Throwable $e) {
            // swallow
        }
    }

    private function buildFiltersFromGet(): array
    {
        $status = isset($_GET['status']) ? trim((string)$_GET['status']) : '';
        $priority = isset($_GET['priority']) ? trim((string)$_GET['priority']) : '';
        $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';

        $where = [];
        $params = [];

        if ($status !== '' && in_array($status, self::STATUSES, true)) {
            $where[] = "t.status = ?";
            $params[] = $status;
        }

        if ($priority !== '' && in_array($priority, self::PRIORITIES, true)) {
            $where[] = "t.priority = ?";
            $params[] = $priority;
        }

        if ($q !== '') {
            $where[] = "(t.title LIKE ? OR t.description LIKE ?)";
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }

        $whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

        return [$status, $priority, $q, $whereSql, $params];
    }

    public function index(): void
    {
        $pdo = Db::pdo();

        [$status, $priority, $q, $whereSql, $params] = $this->buildFiltersFromGet();

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;

        // Count for pagination
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM tasks t {$whereSql}");
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetchColumn();

        $perPage = self::PER_PAGE;
        $offset = ($page - 1) * $perPage;
        if ($offset < 0) $offset = 0;

        $sql = "
            SELECT
                t.task_id, t.title, t.status, t.priority, t.due_date, t.created_at, t.updated_at,
                u1.display_name AS created_by_name,
                u2.display_name AS assigned_to_name
            FROM tasks t
            JOIN users u1 ON u1.user_id = t.created_by_user_id
            LEFT JOIN users u2 ON u2.user_id = t.assigned_to_user_id
            {$whereSql}
            ORDER BY
                CASE t.status
                    WHEN 'blocked' THEN 1
                    WHEN 'in_progress' THEN 2
                    WHEN 'new' THEN 3
                    WHEN 'done' THEN 4
                END,
                CASE t.priority
                    WHEN 'high' THEN 1
                    WHEN 'normal' THEN 2
                    WHEN 'low' THEN 3
                END,
                t.task_id DESC
            LIMIT {$perPage} OFFSET {$offset}
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $tasks = $stmt->fetchAll();

        $totalPages = (int)ceil($total / $perPage);
        if ($totalPages < 1) $totalPages = 1;
        if ($page > $totalPages) $page = $totalPages;

        View::render('tasks/index', [
            'user' => Auth::user(),
            'csrf' => Csrf::token(),
            'tasks' => $tasks,
            'filters' => [
                'status' => $status,
                'priority' => $priority,
                'q' => $q,
            ],
            'page' => $page,
            'total' => $total,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'statuses' => self::STATUSES,
            'priorities' => self::PRIORITIES,
            'msg' => isset($_GET['msg']) ? (string)$_GET['msg'] : '',
        ]);
    }

    public function exportCsv(): void
    {
        $pdo = Db::pdo();

        [$status, $priority, $q, $whereSql, $params] = $this->buildFiltersFromGet();

        // Pull a reasonable cap for demo safety
        $sql = "
            SELECT
                t.task_id,
                t.title,
                t.description,
                t.status,
                t.priority,
                t.due_date,
                u2.display_name AS assigned_to,
                u1.display_name AS created_by,
                t.created_at,
                t.updated_at
            FROM tasks t
            JOIN users u1 ON u1.user_id = t.created_by_user_id
            LEFT JOIN users u2 ON u2.user_id = t.assigned_to_user_id
            {$whereSql}
            ORDER BY t.task_id DESC
            LIMIT 5000
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $filename = 'tasks_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['task_id','title','description','status','priority','due_date','assigned_to','created_by','created_at','updated_at']);

        while ($row = $stmt->fetch()) {
            fputcsv($out, [
                (string)$row['task_id'],
                (string)$row['title'],
                (string)($row['description'] ?? ''),
                (string)$row['status'],
                (string)$row['priority'],
                (string)($row['due_date'] ?? ''),
                (string)($row['assigned_to'] ?? ''),
                (string)$row['created_by'],
                (string)$row['created_at'],
                (string)($row['updated_at'] ?? ''),
            ]);
        }

        fclose($out);
        exit;
    }

    public function create(): void
    {
        $pdo = Db::pdo();
        $users = $pdo->query("SELECT user_id, display_name FROM users WHERE is_active = 1 ORDER BY display_name ASC")->fetchAll();

        View::render('tasks/create', [
            'user' => Auth::user(),
            'csrf' => Csrf::token(),
            'statuses' => self::STATUSES,
            'priorities' => self::PRIORITIES,
            'users' => $users,
            'error' => null,
            'form' => [
                'title' => '',
                'description' => '',
                'status' => 'new',
                'priority' => 'normal',
                'assigned_to_user_id' => '',
                'due_date' => '',
            ],
        ]);
    }

    public function store(): void
    {
        Csrf::verify($_POST['_csrf'] ?? null);

        $pdo = Db::pdo();
        $createdByUserId = $this->currentUserId();

        $title = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $status = trim((string)($_POST['status'] ?? 'new'));
        $priority = trim((string)($_POST['priority'] ?? 'normal'));
        $assignedTo = trim((string)($_POST['assigned_to_user_id'] ?? ''));
        $dueDate = trim((string)($_POST['due_date'] ?? ''));

        if ($title === '') {
            $users = $pdo->query("SELECT user_id, display_name FROM users WHERE is_active = 1 ORDER BY display_name ASC")->fetchAll();
            View::render('tasks/create', [
                'user' => Auth::user(),
                'csrf' => Csrf::token(),
                'statuses' => self::STATUSES,
                'priorities' => self::PRIORITIES,
                'users' => $users,
                'error' => 'Title is required.',
                'form' => [
                    'title' => $title,
                    'description' => $description,
                    'status' => in_array($status, self::STATUSES, true) ? $status : 'new',
                    'priority' => in_array($priority, self::PRIORITIES, true) ? $priority : 'normal',
                    'assigned_to_user_id' => $assignedTo,
                    'due_date' => $dueDate,
                ],
            ]);
            return;
        }

        if (!in_array($status, self::STATUSES, true)) $status = 'new';
        if (!in_array($priority, self::PRIORITIES, true)) $priority = 'normal';

        $assignedToUserId = null;
        if ($assignedTo !== '') {
            $assignedToUserId = (int)$assignedTo;
            if ($assignedToUserId <= 0) $assignedToUserId = null;
        }

        $dueDateSql = null;
        if ($dueDate !== '') {
            $dueDateSql = $dueDate;
        }

        $stmt = $pdo->prepare("
            INSERT INTO tasks (title, description, status, priority, assigned_to_user_id, due_date, created_by_user_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $title,
            $description !== '' ? $description : null,
            $status,
            $priority,
            $assignedToUserId,
            $dueDateSql,
            $createdByUserId,
        ]);

        $taskId = (int)$pdo->lastInsertId();
        $this->audit('task', $taskId, 'create', [
            'title' => $title,
            'status' => $status,
            'priority' => $priority,
            'assigned_to_user_id' => $assignedToUserId,
            'due_date' => $dueDateSql,
        ]);

        Response::redirect('/tasks?msg=created');
    }

    public function edit(): void
    {
        $pdo = Db::pdo();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            Response::redirect('/tasks?msg=notfound');
        }

        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE task_id = ? LIMIT 1");
        $stmt->execute([$id]);
        $task = $stmt->fetch();

        if (!$task) {
            Response::redirect('/tasks?msg=notfound');
        }

        $users = $pdo->query("SELECT user_id, display_name FROM users WHERE is_active = 1 ORDER BY display_name ASC")->fetchAll();

        View::render('tasks/edit', [
            'user' => Auth::user(),
            'csrf' => Csrf::token(),
            'statuses' => self::STATUSES,
            'priorities' => self::PRIORITIES,
            'users' => $users,
            'error' => null,
            'task' => $task,
            'form' => [
                'title' => (string)$task['title'],
                'description' => (string)($task['description'] ?? ''),
                'status' => (string)$task['status'],
                'priority' => (string)$task['priority'],
                'assigned_to_user_id' => (string)($task['assigned_to_user_id'] ?? ''),
                'due_date' => (string)($task['due_date'] ?? ''),
            ],
        ]);
    }

    public function update(): void
    {
        Csrf::verify($_POST['_csrf'] ?? null);

        $pdo = Db::pdo();
        $id = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;
        if ($id <= 0) {
            Response::redirect('/tasks?msg=notfound');
        }

        $stmtOld = $pdo->prepare("SELECT * FROM tasks WHERE task_id = ? LIMIT 1");
        $stmtOld->execute([$id]);
        $old = $stmtOld->fetch();
        if (!$old) Response::redirect('/tasks?msg=notfound');

        $title = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $status = trim((string)($_POST['status'] ?? 'new'));
        $priority = trim((string)($_POST['priority'] ?? 'normal'));
        $assignedTo = trim((string)($_POST['assigned_to_user_id'] ?? ''));
        $dueDate = trim((string)($_POST['due_date'] ?? ''));

        if ($title === '') {
            $users = $pdo->query("SELECT user_id, display_name FROM users WHERE is_active = 1 ORDER BY display_name ASC")->fetchAll();

            View::render('tasks/edit', [
                'user' => Auth::user(),
                'csrf' => Csrf::token(),
                'statuses' => self::STATUSES,
                'priorities' => self::PRIORITIES,
                'users' => $users,
                'error' => 'Title is required.',
                'task' => $old,
                'form' => [
                    'title' => $title,
                    'description' => $description,
                    'status' => in_array($status, self::STATUSES, true) ? $status : (string)$old['status'],
                    'priority' => in_array($priority, self::PRIORITIES, true) ? $priority : (string)$old['priority'],
                    'assigned_to_user_id' => $assignedTo,
                    'due_date' => $dueDate,
                ],
            ]);
            return;
        }

        if (!in_array($status, self::STATUSES, true)) $status = 'new';
        if (!in_array($priority, self::PRIORITIES, true)) $priority = 'normal';

        $assignedToUserId = null;
        if ($assignedTo !== '') {
            $assignedToUserId = (int)$assignedTo;
            if ($assignedToUserId <= 0) $assignedToUserId = null;
        }

        $dueDateSql = null;
        if ($dueDate !== '') {
            $dueDateSql = $dueDate;
        }

        $stmt = $pdo->prepare("
            UPDATE tasks
            SET title = ?, description = ?, status = ?, priority = ?, assigned_to_user_id = ?, due_date = ?, updated_at = NOW()
            WHERE task_id = ?
            LIMIT 1
        ");
        $stmt->execute([
            $title,
            $description !== '' ? $description : null,
            $status,
            $priority,
            $assignedToUserId,
            $dueDateSql,
            $id,
        ]);

        $this->audit('task', $id, 'update', [
            'from' => [
                'title' => $old['title'],
                'status' => $old['status'],
                'priority' => $old['priority'],
                'assigned_to_user_id' => $old['assigned_to_user_id'],
                'due_date' => $old['due_date'],
            ],
            'to' => [
                'title' => $title,
                'status' => $status,
                'priority' => $priority,
                'assigned_to_user_id' => $assignedToUserId,
                'due_date' => $dueDateSql,
            ],
        ]);

        Response::redirect('/tasks?msg=updated');
    }

    public function delete(): void
    {
        Csrf::verify($_POST['_csrf'] ?? null);

        $pdo = Db::pdo();
        $id = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;

        if ($id <= 0) {
            Response::redirect('/tasks?msg=notfound');
        }

        // Capture a few fields for audit before deletion
        $stmtOld = $pdo->prepare("SELECT title,status,priority FROM tasks WHERE task_id = ? LIMIT 1");
        $stmtOld->execute([$id]);
        $old = $stmtOld->fetch();

        $stmt = $pdo->prepare("DELETE FROM tasks WHERE task_id = ? LIMIT 1");
        $stmt->execute([$id]);

        $this->audit('task', $id, 'delete', $old ? $old : []);

        Response::redirect('/tasks?msg=deleted');
    }

    public function bulk(): void
    {
        Csrf::verify($_POST['_csrf'] ?? null);

        $action = trim((string)($_POST['action'] ?? ''));
        $ids = $_POST['ids'] ?? [];

        if (!is_array($ids) || count($ids) === 0) {
            Response::redirect('/tasks?msg=none_selected');
        }

        $taskIds = [];
        foreach ($ids as $v) {
            $i = (int)$v;
            if ($i > 0) $taskIds[] = $i;
        }
        if (count($taskIds) === 0) {
            Response::redirect('/tasks?msg=none_selected');
        }

        $pdo = Db::pdo();

        if ($action === 'set_status') {
            $newStatus = trim((string)($_POST['new_status'] ?? ''));
            if (!in_array($newStatus, self::STATUSES, true)) {
                Response::redirect('/tasks?msg=bad_status');
            }

            $placeholders = implode(',', array_fill(0, count($taskIds), '?'));
            $params = array_merge([$newStatus], $taskIds);

            $stmt = $pdo->prepare("UPDATE tasks SET status = ?, updated_at = NOW() WHERE task_id IN ({$placeholders})");
            $stmt->execute($params);

            // Audit one row for the bulk action (not per-task, for demo simplicity)
            $this->audit('task', 0, 'bulk_status', [
                'task_ids' => $taskIds,
                'new_status' => $newStatus,
            ]);

            Response::redirect('/tasks?msg=bulk_updated');
        }

        Response::redirect('/tasks?msg=bad_action');
    }
}