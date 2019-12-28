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
use Symfony\Component\EventDispatcher\LegacyEventDispatcherProxy;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Event\ModuleStateEvent;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\Repository\ExtensionRepository;
use Zikula\ExtensionsModule\ExtensionEvents;

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
        $this->dispatcher = LegacyEventDispatcherProxy::decorate($dispatcher);
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
        $this->dispatcher->dispatch(new GenericEvent($extension, ['state' => $state]), ExtensionEvents::UPDATE_STATE);

        // Check valid state transition
        switch ($state) {
            case Constant::STATE_INACTIVE:
                $eventName = CoreEvents::MODULE_DISABLE;
                break;
            case Constant::STATE_ACTIVE:
                if (Constant::STATE_INACTIVE === $extension->getState()) {
                    // ACTIVE is used for freshly installed modules, so only register the transition if previously inactive.
                    $eventName = CoreEvents::MODULE_ENABLE;
                }
                break;
            case Constant::STATE_UPGRADED:
                if (Constant::STATE_UNINITIALISED === $extension->getState()) {
                    throw new RuntimeException($this->translator->__('Error! Invalid module state transition.'));
                }
                break;
        }

        // change state
        $extension->setState($state);
        $this->extensionRepository->persistAndFlush($extension);

        // clear the cache before calling events
        $this->cacheClearer->clear('symfony');

        if (isset($eventName)) {
            $moduleBundle = $this->kernel->getModule($extension->getName());
            $event = new ModuleStateEvent($moduleBundle, $extension->toArray());
            $this->dispatcher->dispatch($event, $eventName);
        }

        return true;
    }
}
