<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetUp extends Model
{
    protected $table = 'meetup';

    protected $fillable = [
      'title',
      'description',
      'event_id',
      'date',
      'available_places',
    ];

    public function allWithCountSubscribers($id, $sid = 0) {
        return $this->leftJoin('meetup_subscription', 'meetup_subscription.meetup_id', '=', 'meetup.id')
          ->selectRaw('meetup.*, count(distinct meetup_subscription.id) as subscriptionCount, count(case meetup_subscription.subscriber_id when '.$sid.' then 1 else null end) as sid')
          ->where('meetup.event_id', $id)
          ->where('meetup.deleted', 0)
          ->groupBy('meetup.id')
          ->get();
    }
}
