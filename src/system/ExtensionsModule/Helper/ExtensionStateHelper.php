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

namespace Zikula\ExtensionsModule\Helper;

use RuntimeException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\Repository\ExtensionRepository;
use Zikula\ExtensionsModule\Event\ExtensionPostDisabledEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostEnabledEvent;
use Zikula\ExtensionsModule\Event\ExtensionPreStateChangeEvent;

class ExtensionStateHelper
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var CacheClearer
     */
    private $cacheClearer;

    /**
     * @var ExtensionRepository
     */
    private $extensionRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        CacheClearer $cacheClearer,
        ExtensionRepository $extensionRepository,
        TranslatorInterface $translator,
        ZikulaHttpKernelInterface $kernel
    ) {
        $this->dispatcher = $dispatcher;
        $this->cacheClearer = $cacheClearer;
        $this->extensionRepository = $extensionRepository;
        $this->translator = $translator;
        $this->kernel = $kernel;
    }

    /**
     * Set the state of a module.
     */
    public function updateState(int $id, int $state): bool
    {
        /** @var ExtensionEntity $extension */
        $extension = $this->extensionRepository->find($id);
        $this->dispatcher->dispatch(new ExtensionPreStateChangeEvent($extension, $state));

        // Check valid state transition
        switch ($state) {
            case Constant::STATE_INACTIVE:
                $eventClass = ExtensionPostDisabledEvent::class;
                break;
            case Constant::STATE_ACTIVE:
                if (Constant::STATE_INACTIVE === $extension->getState()) {
                    // ACTIVE is used for freshly installed modules, so only register the transition if previously inactive.
                    $eventClass = ExtensionPostEnabledEvent::class;
                }
                break;
            case Constant::STATE_UPGRADED:
                if (Constant::STATE_UNINITIALISED === $extension->getState()) {
                    throw new RuntimeException($this->translator->trans('Error! Invalid module state transition.'));
                }
                break;
        }

        // change state
        $extension->setState($state);
        $this->extensionRepository->persistAndFlush($extension);

        // clear the cache before calling events
        $this->cacheClearer->clear('symfony');

        if (isset($eventClass) && $this->kernel->isBundle($extension->getName())) {
            $bundle = $this->kernel->getBundle($extension->getName());
            $this->dispatcher->dispatch(new $eventClass($bundle, $extension));
        }

        return true;
    }
}
