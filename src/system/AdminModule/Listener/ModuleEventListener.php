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

use ModUtil;
use ServiceUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\AdminModule\Entity\AdminModuleEntity;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\ModuleStateEvent;
use Zikula\ExtensionsModule\Api\VariableApi;

class ModuleEventListener implements EventSubscriberInterface
{
    /**
     * @var VariableApi
     */
    protected $variableApi;

    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::MODULE_INSTALL => ['moduleInstall'],
        ];
    }

    /**
     * UpdateCheckHelper constructor.
     *
     * @param VariableApi $variableApi VariableApi service instance
     */
    public function __construct(VariableApi $variableApi)
    {
        $this->variableApi = $variableApi;
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
        $module = $event->getModule();
        if ($module) {
            $modName = $module->getName();
        } else {
            // Legacy for non Symfony-styled modules.
            $modInfo = $event->modinfo;
            $modName = $modInfo['name'];
        }

        if (\System::isInstalling()) {
            return;
        }

        $category = $this->variableApi->get('ZikulaAdminModule', 'defaultcategory');
        $moduleId = (int)ModUtil::getIdFromName($modName);

        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $adminModuleRepository = $entityManager->getRepository('ZikulaAdminModule:AdminModuleEntity');
        $sortOrder = $adminModuleRepository->countModulesByCategory($category);

        //move the module
        $item = $adminModuleRepository->findOneBy(['mid' => $moduleId]);
        if (!$item) {
            $item = new AdminModuleEntity();
        }
        $item->setMid($moduleId);
        $item->setCid($category);
        $item->setSortorder($sortOrder);

        $entityManager->persist($item);
        $entityManager->flush();
    }
}
