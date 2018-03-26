<?php

namespace App\Validation\Rules;

use App\Models\User;
use Respect\Validation\Rules\AbstractRule;

class UsernameAvailable extends AbstractRule
{
    /**
     * This methid returns always a boolean value.
     *
     * @param $input
     * @return bool
     */
    public function validate($input)
    {
        // $input is the value submitted (example: string => 'myusername').
        return User::where('username', $input)->count() === 0;
    }
}