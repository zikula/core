<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Console;

use Symfony\Bundle\FrameworkBundle\Console\Application as BaseApplication;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreInstallerBundle\Util\VersionUtil;

class Application extends BaseApplication
{
    private $kernel;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel A KernelInterface instance
     */
    public function __construct(KernelInterface $kernel)
    {
        parent::__construct($kernel);

        $this->kernel = $kernel;

        $this->setName('Zikula');
        $this->setVersion(\ZikulaKernel::VERSION.' - '.$kernel->getName().'/'.$kernel->getEnvironment().($kernel->isDebug() ? '/debug' : ''));
    }

    protected function registerCommands()
    {
        if ($this->kernel->getContainer()->getParameter('installed') !== true) {
            // composer is called, the system may not be installed yet
            return parent::registerCommands();
        }

        // ensure that we have admin access
        if ($this->kernel->getContainer()->getParameter('installed') === true) {
            // don't attempt to login if the Core needs an upgrade
            VersionUtil::defineCurrentInstalledCoreVersion($this->kernel->getContainer());
            $currentVersion = $this->kernel->getContainer()->getParameter(\ZikulaKernel::CORE_INSTALLED_VERSION_PARAM);
            // @todo login is pointless for CLI isn't it?
            if (version_compare($currentVersion, \ZikulaKernel::VERSION, '==')) {
                try {
                    $this->loginAsAdministrator();
                } catch (\Exception $e) {
                    $output = new \Symfony\Component\Console\Output\ConsoleOutput();
                    $this->renderException($e, $output);
                }
            }
        }

        return parent::registerCommands();
    }

    /**
     * Grants admin access for console commands (#1908).
     * This avoids subsequent permission problems from any components used.
     * @deprecated remove at Core-2.0
     */
    protected function loginAsAdministrator()
    {
        $adminId = 2;

        // no need to do anything if there is already an admin login
        if (\UserUtil::isLoggedIn()) {
            if (\UserUtil::getVar('uid') == $adminId) {
                return;
            }

            if (\SecurityUtil::checkPermission('::', '::', ACCESS_ADMIN)) {
                return;
            }
        }

        // login / impersonate now
        $this->kernel->getContainer()->get('session')->set('uid', $adminId);

        // check if it worked
        if (!\UserUtil::isLoggedIn()) {
            throw new AccessDeniedException(__('Error! Auto login failed.'));
        }

        // check if permissions have become available
        if (!\SecurityUtil::checkPermission('::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException(__('Error! Insufficient permissions after auto login.'));
        }
    }
}
