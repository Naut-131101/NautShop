<?php

declare(strict_types=1);

namespace Core;

class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        view($view, $data, $layout);
    }

    protected function redirect(string $path): void
    {
        redirect($path);
    }
}