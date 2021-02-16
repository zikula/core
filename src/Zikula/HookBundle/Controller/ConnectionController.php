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

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;
use Zikula\Bundle\HookBundle\Entity\Connection;
use Zikula\Bundle\HookBundle\HookEvent\HookEvent;
use Zikula\Bundle\HookBundle\HookEventListener\HookEventListenerInterface;
use Zikula\Bundle\HookBundle\Locator\HookLocator;
use Zikula\Bundle\HookBundle\Repository\HookConnectionRepository;

class ConnectionController
{
    /* @var Environment */
    private $twig;

    /**
     * @var ObjectManager
     */
    private $persistenceManager;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /* @var HookLocator */
    private $hookLocator;

    /* @var HookConnectionRepository */
    private $connectionRepository;

    public function __construct(
        Environment $twig,
        ManagerRegistry $managerRegistry,
        ValidatorInterface $validator,
        HookLocator $hookLocator,
        HookConnectionRepository $connectionRepository
    ) {
        $this->twig = $twig;
        $this->persistenceManager = $managerRegistry->getManager();
        $this->validator = $validator;
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
            'connections' => $this->connectionRepository->findAll()
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
            $connection = $this->connectionRepository->find((int) $postVars['id']);
        }
        $event = $this->hookLocator->getHookEvent($postVars['eventName']);
        $listener = $this->hookLocator->getListener($postVars['listenerName']);
        switch ($postVars['action']) {
            case 'connect':
                $connection = $this->createConnection($event, $listener);
                break;
            case 'disconnect':
                $this->persistenceManager->remove($connection);
                $connection = null;
                break;
            case 'increment':
                $connection->incPriority();
                break;
            case 'decrement':
                $connection->decPriority();
                break;
            default:
                throw new \InvalidArgumentException('Invalid action');
        }
        $this->persistenceManager->flush();

        $content = $this->twig->render('@ZikulaHook/Connection/connection.html.twig', [
            'connection' => $connection,
            'event' => $event,
            'listener' => $listener
        ]);

        return new Response($content);
    }

    private function createConnection(HookEvent $event, HookEventListenerInterface $listener): Connection
    {
        if (null !== $this->connectionRepository->findOneBy(['event' => $event->getClassname(), 'listener' => $listener->getClassname()])) {
            throw new Exception('Connection already exists, cannot create again.');
        }
        if (!\is_subclass_of($event, $listener->listensTo())) {
            throw new \InvalidArgumentException('This listener cannot listen to this event.');
        }
        $connection = new Connection($event->getClassname(), $listener->getClassname());
        $validationErrors = $this->validator->validate($connection);
        if (0 === count($validationErrors)) {
            $this->persistenceManager->persist($connection);
        } else {
            throw new \Exception('Could not create connection (invalid)!');
        }

        return $connection;
    }
}
