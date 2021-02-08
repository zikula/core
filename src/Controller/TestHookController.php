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

use App\Hook\AppDisplayHookEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestHookController extends AbstractController
{
    /**
     * @Route("/test/hook", name="test_hook")
     */
    public function index(): Response
    {
        return $this->render('test_hook/index.html.twig', [
            'controller_name' => 'TestHookController',
        ]);
    }
}
