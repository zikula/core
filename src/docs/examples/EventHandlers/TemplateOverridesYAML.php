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

use Zikula\Core\Event\GenericEvent;
use Symfony\Component\Yaml\Yaml;

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
    protected $overrideMap = array();

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
     * Setup of handlers.
     *
     * @return void
     */
    public function setup()
    {
        $this->overrideMap = Yaml::parse('config/template_overrides.yml');
    }

    /**
     * Event handler here.
     *
     * @param GenericEvent $event Event handler.
     *
     * @return void
     */
    public function handler(GenericEvent $event)
    {
        if (array_key_exists($event->data, $this->overrideMap)) {
            $event->data = $this->overrideMap[$event->data];
            $event->stopPropagation();
        }
    }
}