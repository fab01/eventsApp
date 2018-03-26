<?php

use \App\Middleware\AuthMiddleware;
use \App\Middleware\AdminMiddleware;
use \App\Middleware\GuestMiddleware;
use \App\Middleware\RoleMiddleware;

/**
 * Redirection IF USER IS AUTHENTICATED.
 */
$app->group('', function() use($app) {
    // This route displays the view.
    $app->get('/signup', 'AuthController:getSignUp')->setName('auth.signup'); // SIGNUP.
    /**
     * This route handle the data.
     * Method 'setName()' is not necessary because the Action Controller
     * doesn't display anything itself and returns a redirection..
     */
    $app->post('/signup', 'AuthController:postSignUp'); // SIGNUP.
    $app->get('/signin', 'AuthController:getSignIn')->setName('auth.signin'); // SIGNIN.
    $app->post('/signin', 'AuthController:postSignIn'); // SIGNIN.

})->add(new GuestMiddleware($container));

/**
 * Routes for AUTHENTICATED USER.
 */
$app->group('', function() use ($app, $container) {

    $app->get('/', 'HomeController:index')->setName('home');
    $app->get('/signout', 'AuthController:getSignOut')->setName('auth.signout'); // LOGOUT
    $app->get('/password/change', 'PasswordController:getPasswordChange')->setName('auth.password.change'); // PASSWORD CHANGE
    $app->post('/password/change', 'PasswordController:postPasswordChange'); // PASSWORD CHANGE

    /**
     * Routes accessible to to USER EDITOR.
     */
    $app->group('', function() use($app, $container) {

        $app->get('/user/all', 'UserController:getAll')->setName('user.all'); // LIST OF USERS.

        $app->get('/event/all', 'EventController:getAll')->setName('event.all'); // LIST OF EVENTS.
        $app->get('/event/create', 'EventController:getEventCreate')->setName('event.create'); // CREATE EVENTS.
        $app->post('/event/create', 'EventController:postEventCreate'); // CREATE EVENTS.
        $app->get('/event/update/{id}', 'EventController:getEventUpdate')->setName('event.update'); // UPDATE EVENTS.
        $app->post('/event/update/{id}', 'EventController:postEventUpdate'); // UPDATE EVENTS.

        $app->get('/meetup/all', 'MeetUpController:getAll')->setName('meetup.all'); // LIST OF MEETUP.
        $app->post('/meetup/all', 'MeetUpController:postAll'); // LIST OF MEETUP BY POST EVENT ID.
        $app->get('/meetup/create', 'MeetUpController:getMeetUpCreate')->setName('meetup.create'); // CREATE MEETUP.
        $app->post('/meetup/create', 'MeetUpController:postMeetUpCreate'); // CREATE MEETUP.
        $app->get('/meetup/update/{id}', 'MeetUpController:getMeetUpUpdate')->setName('meetup.update'); // UPDATE MEETUP.
        $app->post('/meetup/update/{id}', 'MeetUpController:postMeetUpUpdate'); // UPDATE MEETUP.

        /**
         * Routes reserved to USER ADMINISTRATOR.
         */
        $app->group('', function() use($app, $container) {

        })->add(new AdminMiddleware($container));

    })->add(new RoleMiddleware($container, ['Moderator']));

})->add(new AuthMiddleware($container));
