<?php
/**
 * Copyright 2010 Zikula Foundation
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
 * AbstractHelper class.
 */
abstract class Zikula_AbstractHelper implements Zikula_TranslatableInterface
{
    /**
     * ServiceManager.
     *
     * @var Zikula_ServiceManager
     */
    protected $serviceManager;

    /**
     * EventManager.
     *
     * @var Zikula_EventManager
     */
    protected $eventManager;

    /**
     * Who we're helping.
     *
     * @var object
     */
    protected $object;

    /**
     * Translation domain.
     *
     * @var string|null
     */
    protected $domain = null;

    /**
     * Constructor.
     *
     * Override this as required.  If no dependency-injection is required, simply
     * override or set the domain as required.
     *
     * <samp>
     *  // for an unknown object
     *  public function __construct($specialObject)
     *  {
     *      // do stuff
     *      $this->domain = $specialObject->getDomain(); // Only if required
     *  }
     *
     *  // for a known and already handled object
     *  public function __construct($object)
     *  {
     *      parent::__construct($object);
     *
     *      // do extra required stuff
     *  }
     *
     * @param object $object Object.
     */
    public function __construct($object)
    {
        $this->_setup($object);
    }

    /**
     * Setup of class.
     *
     * Generally helpers are instaciated with new Zikula_AbstractHelper($this), but it
     * will accept most Zikula classes, and override this method.
     *
     * @param object $object Zikula_AbstractBase, Zikula_ServiceManager, Zikula_EventManager, Zikula_AbstractEventHandler, Zikula_Hook_AbstractHandler, or other.
     *
     * @return void
     */
    private function _setup($object)
    {
        $this->object = $object;

        if ($object instanceof Zikula_AbstractBase || $object instanceof Zikula_AbstractEventHandler || $object instanceof Zikula_Hook_AbstractHandler || $object instanceof Zikula_AbstractPlugin) {
            $this->serviceManager = $object->getServiceManager();
            $this->eventManager = $object->getEventManager();
        } elseif ($object instanceof Zikula_ServiceManager) {
            $this->serviceManager = $object;
            $this->eventManager = $object->getService('zikula.eventmanager');
        } elseif ($object instanceof Zikula_EventManager) {
            $this->eventManager = $object;
            $this->serviceManager = $object->getServiceManager();
        }

        if ($object instanceof Zikula_AbstractBase) {
            $this->domain = $object->getDomain();
        }
    }

    /**
     * Translate.
     *
     * @param string $msgid String to be translated.
     *
     * @return string
     */
    public function __($msgid)
    {
        return __($msgid, $this->domain);
    }

    /**
     * Translate with sprintf().
     *
     * @param string       $msgid  String to be translated.
     * @param string|array $params Args for sprintf().
     *
     * @return string
     */
    public function __f($msgid, $params)
    {
        return __f($msgid, $params, $this->domain);
    }

    /**
     * Translate plural string.
     *
     * @param string $singular Singular instance.
     * @param string $plural   Plural instance.
     * @param string $count    Object count.
     *
     * @return string Translated string.
     */
    public function _n($singular, $plural, $count)
    {
        return _n($singular, $plural, $count, $this->domain);
    }

    /**
     * Translate plural string with sprintf().
     *
     * @param string       $sin    Singular instance.
     * @param string       $plu    Plural instance.
     * @param string       $n      Object count.
     * @param string|array $params Sprintf() arguments.
     *
     * @return string
     */
    public function _fn($sin, $plu, $n, $params)
    {
        return _fn($sin, $plu, $n, $params, $this->domain);
    }
}
