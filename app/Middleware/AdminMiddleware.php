<?php

namespace App\Middleware;

use App\Auth\Auth as Auth;

class AdminMiddleware extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        if (!Auth::isAdmin()) {
            //$this->container->flash->addMessage('error', 'You are not authorized. Please sign in before doing that.');
            return $response->withRedirect($this->container->router->pathFor('auth.signin'));
        }
        $response = $next($request, $response);
        return $response;
    }
}
