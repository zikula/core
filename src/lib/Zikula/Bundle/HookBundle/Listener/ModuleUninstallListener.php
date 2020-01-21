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

namespace Zikula\Bundle\HookBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Bundle\CoreBundle\CoreEvents;
use Zikula\Bundle\CoreBundle\Event\ModuleStateEvent;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\RepositoryInterface\HookBindingRepositoryInterface;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\RepositoryInterface\HookRuntimeRepositoryInterface;

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
            CoreEvents::MODULE_REMOVE => 'removeHooks'
        ];
    }

    public function removeHooks(ModuleStateEvent $event): void
    {
        $moduleName = $event->getModInfo()['name'];
        $this->hookBindingRepository->deleteAllByOwner($moduleName);
        $this->hookRuntimeRepository->deleteAllByOwner($moduleName);
    }
}
