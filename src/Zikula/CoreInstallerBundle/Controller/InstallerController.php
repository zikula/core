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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Bundle\CoreInstallerBundle\Helper\WizardHelper;

class InstallerController
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var WizardHelper
     */
    private $wizardHelper;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var bool
     */
    private $installed;

    public function __construct(
        RouterInterface $router,
        WizardHelper $wizardHelper,
        string $locale,
        string $installed
    ) {
        $this->router = $router;
        $this->wizardHelper = $wizardHelper;
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

        return $this->wizardHelper->processWizard($request, $stage, 'install');
    }
}
