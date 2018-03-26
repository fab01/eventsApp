<?php

namespace App\Middleware;

use App\Auth\Auth as Auth;

class RoleMiddleware extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        if (!Auth::hasRole($this->role)) {
            return $response->withRedirect($this->container->router->pathFor('auth.signin'));
        }
        $response = $next($request, $response);
        return $response;
    }
}
