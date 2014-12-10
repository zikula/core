<?php

namespace Zikula\Bundle\CoreBundle\Console;

use Symfony\Bundle\FrameworkBundle\Console\Application as BaseApplication;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Application extends BaseApplication
{
    /**
     * Constructor.
     *
     * @param KernelInterface $kernel A KernelInterface instance
     */
    public function __construct(KernelInterface $kernel)
    {
        parent::__construct($kernel);

        $this->setName('Zikula');
        $this->setVersion(\Zikula_Core::VERSION_NUM.' - '.$kernel->getName().'/'.$kernel->getEnvironment().($kernel->isDebug() ? '/debug' : ''));

        // ensure that we have admin access
        try {
            $this->loginAsAdministrator();
        } catch (\Exception $e) {
            die(__('Sorry, an exception occurred:') . ' ' . $e->getMessage());
        }
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
