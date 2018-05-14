<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'event';

    protected $fillable = [
      'title',
      'status',
    ];

    /**
     * This method returns a full select on Event table plus
     * the count of meet up currently associated to the event
     * and
     * the count of subscribers who joined the event.
     *
     * Original SQL:
     *
     * SELECT
     * event.*, count(distinct meetup.id) AS meetupCount, count(distinct event_subscription.id) AS subscriptionCount
     * FROM `event`
     * LEFT JOIN `meetup` ON meetup.event_id=event.id
     * LEFT JOIN `event_subscription` ON event_subscription.event_id=event.id
     * group by event.id
     */
    public function allWithCountMeetUp() {
        return $this->leftJoin('meetup', 'meetup.event_id', '=', 'event.id')
          ->leftJoin('event_subscription', 'event_subscription.event_id', '=', 'event.id')
          ->selectRaw('event.*, count(distinct event_subscription.id) as subscriptionCount, count(case meetup.deleted when 0 then 1 else null end) as meetupCount')
          ->where('event.deleted', 0)
          ->groupBy('event.id')
          ->get();
    }

    public function isActive($id) {
        $status = $this->find($id);
        if ($status->status == 1) {
            return true;
        }
        return false;
    }

    public function currentEvent() {
        return $this->select('id')->where('status', '=', 1)->first();
    }
}