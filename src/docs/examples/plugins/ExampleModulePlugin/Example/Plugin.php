<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Plugin definition class.
 */
class ModulePlugin_Users_Example_Plugin extends Zikula_AbstractPlugin implements Zikula_Plugin_ConfigurableInterface
{
    /**
     * Setup handler definitions.
     *
     * @return void
     */
    protected function setupHandlerDefinitions()
    {
        $this->addHandlerDefinition('module_dispatch.postexecute', 'addLinks');
        $this->addHandlerDefinition('controller.method_not_found', 'anotherfunction');
    }

    /**
     * Provide plugin meta data.
     *
     * @return array Meta data.
     */
    protected function getMeta()
    {
        return array('displayname' => $this->__('Example Users Plugin'),
                     'description' => $this->__('Adds link to administration menu.'),
                     'version'     => '1.0.0'
                    );
    }

    /**
     * Event handler here.
     *
     * @param Zikula_Event $event Handler.
     *
     * @return void
     */
    public function addLinks(Zikula_Event $event)
    {
        // check if this is for this handler
        if (!($event->getSubject() instanceof Users_Api_Admin && $event['modfunc'][1] == 'getlinks')) {
            return;
        }

        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
            $event->data[] = array('url' => ModUtil::url('Users', 'admin', 'anotherfunction'), 'text' => $this->__('Here is another link'));
        }
    }

    /**
     * Add 'anotherfunction' Event handler .
     *
     * @param Zikula_Event $event Handler.
     *
     * @return void
     */
    public function anotherfunction(Zikula_Event $event)
    {
        // check if this is for this handler
        $subject = $event->getSubject();
        if (!($event['method'] == 'anotherfunction' && $subject instanceof Users_Controller_Admin)) {
            return;
        }

        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $view = Zikula_View_plugin::getModulePluginInstance($this->moduleName, $this->pluginName);

        $event->setData($view->fetch('anotherfunction.tpl'));
        $event->stop();
    }

    /**
     * Controller configuration getter.
     *
     * @return ModulePlugin_Users_Example_Controller
     */
    public function getConfigurationController()
    {
        return new ModulePlugin_Users_Example_Controller($this->serviceManager, $this);
    }
}
