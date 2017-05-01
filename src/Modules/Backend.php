<?php

namespace KikCMS\Modules;

use KikCMS\Plugins\BackendNotFoundPlugin;
use KikCMS\Plugins\SecurityPlugin;
use Phalcon\Loader;
use Phalcon\DiInterface;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Events\Manager as EventManager;

class Backend implements ModuleDefinitionInterface
{
    /**
     * Register a specific autoloader for the module
     * @param DiInterface $di
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        $loader = new Loader();

        $loader->registerNamespaces([
            "KikCMS\\Controllers" => __DIR__ . "../Controllers/",
            "KikCMS\\Models"      => __DIR__ . "../Models/",
        ]);

        $loader->register();
    }

    /**
     * Register specific services for the module
     * @param DiInterface $di
     */
    public function registerServices(DiInterface $di)
    {
        $di->set("dispatcher", function () {
            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace("KikCMS\\Controllers");

            $eventsManager = new EventManager;
            $eventsManager->attach('dispatch:beforeExecuteRoute', new SecurityPlugin);
            $eventsManager->attach('dispatch:beforeException', new BackendNotFoundPlugin);

            $dispatcher->setEventsManager($eventsManager);

            return $dispatcher;
        });
    }
}