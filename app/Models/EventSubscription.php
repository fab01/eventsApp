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

    public function getAll() {
        return $this->leftJoin('subscriber', 'subscriber.id', '=', 'subscriber_id')
          ->leftJoin('accommodation', 'accommodation.id', '=', 'accommodation_id')
          ->select('event_subscription.*', 'accommodation.title', 'subscriber.name', 'subscriber.surname', 'subscriber.email')
          ->where('event_id', '=', $_SESSION['eid'])
          ->get();
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
