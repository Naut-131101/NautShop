<?php

declare(strict_types=1);

namespace Core;

class App
{
    public function __construct(
        protected Router $router,
        protected Request $request,
        protected Response $response
    ) {
    }

    public function run(): void
    {
        $this->router->dispatch($this->request, $this->response);
    }
}