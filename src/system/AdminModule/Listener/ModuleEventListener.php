<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Listener;

use ModUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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
     * @param VariableApi $variableApi VariableApi service instance.
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

        if (!\System::isInstalling()) {
            $category = $this->variableApi->get('ZikulaAdminModule', 'defaultcategory');
            ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'addmodtocategory', ['module' => $modName, 'category' => $category]);
        }
    }
}
