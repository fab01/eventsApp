<?php
/**
 * Created by PhpStorm.
 * User: fabrizio
 * Date: 06/05/18
 * Time: 22:45
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventSubscription extends Model
{
    protected $table = 'event_subscription';

    protected $fillable = [
      'event_id',
      'subscriber_id',
      'accommodation_id',
      'one_night',
      'abstract',
      'apply',
    ];

    public function getAll($eid = null) 
    {
        $event_id = (null === $eid) ? $_SESSION['eid'] : $eid;

        return $this->leftJoin('subscriber', 'subscriber.id', '=', 'subscriber_id')
            ->leftJoin('accommodation', 'accommodation.id', '=', 'accommodation_id')
            ->select('event_subscription.*', 'accommodation.title', 'subscriber.name', 'subscriber.surname', 'subscriber.email')
            ->where('event_id', '=', $event_id)
            ->orderBy('subscriber.surname', 'asc')
            ->orderBy('subscriber.name', 'asc')
            ->get();
    }

    public function mySubscriptionDetails() {
        return $this->leftJoin('accommodation', 'accommodation.id', '=', 'accommodation_id')
          ->select('event_subscription.*', 'accommodation.title')
          ->where('event_id', '=', $_SESSION['eid'])
          ->where('subscriber_id', '=', $_SESSION['uid'])
          ->first();
    }

    public function getSubscriber($id) {
        return $this->leftJoin('subscriber', 'subscriber.id', '=', 'subscriber_id')
          ->select('event_subscription.*', 'subscriber.name', 'subscriber.surname')
          ->where('event_subscription.id', '=', $id)
          ->first();
    }

    public function isAuthorized($id)
    {
        $subscription = $this->find($id);
        if (is_object($subscription)) {
            if ($subscription->subscriber_id === $_SESSION['uid']) {
                return true;
            }
        }
        return false;
    }
}
