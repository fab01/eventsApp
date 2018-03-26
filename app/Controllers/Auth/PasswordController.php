<?php

namespace App\Controllers\Auth;

use App\Models\User;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;

class PasswordController extends Controller
{
    public function getPasswordChange($request, $response)
    {
        return $this->view->render($response, 'controller/password/change.html.twig');
    }

    public function postPasswordChange($request, $response)
    {
        $validation = $this->validator->validate($request, [
            'passwordCurrent' => v::noWhitespace()->notEmpty()->matchesPassword($this->auth->user()->password),
            'password' => v::noWhitespace()->notEmpty(),
            'passwordConfirm' => v::notEmpty()->equals($request->getParam('password')),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.password.change'));
        }

        /**
         * $this->auth->user() method returns App\Models\User::find($user->id).
         * Through this one we can access methods of Model class 'User'.
         *
         * setPassword() returns Illuminate\Database\Eloquent\Model::update() method
         * straight forward id the context of the current User grabbed by Auth class.
         */
        $this->auth->user()->setPassword($request->getParam('password'));

        $this->flash->addMessage('info', "Password has been changed!");

        return $response->withRedirect($this->router->pathFor('home'));
    }
}