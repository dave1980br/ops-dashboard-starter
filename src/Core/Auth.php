<?php
declare(strict_types=1);

final class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function login(array $userRow): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'user_id' => (int)$userRow['user_id'],
            'email' => (string)$userRow['email'],
            'display_name' => (string)$userRow['display_name'],
            'role' => (string)$userRow['role'],
        ];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }
}