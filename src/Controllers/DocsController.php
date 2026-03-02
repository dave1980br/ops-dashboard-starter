<?php
declare(strict_types=1);

final class DocsController
{
    public function index(): void
    {
        View::render('docs/index', [
            'user' => Auth::user(),
            'csrf' => Csrf::token(),
        ]);
    }
}