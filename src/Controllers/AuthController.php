<?php
declare(strict_types=1);

final class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) Response::redirect('/dashboard');

        View::render('auth/login', [
            'csrf' => Csrf::token(),
            'error' => null,
            'email' => '',
        ], 'auth');
    }

    public function login(): void
    {
        Csrf::verify($_POST['_csrf'] ?? null);

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        $stmt = Db::pdo()->prepare("SELECT user_id, email, display_name, role, password_hash
                                    FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            View::render('auth/login', [
                'csrf' => Csrf::token(),
                'error' => 'Invalid email or password.',
                'email' => $email,
            ], 'auth');
            return;
        }

        Auth::login($user);
        Response::redirect('/dashboard');
    }

    public function logout(): void
    {
        Csrf::verify($_POST['_csrf'] ?? null);
        Auth::logout();
        Response::redirect('/login');
    }
}