<?php
namespace App\Middleware;

class ValidationErrorsMiddleware extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        //ob_end_clean();
        //var_dump('Middleware');
        if (isset($_SESSION['errors'])) {
            $this->container->view->getEnvironment()->addGlobal('errors', $_SESSION['errors']);
        }
        unset($_SESSION['errors']);
        $response = $next($request, $response);
        return $response;
    }
}
