<?php
/**
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
use Zikula\Core\Event\GenericEvent;
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
        $this->setVersion(\Zikula_Core::VERSION_NUM.' - '.$kernel->getName().'/'.$kernel->getEnvironment().($kernel->isDebug() ? '/debug' : ''));
    }

    protected function registerCommands()
    {
        if ($this->kernel->getContainer()->getParameter('installed') !== true) {
            // composer is called, the system may not be installed yet
            return parent::registerCommands();
        }

        // ensure that we have admin access
        $this->bootstrap();
        if ($this->kernel->getContainer()->getParameter('installed') === true) {
            // don't attempt to login if the Core needs an upgrade
            VersionUtil::defineCurrentInstalledCoreVersion($this->kernel->getContainer());
            if (defined('ZIKULACORE_CURRENT_INSTALLED_VERSION') && version_compare(ZIKULACORE_CURRENT_INSTALLED_VERSION, \Zikula_Core::VERSION_NUM, '==')) {
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
     * Initialises own (and legacy) components, like service manager.
     */
    protected function bootstrap()
    {
        // taken from lib/bootstrap.php

        // legacy handling
        $core = new \Zikula_Core();
        $core->setKernel($this->kernel);
        $core->boot();

        // these two events are called for BC only. remove in 2.0.0
        $core->getDispatcher()->dispatch('bootstrap.getconfig', new GenericEvent($core));
        $core->getDispatcher()->dispatch('bootstrap.custom', new GenericEvent($core));

        foreach ($GLOBALS['ZConfig'] as $config) {
            $core->getContainer()->loadArguments($config);
        }
        $GLOBALS['ZConfig']['System']['temp'] = $core->getContainer()->getParameter('temp_dir');
        $GLOBALS['ZConfig']['System']['datadir'] = $core->getContainer()->getParameter('datadir');
        $GLOBALS['ZConfig']['System']['system.chmod_dir'] = $core->getContainer()->getParameter('system.chmod_dir');

        \ServiceUtil::getManager($core);
        \EventUtil::getManager($core);
        $core->attachHandlers('config/EventHandlers');

        return $core;
    }

    /**
     * Grants admin access for console commands (#1908).
     * This avoids subsequent permission problems from any components used.
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
