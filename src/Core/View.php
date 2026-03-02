<?php
declare(strict_types=1);

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'app'): void
    {
        extract($data, EXTR_SKIP);

        $viewFile = __DIR__ . '/../Views/' . $view . '.php';
        if (!is_file($viewFile)) {
            throw new RuntimeException("View not found: {$viewFile}");
        }

        $layoutFile = __DIR__ . '/../Views/layouts/' . $layout . '.php';
        if (!is_file($layoutFile)) {
            throw new RuntimeException("Layout not found: {$layoutFile}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require $layoutFile;
    }
}