<?php

namespace App\Extensions;

use Slim\Views\TwigExtension as TwigExtension;
use App\Auth\Auth as Auth;

class TwigExtensionAsserts extends TwigExtension
{
    protected $uri;

    public function __construct($router, $uri)
    {
        parent::__construct($router, $uri);
        $this->uri = $uri;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('path_for', array($this, 'pathFor')),
            new \Twig_SimpleFunction('base_url', array($this, 'baseUrl')),
            new \Twig_SimpleFunction('is_current_path', array($this, 'isCurrentPath')),
            new \Twig_SimpleFunction('assets', array($this, 'assets')),
            new \Twig_SimpleFunction('has_role', array($this, 'hasRole'))
        ];
    }

    public function assets($url)
    {
        if (method_exists($this->uri, 'getBaseUrl')) {
            return $this->uri->getBaseUrl() . '/assets/' . $url;
        }
    }

    public static function hasRole($role) {
        return Auth::hasRole($role);
    }
}
