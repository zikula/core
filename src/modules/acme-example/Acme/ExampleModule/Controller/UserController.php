<?php

namespace Acme\ExampleModule\Controller;

use Symfony\Component\HttpFoundation\Response;
use Zikula\Core\Controller\AbstractController;

class UserController extends AbstractController
{
    public function indexAction($name = 'no name')
    {
        return $this->render('AcmeExampleModule:User:index.html.twig', array('name' => $name));
    }
}
