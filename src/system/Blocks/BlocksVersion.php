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

namespace Blocks;

use HookUtil;
use ModUtil;
use Zikula\Component\HookDispatcher\SubscriberBundle;

class BlocksVersion extends \Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname'] = $this->__('Blocks');
        $meta['description'] = $this->__('Block administration module.');
        $meta['url'] = $this->__('blocks');
        $meta['version'] = '3.8.2';
        $meta['capabilities'] = array(HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true));
        $meta['securityschema'] = array('Blocks::' => 'Block key:Block title:Block ID', 'Blocks::position' => 'Position name::Position ID', 'Menutree:menutreeblock:' => 'Block ID:Link Name:Link ID', 'ExtendedMenublock::' => 'Block ID:Link ID:');
        // Module depedencies
        $meta['dependencies'] = array(
                array('modname'    => 'Scribite',
                      'minversion' => '5.0.0',
                      'maxversion' => '',
                      'status'     => ModUtil::DEPENDENCY_RECOMMENDED),
        );
        return $meta;
    }

    /**
     * Set up hook subscriber bundle
     * 
     * This area is only activated when editing an Html Block. 
     * There are no other hook functions currently implemented since linking
     * back (via url) to a block is impossible. 
     */
    protected function setupHookBundles()
    {
        $bundle = new SubscriberBundle($this->name, 'subscriber.blocks.htmlblock.ui_hooks.content', 'ui_hooks', $this->__('HTML Block content hook'));
        $bundle->addEvent('form_edit', 'blocks.htmlblock.ui_hooks.content.form_edit');
        $this->registerHookSubscriberBundle($bundle);
    }

}