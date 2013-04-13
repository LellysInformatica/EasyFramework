<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Controller\Event;

use Easy\Mvc\Controller\Controller;
use Easy\Network\Request;
use Symfony\Component\EventDispatcher\Event;

class StartupEvent extends Event
{

    protected $controller;
    protected $request;

    public function __construct(Controller $controller, Request $request)
    {
        $this->controller = $controller;
        $this->request = $request;
    }

    public function getController()
    {
        return $this->controller;
    }

    /**
     * Gets the request object
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function setController($controller)
    {
        $this->controller = $controller;
        return $this;
    }

    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

}
