<?php
/**
 * Base Controller class.
 * This way we can pass $container to all controllers which extend this class.
 * Don't touch this.
 */

namespace App\Controllers;

class Controller
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Shortcut to invoke $container methods.
     * Example:
     *    instead of
     *      $this->container->view->render();
     *    we can use:
     *      $this->view->render();
     */
    public function __get($property)
    {
        if ($this->container->{$property}) {
            return $this->container->{$property};
        }
    }
}

