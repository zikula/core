<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Form\TestType;
use App\HookEvent\AppFormHookEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestHookController extends AbstractController
{
    /* @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("/test/hook", name="test_hook")
     */
    public function index(Request $request): Response
    {
        $form = $this->createForm(TestType::class);
        $hook = $this->eventDispatcher->dispatch((new AppFormHookEvent())->setId(543)->setForm($form));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventDispatcher->dispatch($hook->setSubject(['id' => 543, 'text' => 'foo']));
            $this->addFlash('success', sprintf('Form saved! Values: %s & %s', implode(', ', $form->getData()), $hook->getDisplay()));

            return $this->redirectToRoute('test_hook');
        }

        return $this->render('test_hook/index.html.twig', [
            'controller_name' => 'TestHookController',
            'form' => $form->createView(),
            'hook' => $hook
        ]);
    }
}
