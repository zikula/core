<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\EventListener\Theme;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Core\Theme\ParameterBag;

class DefaultPageVarSetterListener implements EventSubscriberInterface
{
    private $pageVars;

    public function __construct(ParameterBag $pageVars)
    {
        $this->pageVars = $pageVars;
    }

    /**
     * Add default pagevar settings to every page
     * @param GetResponseEvent $event
     */
    public function setDefaultPageVars(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        // set some defaults
        $this->pageVars->set('lang', \ZLanguage::getLanguageCode());
        $this->pageVars->set('langdirection', \ZLanguage::getDirection());
        $this->pageVars->set('title', \System::getVar('defaultpagetitle'));
        $this->pageVars->set('meta.charset', \ZLanguage::getDBCharset());
        $this->pageVars->set('meta.description', \System::getVar('defaultmetadescription'));
        $this->pageVars->set('meta.keywords', \System::getVar('metakeywords'));

        $schemeAndHost = $event->getRequest()->getSchemeAndHttpHost();
        $baseUrl = $event->getRequest()->getBaseUrl();
        $this->pageVars->set('homepath', $schemeAndHost . $baseUrl);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(
                array('setDefaultPageVars', 201),
            ),
        );
    }
}
