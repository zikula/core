<?php

namespace Zikula\Bundle\CoreBundle\Command;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Command\CacheClearCommand as BaseCommand;

class CacheClearCommand extends BaseCommand
{
    /**
     * @param KernelInterface $parent
     * @param string          $namespace
     * @param string          $parentClass
     * @param string          $warmupDir
     *
     * @return KernelInterface
     */
    protected function getTempKernel(KernelInterface $parent, $namespace, $parentClass, $warmupDir)
    {
        $kernel = parent::getTempKernel($parent, $namespace, $parentClass, $warmupDir);

        $kernel->setAutoloader($this->getContainer()->get('kernel')->getAutoloader());

        return $kernel;
    }
}
