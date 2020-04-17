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

namespace Zikula\Bundle\HookBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Bundle\HookBundle\RepositoryInterface\HookBindingRepositoryInterface;
use Zikula\Bundle\HookBundle\RepositoryInterface\HookRuntimeRepositoryInterface;
use Zikula\ExtensionsModule\Event\ExtensionPostRemoveEvent;

class ModuleUninstallListener implements EventSubscriberInterface
{
    /**
     * @var HookBindingRepositoryInterface
     */
    private $hookBindingRepository;

    /**
     * @var HookRuntimeRepositoryInterface
     */
    private $hookRuntimeRepository;

    public function __construct(
        HookBindingRepositoryInterface $hookBindingRepository,
        HookRuntimeRepositoryInterface $hookRuntimeRepository
    ) {
        $this->hookBindingRepository = $hookBindingRepository;
        $this->hookRuntimeRepository = $hookRuntimeRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            ExtensionPostRemoveEvent::class => 'removeHooks'
        ];
    }

    public function removeHooks(ExtensionPostRemoveEvent $event): void
    {
        $extensionName = $event->getExtensionEntity()->getName();
        $this->hookBindingRepository->deleteAllByOwner($extensionName);
        $this->hookRuntimeRepository->deleteAllByOwner($extensionName);
    }
}
