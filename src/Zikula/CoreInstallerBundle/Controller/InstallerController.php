<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class InstallerController
 */
class InstallerController extends AbstractController
{
    private $installed;

    public function __construct(ContainerInterface $container, string $installed)
    {
        parent::__construct($container);
        $this->router = $container->get('router');
        $this->form = $this->container->get('form.factory');
        $this->installed = '0.0.0' !== $installed;
    }

    public function installAction(Request $request, string $stage): Response
    {
        // already installed?
        if ('complete' !== $stage && $this->installed) {
            $stage = 'installed';
        }

        // not installed but requesting installed stage?
        if ('installed' === $stage && !$this->installed) {
            $stage = 'notinstalled';
        }

        $request->setLocale($this->container->getParameter('locale'));

        return $this->controllerHelper->processWizard($request, $stage, 'install', $this->form);
    }
}
