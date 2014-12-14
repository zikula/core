<?php

namespace Zikula\Bundle\CoreBundle\Console;

use Symfony\Bundle\FrameworkBundle\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Event\GenericEvent;

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
        // ensure that we have admin access
        $this->bootstrap();
        try {
            $this->loginAsAdministrator();
        } catch (\Exception $e) {
            die(__('Sorry, an exception occurred:') . ' ' . $e->getMessage());
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

        //initialise service manager
        $sm = \ServiceUtil::getManager();

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
        \UserUtil::setUserByUid($adminId);

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
