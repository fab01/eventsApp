<?php
/**
 * Auth Class is the class where we come when we want do anything
 * in regards the Authentication.
 * In example:
 *  - Check if User is loggedIn or not
 *  - attempt to authentication,
 *  - grab current authenticate user
 * and so on.
 */
namespace App\Auth;

use App\Models\Event;
use App\Models\User;
use App\Models\Subscriber;

class Auth
{
    private $client_secret = 'admin';
    private $client_id = 'd943aced-e379-4bcb-b17c-81ba484a1c90';
    private $api_username;
    private $api_password;
    private $role;

    /**
     * @var string
     * if administrator is not the current role the API will return anyway
     * the roles for the user who is trying to login.
     */
    private $scope = 'administrator';

    /**
     * @return bool|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null|static|static[]
     *
     */
    public function user() {
        return (isset($_SESSION['user'])) ? $_SESSION['user'] : false;
    }

    /**
     * @return bool
     * Verify if user is currently logged in.
     */
    public function isLoggedIn() {
        return isset($_SESSION['user']);
    }

    /**
     * @return bool
     */
    public static function isAdmin() {
        if (isset($_SESSION['user'])) {
            if (null !== $_SESSION['user'] && null !== $_SESSION['user']->roles) {
                if (in_array('administrator', $_SESSION['user']->roles)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param array $admitted_roles
     *
     * @return bool
     */
    public static function hasRole(array $admitted_roles = []) {
        if (isset($_SESSION['user'])) {
            if (null !== $_SESSION['user']->roles && !empty($admitted_roles)) {

                // Kill the execution and return true by default if is Admin.
                if (self::isAdmin())
                    return true;

                // If there are matches return true.
                $matches = array_intersect($_SESSION['user']->roles, $admitted_roles);
                if (count($matches) > 0)
                    return true;
            }
        }

        return false;
    }

    /**
     * @param $username
     * @param $password
     *
     * @return bool
     *
     * Perform Check login.
     */
    public function checkLogin($username, $password) {
        // Get the username.
        //$user = User::where('username', $username)->first();
        $this->api_username = $username;
        $this->api_password = $password;
        $user = $this->getUserDateD7();
        $user_data = $user['user'];

        if (NULL !== $user_data && $user_data !== 'Forbidden' && is_array($user_data)) {

            $objUser = (object) $user_data;
            $subscriber = Subscriber::where('uid', $objUser->uid)->first();

            if (NULL === $subscriber) {
              $subscriber = Subscriber::create([
                'uid'     => $objUser->uid,
                'name'    => $objUser->field_first_name['und'][0]['value'],
                'surname' => $objUser->field_last_name['und'][0]['value'],
                'email'   => $objUser->mail
              ]);
            }
            /* @todo: Se l'utente aggiorna i propri dati lato Drupal questi devono essere aggiornati anche lato App. */

            $event = new Event();

            $_SESSION['user'] = $objUser;
            $_SESSION['uid'] = $subscriber->id;
            $_SESSION['eid'] = $event->currentEvent()->id;

            return true;
        }
        return false;
    }

    /**
     * Execute Logout.
     */
    public function logout() {
        unset($_SESSION['user']);
    }

    /**
     * @param $request
     *
     * @return bool|mixed
     */
    protected function getAccessToken ()
    {
        // try to get an access token
        $url = 'http://iim.d7.iim/oauth/token';
        $params = [
          "username" => $this->api_username,
          "password" => $this->api_password,
          "client_id" => $this->client_id,
          "client_secret" => $this->client_secret,
          "redirect_uri" => 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"],
          "grant_type" => "password",
          "scope" => $this->scope,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, constant("CURLOPT_" . 'HEADER'), 'Content-Type: application/x-www-form-urlencoded');
        curl_setopt($ch, constant("CURLOPT_" . 'RETURNTRANSFER'), true);
        curl_setopt($ch, constant("CURLOPT_" . 'URL'), $url);
        curl_setopt($ch, constant("CURLOPT_" . 'POST'), true);

        curl_setopt($ch, constant("CURLOPT_" . 'POSTFIELDS'), $params);

        $json_response = curl_exec($ch);

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // evaluate for success response
        if ($status != 200) {
            return FALSE;
        }
        curl_close($ch);
        return json_decode($json_response, TRUE);
    }

    public function getUserDateD7()
    {
        // try to get an access token
        $url = 'http://iim.d7.dry/auth_service/user/login';
        $params = [
          "username" => $this->api_username,
          "password" => $this->api_password,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, constant("CURLOPT_" . 'HEADER'), 'Content-Type: application/x-www-form-urlencoded');
        curl_setopt($ch, constant("CURLOPT_" . 'RETURNTRANSFER'), true);
        curl_setopt($ch, constant("CURLOPT_" . 'URL'), $url);
        curl_setopt($ch, constant("CURLOPT_" . 'POST'), true);

        curl_setopt($ch, constant("CURLOPT_" . 'POSTFIELDS'), $params);

        $json_response = curl_exec($ch);

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // evaluate for success response
        if ($status != 200) {
            return FALSE;
        }
        curl_close($ch);
        /* DEBUG - Users' data returned by Drupal's Api
        var_dump(json_decode($json_response, TRUE));
        die();
        */
        return json_decode($json_response, TRUE);
    }

    /**
     * @return mixed|string
     */
    /*public function getUserData()
    {
        if ($this->getAccessToken() && is_array($this->getAccessToken())) {

            $token = $this->getAccessToken();
            $access_token = $token['access_token'];
            $refresh_token = $token['access_token'];
            $token_type = $token['token_type'];

            $url = 'http://iim.d8/api/1.0/users/'.$this->api_username;
            $authorization = "Authorization: " . $token_type . " " . $access_token;

            $ch = curl_init();

            curl_setopt($ch, constant("CURLOPT_" . 'URL'), $url);
            curl_setopt($ch, constant("CURLOPT_" . 'RETURNTRANSFER'), true);
            curl_setopt($ch, constant("CURLOPT_" . 'HTTPHEADER'), array(
              'Content-Type: application/json',
              $authorization
            ));

            $result = curl_exec($ch);
            curl_close($ch);
            return json_decode($result, TRUE);
        }
        else {
            return 'Forbidden';
        }
    }*/
}