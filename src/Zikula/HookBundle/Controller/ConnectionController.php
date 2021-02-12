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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
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
        $content = $this->twig->render('@ZikulaHook/Connection/connection.html.twig', [
            'locator' => $this->hookLocator,
            'connections' => $this->connectionRepository->getAll()
        ]);

        return new Response($content);
    }
}
