<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 * @package ZikulaExamples
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Example event handler to override templates.
 */
class TemplateOverrides extends Zikula_AbstractEventHandler
{
    /**
     * Associative array.
     * 
     * Maps template path to overriden path.
     *
     * @var array
     */
    protected $overrideMap = array('modules/Example/templates/foo.tpl' => 'config/modules/Example/foo.tpl');

    /**
     * Setup handler definitions.
     *
     * @return void
     */
    protected function setupHandlerDefinitions()
    {
        $this->addHandlerDefinition('zikula_view.template_override', 'handler');
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
        if (array_key_exists($event->data, $this->overrideMap)) {
            $event->data = $this->overrideMap[$event->data];
            $event->stop();
        }
    }
}