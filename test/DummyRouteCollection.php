<?php

declare(strict_types=1);

namespace FastRoute\Test;

use FastRoute\RouteCollection;

class DummyRouteCollection extends RouteCollection
{
    /** @var mixed[] */
    public $routes = [];

    public function __construct()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function addRoute($httpMethod, string $route, $handler, ?string $name = null): void
    {
        $route = $this->currentGroupPrefix . $route;
        $this->routes[] = [$httpMethod, $route, $handler];
    }
}
