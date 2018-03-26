<?php
namespace App\Middleware;

/**
 * Middleware base class. (Basically like Base Controller class).
 * In this case we use Middleware in order to display Errors within all views.
 *
 * A Middleware is basically a layer surrounding the application to run some code
 * before and after application execution. This code is supposed to manipulate
 * Request and Response objects.
 *
 * Technically it's a Callable which expects three parameters:
 *   \Psr\Http\Message\ServerRequestInterface [Request Obj]
 *   \Psr\Http\Message\ResponseInterface [Response Obj]
 *   Callable: Next callable middleware [$next]
 *
 * Doc:
 * https://www.slimframework.com/docs/concepts/middleware.html
 */
class Middleware
{
    protected $container;
    protected $role;

    public function __construct($container, $role = null)
    {
        $this->container = $container;
        $this->role = (null !== $role) ? $role : null;
    }
}
