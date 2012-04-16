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

namespace Zikula\Framework;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Zikula\Common\I18n\TranslatableInterface;

/**
 * AbstractHelper class.
 */
abstract class AbstractHelper implements TranslatableInterface
{
    /**
     * ServiceManager.
     *
     * @var ServiceManager
     */
    protected $container;

    /**
     * EventManager.
     *
     * @var EventManager
     */
    protected $dispatcher;

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
     * @param object $object AbstractBase, ServiceManager, EventManager, AbstractEventHandler, AbstractHandler, or other.
     *
     * @return void
     */
    private function _setup($object)
    {
        $this->object = $object;

        if ($object instanceof AbstractBase || $object instanceof AbstractEventHandler || $object instanceof \Zikula\Core\Hook\AbstractHandler || $object instanceof AbstractPlugin) {
            $this->container = $object->getContainer();
            $this->dispatcher = $object->getDispatcher();
        } else if ($object instanceof ContainerBuilder) {
            $this->container = $object;
            $this->dispatcher = $object->get('event_dispatcher');
        } else if ($object instanceof EvenDispatcher) {
            $this->dispatcher = $object;
            $this->container = $object->getContainer();
        }

        if ($object instanceof AbstractBase) {
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
