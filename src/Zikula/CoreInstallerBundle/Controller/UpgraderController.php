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

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Bundle\CoreInstallerBundle\Helper\ControllerHelper;

/**
 * Class UpgraderController
 */
class UpgraderController
{
    public const ZIKULACORE_MINIMUM_UPGRADE_VERSION = '1.4.3';

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var FormFactoryInterface
     */
    private $form;

    /**
     * @var ControllerHelper
     */
    private $controllerHelper;

    /**
     * @var string
     */
    private $installed;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @var string
     */
    private $locale;

    public function __construct(
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        ControllerHelper $controllerHelper,
        string $installed,
        string $projectDir,
        string $locale
    ) {
        $this->router = $router;
        $this->form = $formFactory;
        $this->controllerHelper = $controllerHelper;
        $this->installed = $installed;
        $this->projectDir = $projectDir;
        $this->locale = $locale;
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

        $yamlDumper = new YamlDumper($this->projectDir . '/config', 'services_custom.yaml');
        $yamlDumper->setParameter('upgrading', true);
        $request->setLocale($this->locale);

        return $this->controllerHelper->processWizard($request, $stage, 'upgrade', $this->form, $yamlDumper);
    }
}
