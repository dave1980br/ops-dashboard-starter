<?php
declare(strict_types=1);

final class DashboardController
{
    public function index(): void
    {
        View::render('dashboard/index', [
            'user' => Auth::user(),
            'csrf' => Csrf::token(),
        ]);
    }
}