<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Listener;

use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\RepositoryInterface\HookBindingRepositoryInterface;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\RepositoryInterface\HookRuntimeRepositoryInterface;
use Zikula\Core\CoreEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Core\Event\ModuleStateEvent;

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

    /**
     * ModuleUninstallListener constructor.
     * @param HookBindingRepositoryInterface $hookBindingRepository
     * @param HookRuntimeRepositoryInterface $hookRuntimeRepository
     */
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
            CoreEvents::MODULE_REMOVE => 'removeHooks',
        ];
    }

    public function removeHooks(ModuleStateEvent $event)
    {
        $moduleName = $event->getModInfo()['name'];
        $this->hookBindingRepository->deleteAllByOwner($moduleName);
        $this->hookRuntimeRepository->deleteAllByOwner($moduleName);
    }
}
