<?php
/**
 * In our Controller we will call Actions with
 * prefix "get" to indicate the ones who render the View and
 * prefix "post" to indicate the ones who perform actions on submit.
 */
namespace App\Controllers\Auth;

use App\Controllers\Controller as Controller;
use App\Models\User;
use Respect\Validation\Validator as v;

class AuthController extends Controller
{
    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     */
    public function getSignUp($request, $response) {
        return $this->view->render($response, 'controller/auth/signUp.html.twig');
    }

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     */
    public function postSignUp($request, $response) {
        $validation = $this->validator->validate($request, [
            'username' => v::noWhitespace()->notEmpty()->usernameAvailable(),
            'email'    => v::noWhitespace()->notEmpty()->email()->emailAvailable(),
            'password' => v::noWhitespace()->notEmpty(),
            'passwordConfirm' => v::notEmpty()->equals($request->getParam('password')),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.signup'));
        }

        $user = User::create([
            'email'    => $request->getParam('email'),
            'username' => $request->getParam('username'),
            'password' => password_hash($request->getParam('password'), PASSWORD_DEFAULT),
        ]);

        $this->flash->addMessage('success', "Welcome {$user->username}! Your have been correctly registered and signed in!");

        // SignIn on SignUp.
        $this->auth->checkLogin($user->username, $request->getParam('password'));

        /**
         * REDIRECT.
         * Router is an object retrieved from 'container'.
         * Example: $this->container->router->...
         * Check magic method __get() in Base Controller class.
         */
        return $response->withRedirect($this->router->pathFor('home'));
    }

    /**
     * @param $request
     * @param $response
     */
    public function getSignIn($request, $response) {
        $this->view->render($response, 'controller/auth/signIn.html.twig');
    }

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     */
    public function postSignIn($request, $response) {
        $validation = $this->validator->validate($request, [
            'username' => v::notEmpty(),
            'password' => v::notEmpty(),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.signin'));
        }

        $auth = $this->auth->checkLogin(
            $request->getParam('username'),
            $request->getParam('password')
        );

        if (!$auth) {
            $this->flash->addMessage('error', "Your credentials are incorrect!");
            return $response->withRedirect($this->router->pathFor('auth.signin'));
        }

        return $response->withRedirect($this->router->pathFor('home'));
    }

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     */
    public function getSignOut($request, $response) {
        $this->auth->logout();
        return $response->withRedirect($this->router->pathFor('auth.signin'));
    }
}
