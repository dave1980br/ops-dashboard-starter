<?php
declare(strict_types=1);

final class Response
{
    public static function redirect(string $path): void
    {
        if ($path === '' || $path[0] !== '/') {
            $path = '/' . ltrim($path, '/');
        }

        header('Location: ' . APP_BASE_PATH . $path, true, 302);
        exit;
    }
}