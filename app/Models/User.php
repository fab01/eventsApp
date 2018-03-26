<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'user';

    /**
     * Property which defines the all writable fields in the table.
     * Fields which are not defined within this method won't be filled by the application.
     */
    protected $fillable = [
        'username',
        'email',
        'password',
    ];

    public function setPassword($password)
    {
        $this->update([
           'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }
}
