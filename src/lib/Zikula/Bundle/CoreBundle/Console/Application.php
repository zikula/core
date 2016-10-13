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

        return parent::registerCommands();
    }
}
