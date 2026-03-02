<?php
declare(strict_types=1);

final class DashboardController
{
    public function index(): void
    {
        $pdo = Db::pdo();

        // Task counts
        $counts = [
            'total' => (int)$pdo->query("SELECT COUNT(*) FROM tasks")->fetchColumn(),
            'new' => (int)$pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'new'")->fetchColumn(),
            'in_progress' => (int)$pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'in_progress'")->fetchColumn(),
            'blocked' => (int)$pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'blocked'")->fetchColumn(),
            'done' => (int)$pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'done'")->fetchColumn(),
            'overdue' => (int)$pdo->query("
                SELECT COUNT(*) 
                FROM tasks 
                WHERE due_date IS NOT NULL 
                  AND due_date < CURDATE()
                  AND status <> 'done'
            ")->fetchColumn(),
        ];

        // Recent tasks (show some operational reality)
        $recentTasks = $pdo->query("
            SELECT
                t.task_id, t.title, t.status, t.priority, t.due_date, t.updated_at, t.created_at,
                u2.display_name AS assigned_to_name
            FROM tasks t
            LEFT JOIN users u2 ON u2.user_id = t.assigned_to_user_id
            ORDER BY COALESCE(t.updated_at, t.created_at) DESC
            LIMIT 8
        ")->fetchAll();

        // Recent audit activity
        $recentAudit = $pdo->query("
            SELECT
                a.audit_log_id, a.entity_type, a.entity_id, a.action, a.created_at,
                u.display_name AS actor_name
            FROM audit_log a
            JOIN users u ON u.user_id = a.actor_user_id
            ORDER BY a.audit_log_id DESC
            LIMIT 8
        ")->fetchAll();

        View::render('dashboard/index', [
            'user' => Auth::user(),
            'csrf' => Csrf::token(),
            'counts' => $counts,
            'recentTasks' => $recentTasks,
            'recentAudit' => $recentAudit,
        ]);
    }
}