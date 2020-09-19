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
use Zikula\Bundle\CoreInstallerBundle\Helper\PhpHelper;
use Zikula\Bundle\CoreInstallerBundle\Helper\WizardHelper;

class InstallerController
{
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
    private $locale;

    /**
     * @var bool
     */
    private $installed;

    public function __construct(
        WizardHelper $wizardHelper,
        PhpHelper $phpHelper,
        string $locale,
        string $installed
    ) {
        $this->wizardHelper = $wizardHelper;
        $this->phpHelper = $phpHelper;
        $this->locale = $locale;
        $this->installed = '0.0.0' !== $installed;
    }

    public function install(Request $request, string $stage): Response
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
        $iniWarnings = $this->phpHelper->setUp();
        if (null !== $session && 0 < count($iniWarnings)) {
            $session->getFlashBag()->add('warning', implode('<hr />', $iniWarnings));
        }

        return $this->wizardHelper->processWizard($request, $stage, 'install');
    }
}
