<?php
/**
 * Created by PhpStorm.
 * User: fabrizio
 * Date: 29/04/18
 * Time: 12:55
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accommodation extends Model
{
  protected $table = 'accommodation';

  protected $fillable = [
    'title',
  ];
}