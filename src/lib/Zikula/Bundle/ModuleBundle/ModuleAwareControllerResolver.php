<?php

namespace Zikula\Bundle\ModuleBundle;

use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpFoundation\Request;

class ModuleAwareControllerResolver implements ControllerResolverInterface
{
    /**
     * @var ControllerResolverInterface
     */
    private $delegate;

    /**
     * @var ZikulaKernel
     */
    private $kernel;

    public function __construct($delegate, $kernel)
    {
        $this->kernel = $kernel;
        $this->delegate = $delegate;
    }

    public function getArguments(Request $request, $controller)
    {
        return $this->delegate->getArguments($request, $controller);
    }

    public function getController(Request $request)
    {
        $controller = $this->delegate->getController($request);

        if (is_array($controller) && is_object($controller[0])) {
            if ($this->kernel->isClassInModule(get_class($controller[0]))
                && !$this->kernel->isClassInActiveModule(get_class($controller[0]))) {
                $controller = false;
            }
        }

        return $controller;
    }
}
