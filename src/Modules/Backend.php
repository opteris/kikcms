<?php

namespace KikCMS\Modules;

use KikCMS\Plugins\BackendNotFoundPlugin;
use KikCMS\Plugins\SecurityPlugin;
use Phalcon\DiInterface;
use Phalcon\Events\Manager;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Backend implements ModuleDefinitionInterface
{
    protected $defaultNamespace = "KikCMS\\Controllers";

    /**
     * @inheritdoc
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        // nothing else needed
    }

    /**
     * @inheritdoc
     */
    public function registerServices(DiInterface $di)
    {
        $defaultNameSpace = $this->defaultNamespace;

        $di->set("dispatcher", function () use ($defaultNameSpace){
            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace($defaultNameSpace);

            $eventsManager = new Manager;
            $eventsManager->attach('dispatch:beforeExecuteRoute', new SecurityPlugin);
            $eventsManager->attach('dispatch:beforeException', new BackendNotFoundPlugin);

            $dispatcher->setEventsManager($eventsManager);

            return $dispatcher;
        });
    }
}