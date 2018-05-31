<?php

namespace App\Controllers;

use App\Auth\Auth;
use App\Controllers\Controller as Controller;
use App\Models\EventSubscription;
use App\Models\User;

class HomeController extends Controller
{
    public function index($request, $response)
    {
        //var_dump($request->getParam('name'));
        //if (!Auth::isAdmin()) {}
        //
        $subscription = EventSubscription::where('subscriber_id', $_SESSION['uid'])->first();
        if (NULL === $subscription && NULL !== $_SESSION['eid']) {
            return $response->withRedirect($this->router->pathFor('event.subscription.create'));
        }
        return $this->view->render($response, 'controller/home/index.html.twig');
    }
}

