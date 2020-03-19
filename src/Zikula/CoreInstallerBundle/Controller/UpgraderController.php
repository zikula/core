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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\YamlDumper;

/**
 * Class UpgraderController
 */
class UpgraderController extends AbstractController
{
    public const ZIKULACORE_MINIMUM_UPGRADE_VERSION = '1.4.3';

    private $installed;

    public function __construct(ContainerInterface $container, string $installed)
    {
        parent::__construct($container);
        $this->installed = $installed;
        $this->router = $container->get('router');
        $this->form = $this->container->get('form.factory');
    }

    public function upgradeAction(Request $request, $stage): Response
    {
        if (version_compare($this->installed, ZikulaKernel::VERSION, '=')) {
            $stage = 'complete';
        }
        // not installed?
        if ('0.0.0' === $this->installed) {
            return new RedirectResponse($this->router->generate('install'));
        }

        $yamlDumper = new YamlDumper($this->container->get('kernel')->getProjectDir() . '/config', 'services_custom.yaml');
        $yamlDumper->setParameter('upgrading', true);
        $request->setLocale($this->container->getParameter('locale'));

        return $this->controllerHelper->processWizard($request, $stage, 'upgrade', $this->form, $yamlDumper);
    }
}
