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
}