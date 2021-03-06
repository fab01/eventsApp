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

    //ROUTES PER LE REGISTRAZIONI
    $app->get('/event/subscription', 'SubscriptionController:getEventSubscriptionCreate')->setName('event.subscription.create');
    $app->get('/event/subscription/{id}', 'SubscriptionController:getEventSubscriptionUpdate')->setName('event.subscription.update');
    $app->get('/meetup/subscription/{id}', 'SubscriptionController:getMeetUpSubscriptionCreate')->setName('meetup.subscription.create');
    $app->post('/event/subscription', 'SubscriptionController:postEventSubscriptionCreate');
    $app->post('/event/subscription/{id}', 'SubscriptionController:postEventSubscriptionUpdate');

    /**
     * Routes accessible to to USER EDITOR AND MODERATOR.
     */
    $app->group('', function() use($app, $container) {

        $app->get('/user/all', 'UserController:getAll')->setName('user.all'); // LIST OF USERS.
        $app->get('/accommodation/all', 'AccommodationController:getAll')->setName('accommodation.all'); // LIST OF ACCOMMODATION.

        $app->get('/event/all', 'EventController:getAll')->setName('event.all'); // LIST OF EVENTS.
        $app->get('/event/details/{eid}', 'EventController:getEventDetails')->setName('event.details'); // DETAILS EVENTS.
        $app->get('/event/create', 'EventController:getEventCreate')->setName('event.create'); // CREATE EVENTS.
        $app->get('/event/update/{id}', 'EventController:getEventUpdate')->setName('event.update'); // UPDATE EVENTS.

        $app->get('/meetup/update/{id}', 'MeetUpController:getMeetUpUpdate')->setName('meetup.update'); // UPDATE MEETUP.
        $app->get('/meetup/all', 'MeetUpController:getAll')->setName('meetup.all'); // LIST OF MEETUP.
        $app->post('/meetup/all', 'MeetUpController:postAll'); // LIST OF MEETUP BY POST EVENT ID.
        $app->get('/meetup/all/{eid}', 'MeetUpController:getAllByEid')->setName('meetup.all.eid'); // LIST OF MEETUP FILTERED BY Event ID in GET.
        $app->get('/meetup/details/{id}', 'MeetUpController:getMeetUpDetails')->setName('meetup.details'); // DETAILS MEETUP.

        $app->get('/accommodation/create', 'AccommodationController:getAccommodationCreate')->setName('accommodation.create'); // CREATE ACCOMMODATION.
        $app->post('/accommodation/create', 'AccommodationController:postAccommodationCreate'); // CREATE ACCOMMODATION.
        $app->get('/accommodation/update/{id}', 'AccommodationController:getAccommodationUpdate')->setName('accommodation.update'); // UPDATE ACCOMMODATION.
        $app->post('/accommodation/update/{id}', 'AccommodationController:postAccommodationUpdate'); // UPDATE ACCOMMODATION.

        // Download attachments.
        $app->get('/download/{id}', 'EventController:getAbstractDownload')->setName('abstract.download');

        /**
         * Routes reserved to USER ADMINISTRATOR.
         */
        $app->group('', function() use($app, $container) {

            $app->get('/getZipOfAbstracts/{eid}', 'SubscriptionController:getZipOfAbstracts')->setName('event.getZipOfAbstracts'); // Download all Abstracts.
            $app->get('/getEmailExcel/{eid}', 'SubscriptionController:getEmailExcel')->setName('event.getEmailExcel'); // Download list of email Excel.
            $app->post('/event/create', 'EventController:postEventCreate'); // CREATE EVENTS.
            $app->post('/event/update/{id}', 'EventController:postEventUpdate'); // UPDATE EVENTS.
            $app->get('/event/delete/{id}', 'EventController:getEventDelete')->setName('event.delete'); // DELETE EVENT.
            $app->get('/meetup/create', 'MeetUpController:getMeetUpCreate')->setName('meetup.create'); // CREATE MEETUP.
            $app->post('/meetup/create', 'MeetUpController:postMeetUpCreate'); // CREATE MEETUP.
            $app->post('/meetup/update/{id}', 'MeetUpController:postMeetUpUpdate'); // UPDATE MEETUP.
            $app->get('/meetup/delete/{id}', 'MeetUpController:getMeetUpDelete')->setName('meetup.delete'); // DELETE MEETUP.
            $app->get('/event/details/update/{id}', 'EventController:getEventDetailsUpdate')->setName('event.details.update'); // UPDATE EVENTS DETAILS.
            $app->post('/event/details/update/{id}', 'EventController:postEventDetailsUpdate'); // UPDATE EVENTS DETAILS.


        })->add(new AdminMiddleware($container));

    })->add(new RoleMiddleware($container, ['moderator', 'editor']));

})->add(new AuthMiddleware($container));
