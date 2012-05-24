<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Event handler to override templates.
 */
class TemplateOverridesYaml extends Zikula_AbstractEventHandler
{
    /**
     * Associative array.
     *
     * Maps template path to overriden path.
     *
     * @var array
     */
    protected $overrideMap = array();

    /**
     * Setup handler definitions.
     *
     * @return void
     */
    protected function setupHandlerDefinitions()
    {
        // weight -5 ensures it's notified before any other handlers since this need to override anything else.
        $this->addHandlerDefinition('zikula_view.template_override', 'handler', -5);
    }

    /**
     * Setup of handlers.
     *
     * @return void
     */
    public function setup()
    {
        if (is_readable('config/template_overrides.yml')) {
            $this->overrideMap = Doctrine_Parser::load('config/template_overrides.yml', 'yml');
        }
    }

    /**
     * Listens for 'zikula_view.template_override' events.
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
