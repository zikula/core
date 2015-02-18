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

class Mailer_Version extends Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Mailer');
        $meta['description']    = $this->__('Mailer module, provides mail API and mail setting administration.');
        //! module name that appears in URL
        $meta['url']            = $this->__('mailer');
        $meta['version']        = '1.3.3';
        $meta['capabilities']   = array(HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true));
        $meta['securityschema'] = array('Mailer::' => '::');

        return $meta;
    }

    /**
     * Set up hook subscriber bundle
     */
    protected function setupHookBundles()
    {
        // This enables Scribite 5 connection to HTML e-mail test
        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber.mailer.ui_hooks.htmlmail', 'ui_hooks', $this->__('HTML mail hook'));
        $bundle->addEvent('form_edit', 'mailer.ui_hooks.htmlmail.form_edit');
        $this->registerHookSubscriberBundle($bundle);
    }
}
