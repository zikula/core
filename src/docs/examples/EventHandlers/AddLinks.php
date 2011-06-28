<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT http://www.opensource.org/licenses/mit-license.php
 * @package ZikulaExamples
 * 
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Add a link to Users administration links Event Handler class.
 */
class Users_EventHandlers_AddLinks extends Zikula_AbstractEventHandler
{
    /**
     * Setup handler definitions.
     *
     * @return void
     */
    protected function setupHandlerDefinitions()
    {
        $this->addHandlerDefinition('module_dispatch.postexecute', 'handler');
        $this->addHandlerDefinition('controller.method_not_found', 'anotherfunction');
    }

    /**
     * Event handler here.
     *
     * @param Zikula_Event $event Event handler.
     *
     * @return void
     */
    public function handler(Zikula_Event $event)
    {
        // check if this is for this handler
        if (!($event->getSubject() instanceof Users_Api_Admin && $event['modfunc'][1] == 'getlinks')) {
            return;
        }

        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
            $event->data[] = array('url' => ModUtil::url('Users', 'admin', 'somelink'), 'text' => __('Here is another link'));
        }
    }
}