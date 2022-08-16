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

namespace Zikula\ExtensionsModule\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\ExtensionsModule\Helper\BundleSyncHelper;
use Zikula\ExtensionsModule\Helper\ExtensionHelper;

abstract class AbstractExtensionCommand extends Command
{
    public function __construct(
        protected readonly ExtensionRepositoryInterface $extensionRepository,
        protected readonly BundleSyncHelper $bundleSyncHelper,
        protected readonly ExtensionHelper $extensionHelper,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly ZikulaHttpKernelInterface $kernel
    ) {
        parent::__construct();
    }

    protected function isInstalled(string $bundleName)
    {
        if (null !== $extension = $this->extensionRepository->findOneBy(['name' => $bundleName])) {
            if (in_array($extension->getState(), [Constant::STATE_ACTIVE, Constant::STATE_INACTIVE], true)) {
                return $extension;
            }
        }

        return false;
    }

    protected function reSync($reboot = true): void
    {
        $extensionsInFileSystem = $this->bundleSyncHelper->scanForBundles();
        $this->bundleSyncHelper->syncExtensions($extensionsInFileSystem);
        if ($reboot) {
            $this->kernel->reboot(null);
        }
    }
}
