<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Core
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Abstract controller for blocks.
 */
abstract class Zikula_Block extends Zikula_Base
{
    /**
     * Renderer instance.
     *
     * @var Renderer
     */
    protected $renderer;

    /**
     * Constructor.
     *
     * @param Zikula_ServiceManager $serviceManager ServiceManager instance.
     * @param Zikula_EventManager   $eventManager   EventManager instance.
     * @param array                 $options        Options (universal constructor).
     */
    public function  __construct(Zikula_ServiceManager $serviceManager, Zikula_EventManager $eventManager, array $options = array())
    {
        parent::__construct($serviceManager, $eventManager, $options);

        // Create renderer object
        $this->setRenderer();
        $this->renderer->assign('controller', $this);
    }

    /**
     * Set renderer property.
     *
     * @param Renderer $renderer Default null means new Render instance for this module name.
     *
     * @return Zikula_Controller
     */
    protected function setRenderer(Renderer $renderer = null)
    {
        if (is_null($renderer)) {
            $renderer = Renderer::getInstance($this->getName());
        }

        $this->renderer = $renderer;
        return $this;
    }

    /**
     * Initialise interface.
     *
     * @return void
     */
    abstract public function init();

    /**
     * Get info interface.
     *
     * @return array Blockinfo.
     */
    abstract public function info();

    /**
     * Display block.
     *
     * @param array $blockinfo Blockinfo.
     *
     * @return array Blockinfo.
     */
    abstract public function display($blockinfo);

    /**
     * Modify block interface.
     *
     * @param array $blockinfo Block info.
     *
     * @return string
     */
    public function modify($blockinfo)
    {
        return '';
    }

    /**
     * Update block interface.
     *
     * @param array $blockinfo Block info.
     *
     * @return array Blockinfo.
     */
    public function update($blockinfo)
    {
        return $blockinfo;
    }

    /**
     * Magic method to for method_not_found events.
     *
     * @param string $method Method invoked.
     * @param array  $args   Arguments.
     *
     * @throws BadMethodCallException If no event responds.
     *
     * @return string Data.
     */
    public function __call($method, $args)
    {
        $event = new Zikula_Event('block.method_not_found', $this, array('method' => $method, 'args' => $args));
        EventUtil::notifyUntil($event);
        if ($event->hasNotified()) {
            return $event->getData();
        }

        throw new BadMethodCallException(__f('%1$s::%2$s() does not exist.', array(get_class($this), $method)));
    }
}