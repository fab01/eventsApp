<?php

namespace App\Form;

class Form
{
    protected $form;

    public function __construct($container)
    {
        $this->form = $container;
    }

    public function getMessage($fieldName)
    {
        $oldMessages = $this->form->view->getEnvironment()->getGlobals();
        if (isset($oldMessages['old'][$fieldName])) {
            return $oldMessages['old'][$fieldName];
        }
        return "";
    }

    public function getFields($class_name)
    {
        $class = "App\\Form\\Fields\\{$class_name}Fields";
        if (class_exists($class)) {
            return new $class( $this->form );
        } else {
            throw new \Exception("Class {$class_name} does not exists!");
        }
    }
}
