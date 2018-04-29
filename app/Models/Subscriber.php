<?php
/**
 * Created by PhpStorm.
 * User: fabrizio
 * Date: 01/04/18
 * Time: 15:46
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    protected $table = 'subscriber';

    protected $fillable = [
      'uid',
      'name',
      'surname',
      'email',
    ];
}
