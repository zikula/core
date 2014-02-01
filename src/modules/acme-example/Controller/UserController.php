<?php

namespace Acme\ExampleModule\Controller;

use Symfony\Component\HttpFoundation\Response;
use Zikula\Core\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/{name}")
     *
     * @param string $name
     *
     * @return Response
     */
    public function indexAction($name = 'no name')
    {
        return $this->render('AcmeExampleModule:User:index.html.twig', array('name' => $name));
    }
}
