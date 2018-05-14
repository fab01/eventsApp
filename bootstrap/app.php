<?php
use Respect\Validation\Validator as v;
session_start();
require __DIR__."/../vendor/autoload.php";

/* -- // Initialize Application. \\ -- */
$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => TRUE,
        'db' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'iimEvents',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ],
    ],
]);

/**
 * Set the Container Object.
 */
$container = $app->getContainer();

/**
 * ELOQUENT ORM.
 * Set and boot Eloquent App.
 * Why Variable $capsule?
 *  "$capsule is just the way Laravel let us to use its components out of Laravel".
 */
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();
/**
 * Containers for Database Eloquent.
 */
$container['db'] = function($container) use($capsule) {
    return $capsule;
};

/**
 * VALIDATION.
 * Containers for validation.
 */
$container['validator'] = function($container) {
    return new \App\Validation\Validator;
};
/**
 * Allow Validation library to use our Rules in App\Validation\Rules.
 */
v::with('\\App\\Validation\\Rules\\');

/**
 * AUTHENTICATION.
 * Container for Authentication
 */
$container['auth'] = function ($container) {
    return new App\Auth\Auth;
};

/**
 * FORM.
 * Container for Web forms.
 */
$container['form'] = function ($container) {
    return new App\Form\Form($container);
};

/**
 * FLASH MESSAGES.
 */
$container['flash'] = function ($container) {
    return new \Slim\Flash\Messages;
};

/**
 * VIEWS.
 * Containers for views.
 */
$container['view'] = function($container) {
    $view = new \Slim\Views\Twig(__DIR__.'/../resources/views', [
        'cache' => FALSE,
    ]);

    $view->addExtension(new \Slim\Views\TwigExtension(
        $container->router,
        $container->request->getUri()
    ));

    $view->addExtension(new \App\Extensions\TwigExtensionAsserts(
        $container->router,
        $container->request->getUri()
    ));

    /**
     * This way we add a variable 'auth' that we can use inside views and Twig.
     * This variable contains User information and check if is logged.
     */
    $view->getEnvironment()->addGlobal('auth', [
        'isLoggedIn' => $container->auth->isLoggedIn(),
        'isAdmin' => $container->auth->isAdmin(),
        'user' => $container->auth->user(),
    ]);

    /**
     * Flash global method.
     */
    $view->getEnvironment()->addGlobal('flash', $container->flash);

    return $view;
};

/**
 * CONTROLLER.
 * Containers for Controllers.
 */
$container['HomeController'] = function($container) {
    return new \App\Controllers\HomeController($container);
};

$container['AuthController'] = function($container) {
    return new \App\Controllers\Auth\AuthController($container);
};

$container['PasswordController'] = function($container) {
    return new \App\Controllers\Auth\PasswordController($container);
};

$container['UserController'] = function($container) {
    return new \App\Controllers\UserController($container);
};

$container['EventController'] = function($container) {
    return new \App\Controllers\EventController($container);
};

$container['MeetUpController'] = function($container) {
    return new \App\Controllers\MeetUpController($container);
};

$container['SubscriptionController'] = function($container) {
    return new \App\Controllers\SubscriptionController($container);
};

$container['AccommodationController'] = function($container) {
  return new \App\Controllers\AccommodationController($container);
};
/**
 * MIDDLEWARE.
 * Attach Middleware classes to Slim.
 * Variable "$container" is necessary because it's extending Base Middleware class.
 */
$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \App\Middleware\OldInputMiddleware($container));

/**
 * CSRF.
 * Add Slim-Csrf library in the Container and switch it on.
 * Repo and Doc:
 * https://github.com/slimphp/Slim-Csrf
 */
$app->add(new \App\Middleware\CsrfViewMiddleware($container));
$container['csrf'] = function($container) {
    return new \Slim\Csrf\Guard;
};
//$app->add($container->csrf);

/* -- // Get routes. \\ -- */
require __DIR__.'/../app/routes.php';

