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
        $event = Event::where('status', 1)->first();
        $status = (is_object($event)) ? $event->status : NULL;

        if (NULL !== $status) {
            $subscription = EventSubscription::where('subscriber_id', $_SESSION['uid'])->where('event_id', $event->id)->first();
            if (NULL === $subscription && NULL !== $_SESSION['eid'] && $event->status == 1) {
                return $response->withRedirect($this->router->pathFor('event.subscription.create'));
            } else if (NULL !== $subscription && NULL !== $_SESSION['eid'] && $event->status == 1) {
                return $response->withRedirect($this->router->pathFor('event.subscription.update', ['id' => $subscription->id]));
            }
        }

        return $this->view->render($response, 'controller/home/index.html.twig', ['status' => $status]);
    }
}

