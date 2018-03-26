<?php
namespace App\Validation\Rules;

use App\Models\User;
use Respect\Validation\Rules\AbstractRule;

class EmailAvailable extends AbstractRule
{
    /**
     * @param $input
     * @return bool
     */
    public function validate($input)
    {
        // $input is the value submitted (example: string => 'me@email.com').
        return User::where('email', $input)->count() === 0;
    }
}
