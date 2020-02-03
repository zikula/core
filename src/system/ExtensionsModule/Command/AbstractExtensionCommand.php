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

namespace Zikula\ExtensionsModule\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockRepositoryInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\ExtensionsModule\Helper\BundleSyncHelper;
use Zikula\ExtensionsModule\Helper\ExtensionDependencyHelper;
use Zikula\ExtensionsModule\Helper\ExtensionHelper;

abstract class AbstractExtensionCommand extends Command
{
    /**
     * @var ExtensionRepositoryInterface
     */
    protected $extensionRepository;

    /**
     * @var BlockRepositoryInterface
     */
    protected $blockRepository;

    /**
     * @var ExtensionDependencyHelper
     */
    protected $dependencyHelper;

    /**
     * @var BundleSyncHelper
     */
    protected $bundleSyncHelper;

    /**
     * @var ExtensionHelper
     */
    protected $extensionHelper;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var ZikulaHttpKernelInterface
     */
    protected $kernel;

    public function __construct(
        ExtensionRepositoryInterface $extensionRepository,
        BlockRepositoryInterface $blockRepository,
        ExtensionDependencyHelper $dependencyHelper,
        BundleSyncHelper $bundleSyncHelper,
        ExtensionHelper $extensionHelper,
        EventDispatcherInterface $eventDispatcher,
        ZikulaHttpKernelInterface $kernel
    ) {
        $this->extensionRepository = $extensionRepository;
        $this->blockRepository = $blockRepository;
        $this->dependencyHelper = $dependencyHelper;
        $this->bundleSyncHelper = $bundleSyncHelper;
        $this->extensionHelper = $extensionHelper;
        $this->eventDispatcher = $eventDispatcher;
        $this->kernel = $kernel;
        parent::__construct();
    }

    protected function isInstalled(string $bundleName)
    {
        if (null !== $extension = $this->extensionRepository->findOneBy(['name' => $bundleName])) {
            if (in_array($extension->getState(), [Constant::STATE_ACTIVE, Constant::STATE_INACTIVE])) {
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
