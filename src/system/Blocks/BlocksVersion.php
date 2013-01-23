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

    protected function setupHookBundles()
    {
        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber.blocks.ui_hooks.content', 'ui_hooks', $this->__('HTML Block content hooks'));
        $bundle->addEvent('display_view', 'blocks.ui_hooks.content.display_view');
        $bundle->addEvent('form_edit', 'blocks.ui_hooks.content.form_edit');
        $bundle->addEvent('form_delete', 'blocks.ui_hooks.content.form_delete');
        $bundle->addEvent('validate_edit', 'blocks.ui_hooks.content.validate_edit');
        $bundle->addEvent('validate_delete', 'blocks.ui_hooks.content.validate_delete');
        $bundle->addEvent('process_edit', 'blocks.ui_hooks.content.process_edit');
        $bundle->addEvent('process_delete', 'blocks.ui_hooks.content.process_delete');
        $this->registerHookSubscriberBundle($bundle);

        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber.blocks.filter_hooks.content', 'filter_hooks', $this->__('HTML block filter hook'));
        $bundle->addEvent('filter', 'blocks.filter_hooks.content.filter');
        $this->registerHookSubscriberBundle($bundle);
    }

}