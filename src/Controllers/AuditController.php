<?php
declare(strict_types=1);

final class AuditController
{
    private const PER_PAGE = 50;

    public function index(): void
    {
        $pdo = Db::pdo();

        $entityType = isset($_GET['entity_type']) ? trim((string)$_GET['entity_type']) : '';
        $action = isset($_GET['action']) ? trim((string)$_GET['action']) : '';
        $actor = isset($_GET['actor']) ? trim((string)$_GET['actor']) : '';
        $entityId = isset($_GET['entity_id']) ? trim((string)$_GET['entity_id']) : '';

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;

        $where = [];
        $params = [];

        if ($entityType !== '') {
            $where[] = "a.entity_type = ?";
            $params[] = $entityType;
        }

        if ($action !== '') {
            $where[] = "a.action = ?";
            $params[] = $action;
        }

        if ($actor !== '') {
            // actor filter matches display_name or email (LIKE)
            $where[] = "(u.display_name LIKE ? OR u.email LIKE ?)";
            $params[] = '%' . $actor . '%';
            $params[] = '%' . $actor . '%';
        }

        if ($entityId !== '' && ctype_digit($entityId)) {
            $where[] = "a.entity_id = ?";
            $params[] = (int)$entityId;
        }

        $whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM audit_log a JOIN users u ON u.user_id = a.actor_user_id {$whereSql}");
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetchColumn();

        $perPage = self::PER_PAGE;
        $offset = ($page - 1) * $perPage;
        if ($offset < 0) $offset = 0;

        $stmt = $pdo->prepare("
            SELECT
                a.audit_log_id, a.actor_user_id, a.entity_type, a.entity_id, a.action, a.details_json, a.created_at,
                u.display_name AS actor_name, u.email AS actor_email
            FROM audit_log a
            JOIN users u ON u.user_id = a.actor_user_id
            {$whereSql}
            ORDER BY a.audit_log_id DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // Filters dropdown options (distinct values)
        $entityTypes = $pdo->query("SELECT DISTINCT entity_type FROM audit_log ORDER BY entity_type ASC")->fetchAll();
        $actions = $pdo->query("SELECT DISTINCT action FROM audit_log ORDER BY action ASC")->fetchAll();

        $totalPages = (int)ceil($total / $perPage);
        if ($totalPages < 1) $totalPages = 1;
        if ($page > $totalPages) $page = $totalPages;

        View::render('audit/index', [
            'user' => Auth::user(),
            'csrf' => Csrf::token(),
            'rows' => $rows,
            'filters' => [
                'entity_type' => $entityType,
                'action' => $action,
                'actor' => $actor,
                'entity_id' => $entityId,
            ],
            'entityTypes' => array_map(fn($r) => (string)$r['entity_type'], $entityTypes),
            'actions' => array_map(fn($r) => (string)$r['action'], $actions),
            'page' => $page,
            'total' => $total,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
        ]);
    }
}