<?php

namespace App\Controllers;

use \App\Models\User as User;

class UserController extends Controller
{
    public function getAll($request, $response)
    {
        return $this->view->render($response, 'controller/user/all.html.twig', ['users' => User::all()]);
    }
}
