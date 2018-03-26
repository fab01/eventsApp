<?php

namespace App\Controllers;

use App\Controllers\Controller as Controller;
use App\Models\User;

class HomeController extends Controller
{
    public function index($request, $response)
    {
        //var_dump($request->getParam('name'));
        return $this->view->render($response, 'controller/home/index.html.twig');
    }
}

