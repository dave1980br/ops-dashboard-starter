<?php
declare(strict_types=1);

final class RequireAuth
{
    public static function handle(): void
    {
        if (!Auth::check()) {
            Response::redirect('/login');
        }
    }
}