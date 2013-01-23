<?php

namespace Easy\Mvc\Routing\Event;

use Easy\Mvc\Controller\Controller;
use Symfony\Component\EventDispatcher\Event;

class BeforeCallEvent extends Event
{

    protected $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function getController()
    {
        return $this->controller;
    }

}