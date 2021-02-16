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

namespace Zikula\Bundle\HookBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Zikula\Bundle\HookBundle\Hook\Connection;
use Zikula\Bundle\HookBundle\Locator\HookLocator;
use Zikula\Bundle\HookBundle\Repository\HookConnectionRepository;

class ConnectionController
{
    /* @var Environment */
    private $twig;

    /* @var HookLocator */
    private $hookLocator;

    /* @var HookConnectionRepository */
    private $connectionRepository;

    public function __construct(
        Environment $twig,
        HookLocator $hookLocator,
        HookConnectionRepository $connectionRepository
    ) {
        $this->twig = $twig;
        $this->hookLocator = $hookLocator;
        $this->connectionRepository = $connectionRepository;
    }

    /**
     * @Route("/hook-connections")
     */
    public function connections(): Response
    {
        $content = $this->twig->render('@ZikulaHook/Connection/connections.html.twig', [
            'locator' => $this->hookLocator,
            'connections' => $this->connectionRepository->getAll()
        ]);

        return new Response($content);
    }

    /**
     * @Route("hook-modify", methods = {"POST"}, options={"expose"=true})
     */
    public function modify(Request $request): Response
    {
        $postVars = $request->request->all();
        if (is_numeric($postVars['id'])) {
            $connection = $this->connectionRepository->get((int) $postVars['id']);
        }
        switch ($postVars['action']) {
            case 'connect':
                // check if already connected?
                $connection = new Connection(400/* this is a bogus ID. should be null on persist */, $postVars['eventName'], $postVars['listenerName']);
                // persist connection
                break;
            case 'disconnect':
                // delete the existing connection @id
                $connection = null;
                break;
            case 'increment':
                $connection->incPriority();
                break;
            case 'decrement':
                $connection->decPriority();
                break;
            default:
                // throw error
        }
        // flush entityManager

        $content = $this->twig->render('@ZikulaHook/Connection/connection.html.twig', [
            'connection' => $connection,
            'event' => $this->hookLocator->getHookEvent($postVars['eventName']),
            'listener' => $this->hookLocator->getListener($postVars['listenerName'])
        ]);

        return new Response($content);
    }
}
