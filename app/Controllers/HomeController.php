<?php

namespace App\Controllers;

use App\Auth\Auth;
use App\Controllers\Controller as Controller;
use App\Models\Event;
use App\Models\EventSubscription;
use App\Models\User;

class HomeController extends Controller
{
    public function index($request, $response)
    {
        $subscription = EventSubscription::where('subscriber_id', $_SESSION['uid'])->first();
        if (NULL === $subscription && NULL !== $_SESSION['eid']) {
            return $response->withRedirect($this->router->pathFor('event.subscription.create'));
        } else if (NULL !== $subscription && NULL !== $_SESSION['eid']) {
            return $response->withRedirect($this->router->pathFor('event.subscription.update', ['id' => $subscription->id]));
        }

        $event = Event::where('status', 1)->first();
        $status = (is_object($event)) ? $event->status : NULL;
        return $this->view->render($response, 'controller/home/index.html.twig', ['status' => $status]);
    }
}

