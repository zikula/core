<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @copyright Zikula Foundation
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\BlocksModule;

use HookUtil;
use ModUtil;
use Zikula\Component\HookDispatcher\SubscriberBundle;

/**
 * Version information for the blocks module
 *
 */
class BlocksModuleVersion extends \Zikula_AbstractVersion
{
    /**
     * Generate an array of meta data about this module
     *
     * @return array meta data array
     */
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname'] = $this->__('Blocks');
        $meta['description'] = $this->__('Block administration module.');
        $meta['url'] = $this->__('blocks');
        $meta['version'] = '3.8.2';
        $meta['core_min'] = '1.3.6';
        $meta['capabilities'] = array(HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true));
        $meta['securityschema'] = array('ZikulaBlocksModule::' => 'Block key:Block title:Block ID',
                                        'ZikulaBlocksModule::position' => 'Position name::Position ID', 
                                        'Menutree:menutreeblock:' => 'Block ID:Link Name:Link ID',
                                        'ExtendedMenublock::' => 'Block ID:Link ID:',
                                        'fincludeblock::' => 'Block title::',
                                        'HTMLblock::' => 'Block title::',
                                        'Languageblock::' => 'Block title::',
                                        'Menublock::' => 'Block title:Link name:',
                                        'PendingContent::' => 'Block title::',
                                        'Textblock::' => 'Block title::',
                                        'xsltblock::' => 'Block title::',
                                        );
        
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
        $bundle = new SubscriberBundle($this->name, 'subscriber.blocks.ui_hooks.htmlblock.content', 'ui_hooks', $this->__('HTML Block content hook'));
        $bundle->addEvent('form_edit', 'blocks.ui_hooks.htmlblock.content.form_edit');
        $this->registerHookSubscriberBundle($bundle);
    }
}