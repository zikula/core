<?php

namespace App\Controller;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Hook\AppDisplayHookEvent;

class TestHookController extends AbstractController
{
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("/test/hook", name="test_hook")
     */
    public function index(): Response
    {
        $this->eventDispatcher->dispatch($displayHook = new AppDisplayHookEvent('foo'));

        return $this->render('test_hook/index.html.twig', [
            'controller_name' => 'TestHookController',
            'hookResponses' => $displayHook->getResponses()
        ]);
    }
}
