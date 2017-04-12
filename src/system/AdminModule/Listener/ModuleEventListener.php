<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\AdminModule\Entity\AdminModuleEntity;
use Zikula\AdminModule\Entity\RepositoryInterface\AdminModuleRepositoryInterface;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\ModuleStateEvent;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;

class ModuleEventListener implements EventSubscriberInterface
{
    /**
     * @var AdminModuleRepositoryInterface
     */
    protected $adminModuleRepository;

    /**
     * @var ExtensionRepositoryInterface
     */
    protected $extensionRepository;

    /**
     * @var VariableApiInterface
     */
    protected $variableApi;

    /**
     * @var bool
     */
    private $installed;

    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::MODULE_INSTALL => ['moduleInstall'],
        ];
    }

    /**
     * UpdateCheckHelper constructor.
     *
     * @param AdminModuleRepositoryInterface $adminModuleRepository
     * @param ExtensionRepositoryInterface $extensionRepository
     * @param VariableApiInterface $variableApi VariableApi service instance
     * @param bool $installed
     */
    public function __construct(
        AdminModuleRepositoryInterface $adminModuleRepository,
        ExtensionRepositoryInterface $extensionRepository,
        VariableApiInterface $variableApi,
        $installed
    ) {
        $this->adminModuleRepository = $adminModuleRepository;
        $this->extensionRepository = $extensionRepository;
        $this->variableApi = $variableApi;
        $this->installed = $installed;
    }

    /**
     * Handle module install event.
     *
     * @param ModuleStateEvent $event
     *
     * @return void
     */
    public function moduleInstall(ModuleStateEvent $event)
    {
        if (!$this->installed) {
            return;
        }
        $module = $event->getModule();
        $category = $this->variableApi->get('ZikulaAdminModule', 'defaultcategory');
        $module = $this->extensionRepository->findOneBy(['name' => $module->getName()]);
        $sortOrder = $this->adminModuleRepository->countModulesByCategory($category);

        //move the module
        $adminModuleEntity = $this->adminModuleRepository->findOneBy(['mid' => $module->getId()]);
        if (!$adminModuleEntity) {
            $adminModuleEntity = new AdminModuleEntity();
        }
        $adminModuleEntity->setMid($module->getId());
        $adminModuleEntity->setCid($category);
        $adminModuleEntity->setSortorder($sortOrder);

        $this->adminModuleRepository->persistAndFlush($adminModuleEntity);
    }
}
