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

namespace Zikula\Bundle\CoreInstallerBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Bundle\CoreInstallerBundle\Helper\PhpHelper;
use Zikula\Bundle\CoreInstallerBundle\Helper\WizardHelper;

class UpgraderController
{
    public const ZIKULACORE_MINIMUM_UPGRADE_VERSION = '1.4.3';

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var WizardHelper
     */
    private $wizardHelper;

    /**
     * @var PhpHelper
     */
    private $phpHelper;

    /**
     * @var string
     */
    private $installed;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var string
     */
    private $locale;

    public function __construct(
        RouterInterface $router,
        WizardHelper $wizardHelper,
        PhpHelper $phpHelper,
        string $installed,
        string $cacheDir,
        string $locale
    ) {
        $this->router = $router;
        $this->wizardHelper = $wizardHelper;
        $this->phpHelper = $phpHelper;
        $this->installed = $installed;
        $this->cacheDir = $cacheDir;
        $this->locale = $locale;
    }

    public function upgrade(Request $request, $stage): Response
    {
        if (version_compare($this->installed, ZikulaKernel::VERSION, '=')) {
            $stage = 'complete';
        }
        // not installed?
        if ('0.0.0' === $this->installed) {
            return new RedirectResponse($this->router->generate('install'));
        }

        $yamlDumper = new YamlDumper($this->cacheDir, 'temp_params.yaml');
        $yamlDumper->setParameter('upgrading', true);
        $request->setLocale($this->locale);
        $session = $request->hasSession() ? $request->getSession() : null;
        $iniWarnings = $this->phpHelper->setUp();
        if (null !== $session && 0 < count($iniWarnings)) {
            $session->getFlashBag()->add('warning', implode('<hr />', $iniWarnings));
        }

        return $this->wizardHelper->processWizard($request, $stage, 'upgrade', $yamlDumper);
    }
}
