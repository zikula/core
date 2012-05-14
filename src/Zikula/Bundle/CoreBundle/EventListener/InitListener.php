<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\EventListener;

use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Component\DependencyInjection\ContainerBuilder;
use Zikula\Core\Event\GenericEvent;
use Zikula\Framework\AbstractEventHandler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use UsersModule\Constants as UsersConstant;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Zikula\Core\CoreEvents;

/**
 * System class.
 *
 * Core class with the base methods.
 */
class InitListener implements EventSubscriberInterface
{
    const STAGE_NONE = 0;
    const STAGE_PRE = 1;
    const STAGE_POST = 2;
    const STAGE_CONFIG = 4;
    const STAGE_DB = 8;
    const STAGE_TABLES = 16;
    const STAGE_SESSIONS = 32;
    const STAGE_LANGS = 64;
    const STAGE_MODS = 128;
    const STAGE_DECODEURLS = 1024;
    const STAGE_THEME = 2048;
    const STAGE_ALL = 4095;
    const STAGE_AJAX = 4096; // needs to be set explicitly, STAGE_ALL | STAGE_AJAX

    /**
     * Stage.
     *
     * @var integer
     */
    protected $stage = 0;

    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @var ContainerAwareEventDispatcher
     */
    protected $dispatcher;

    /**
     * Constructor.
     *
     * @param ContainerBuilder $container
     */
    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onInit', 500),
        );
    }

    /**
     * Get stage.
     *
     * @return integer
     */
    public function getStage()
    {
        return $this->stage;
    }

    /**
     * Initialise Zikula.
     *
     * Carries out a number of initialisation tasks to get Zikula up and
     * running.
     *
     * @param integer $stage Stage to load.
     *
     * @return boolean True initialisation successful false otherwise.
     */
    public function onInit(GetResponseEvent $event) //$stage = self::STAGE_ALL)
    {
        if ($event->getRequestType() === HttpKernelInterface::SUB_REQUEST) {
            return;
        }

        $this->dispatcher = $event->getDispatcher();

        $this->stage = $stage = self::STAGE_ALL;

        $coreInitEvent = new GenericEvent($this);
        $coreInitEvent['request'] = $event->getRequest();

        // store the load stages in a global so other API's can check whats loaded

        $this->dispatcher->dispatch(CoreEvents::PREINIT, new GenericEvent($this));

//        // Initialise and load configuration
//        if ($stage & self::STAGE_CONFIG) {
//            // error reporting
//            if (!\System::isInstalling()) {
//                // this is here because it depends on the config.php loading.
//                $event = new GenericEvent(null, array('stage' => $stage));
//                $this->dispatcher->dispatch(CoreEvents::ERRORREPORTING, $event);
//            }
//
//            // initialise custom event listeners from config.php settings
//            $coreInitEvent->setArgument('stage', self::STAGE_CONFIG);
//            $this->dispatcher->dispatch(CoreEvents::INIT, $coreInitEvent);
//        }

//        // Check that Zikula is installed before continuing
//        if (\System::getVar('installed') == 0 && !\System::isInstalling()) {
//            $response = new RedirectResponse(\System::getBaseUrl().'install.php?notinstalled');
//            $response->send();
//            \System::shutdown();
//        }

        if ($stage & self::STAGE_DB) {
            try {
                $dbEvent = new GenericEvent();
                $this->dispatcher->dispatch('doctrine.init_connection', $dbEvent);
                $dbEvent = new GenericEvent($this, array('stage' => self::STAGE_DB));
                $this->dispatcher->dispatch(CoreEvents::INIT, $dbEvent);
            } catch (\PDOException $e) {
                if (!\System::isInstalling()) {
                    header('HTTP/1.1 503 Service Unavailable');
                    require_once \System::getSystemErrorTemplate('dbconnectionerror.tpl');
                    \System::shutDown();
                } else {
                    return false;
                }
            }
        }

        if ($stage & self::STAGE_TABLES) {
            // Initialise dbtables
            \ModUtil::initCoreVars();

            if (!\System::isInstalling()) {
                \ModUtil::registerAutoloaders();
            }

            $coreInitEvent->setArgument('stage', self::STAGE_TABLES);
            $this->dispatcher->dispatch(CoreEvents::INIT, $coreInitEvent);
        }

        if ($stage & self::STAGE_SESSIONS) {
            \SessionUtil::requireSession();
            $coreInitEvent->setArgument('stage', self::STAGE_SESSIONS);
            $this->dispatcher->dispatch(CoreEvents::INIT, $coreInitEvent);
        }

        // Have to load in this order specifically since we cant setup the languages until we've decoded the URL if required (drak)
        // start block
        if ($stage & self::STAGE_LANGS) {
            $lang = \ZLanguage::getInstance();
        }

        if ($stage & self::STAGE_DECODEURLS) {
            \System::queryStringDecode();
            $coreInitEvent->setArgument('stage', self::STAGE_DECODEURLS);
            $this->dispatcher->dispatch(CoreEvents::INIT, $coreInitEvent);
        }

        if ($stage & self::STAGE_LANGS) {
            $lang->setup();
            $coreInitEvent->setArgument('stage', self::STAGE_LANGS);
            $this->dispatcher->dispatch(CoreEvents::INIT, $coreInitEvent);
        }
        // end block

        if ($stage & self::STAGE_MODS) {
            // Set compression on if desired
            if (\System::getVar('UseCompression') == 1) {
                //ob_start("ob_gzhandler");
            }

            \ModUtil::load('SecurityCenter');

            $coreInitEvent->setArgument('stage', self::STAGE_MODS);
            $this->dispatcher->dispatch(CoreEvents::INIT, $coreInitEvent);
        }

        if ($stage & self::STAGE_THEME) {
            // register default page vars
            \PageUtil::registerVar('title');
            \PageUtil::setVar('title', \System::getVar('defaultpagetitle'));
            \PageUtil::registerVar('keywords', true);
            \PageUtil::registerVar('stylesheet', true);
            \PageUtil::registerVar('javascript', true);
            \PageUtil::registerVar('jsgettext', true);
            \PageUtil::registerVar('body', true);
            \PageUtil::registerVar('header', true);
            \PageUtil::registerVar('footer', true);

            $theme = \Zikula_View_Theme::getInstance();

            // set some defaults
            // Metadata for SEO
            $this->container['zikula_view.metatags']['description'] = \System::getVar('defaultmetadescription');
            $this->container['zikula_view.metatags']['keywords'] = \System::getVar('metakeywords');

            $coreInitEvent->setArgument('stage', self::STAGE_THEME);
            $this->dispatcher->dispatch(CoreEvents::INIT, $coreInitEvent);
        }

        // check the users status, if not 1 then log him out
        if (\UserUtil::isLoggedIn()) {
            $userstatus = \UserUtil::getVar('activated');
            if ($userstatus != UsersConstant::ACTIVATED_ACTIVE) {
                \UserUtil::logout();
                // TODO - When getting logged out this way, the existing session is destroyed and
                //        then a new one is created on the reentry into index.php. The message
                //        set by the registerStatus call below gets lost.
                \LogUtil::registerStatus(__('You have been logged out.'));
                $response = new RedirectResponse(\ModUtil::url('Users', 'user', 'login'));
                $response->send();
                exit;
            }
        }

        if (($stage & self::STAGE_POST) && ($this->stage & ~self::STAGE_POST)) {
            $this->dispatcher->dispatch(CoreEvents::POSTINIT, new GenericEvent($this, array('stages' => $stage)));
        }

        $this->dispatcher->dispatch('frontcontroller.predispatch', new GenericEvent());
    }
}
