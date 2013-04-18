<?php

namespace Acme\ExampleModule\Controller;

use Zikula\Core\Controller\AbstractController;

class DefaultController extends AbstractController
{
    public function indexAction($name = 'no name')
    {
        return $this->render('AcmeExampleModule:Default:index.html.twig', array('name' => $name));
    }
}
