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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zikula\Bundle\CoreInstallerBundle\Helper\ControllerHelper;
use Zikula\Bundle\CoreInstallerBundle\Helper\WizardHelper;

class InstallerController
{
    /**
     * @var WizardHelper
     */
    private $wizardHelper;

    /**
     * @var ControllerHelper
     */
    private $controllerHelper;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var bool
     */
    private $installed;

    public function __construct(
        WizardHelper $wizardHelper,
        ControllerHelper $controllerHelper,
        string $locale,
        string $installed
    ) {
        $this->wizardHelper = $wizardHelper;
        $this->controllerHelper = $controllerHelper;
        $this->locale = $locale;
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

        $request->setLocale($this->locale);
        $session = $request->hasSession() ? $request->getSession() : null;
        $iniWarnings = $this->controllerHelper->initPhp();
        if (null !== $session && 0 < count($iniWarnings)) {
            $session->getFlashBag()->add('warning', implode('<hr />', $iniWarnings));
        }

        return $this->wizardHelper->processWizard($request, $stage, 'install');
    }
}
