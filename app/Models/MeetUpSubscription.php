<?php
/**
 * Created by PhpStorm.
 * User: fabrizio
 * Date: 02/04/18
 * Time: 01:12
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetUpSubscription extends Model
{
    protected $table = 'meetup_subscription';

    protected $fillable = [
      'meetup_id',
      'subscriber_id',
    ];

    public function allSubscribersByMeetupId($id)
    {
        return $this->count()->where("meetup_id", $id);
    }
}
