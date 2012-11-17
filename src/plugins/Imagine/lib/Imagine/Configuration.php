<?php
/**
 * Copyright Zikula Foundation 2012 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version.
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class SystemPlugin_Imagine_Configuration extends Zikula_Controller_AbstractPlugin
{
    /**
     * Parent plugin instance.
     *
     * @var SystemPlugin_Imagine_Plugin
     */
    protected $plugin;

    protected function postInitialize()
    {
        // In this controller we don't want caching to be enabled.
        $this->view->setCaching(false);
    }

    /**
     * Fetch and render the configuration template.
     *
     * @return string The rendered template.
     */
    public function configure()
    {
        $modVars = $this->plugin->getVars();
        $options = array(
            'mode' => array('inset', 'outset'),
            'extension' => array('jpg', 'png', 'gif'),
        );

        $this->getView()
            ->assign('header', ModUtil::func('Admin', 'admin', 'adminheader'))
            ->assign('footer', ModUtil::func('Admin', 'admin', 'adminfooter'))
            ->assign('vars', $modVars)
            ->assign('thumb_full_dir', CacheUtil::getLocalDir($modVars['thumb_dir']))
            ->assign('options', $options);

        return $this->getView()->fetch('configuration.tpl');
    }

    /**
     * Update plugin configuration
     */
    public function updateConfig()
    {
        $this->checkCsrfToken();
        $oldVars = $this->plugin->getVars();

        $thumb_dir = $this->request->getPost()->get('thumb_dir');
        if (!empty($thumb_dir) && $thumb_dir !== $oldVars['thumb_dir']) {
            $result = $this->plugin->setupThumbDir($thumb_dir);
            if ($result) {
                $this->plugin->getManager()->cleanupThumbs(true);
                CacheUtil::removeLocalDir($oldVars['thumb_dir']);
                $this->plugin->setVar('thumb_dir', $thumb_dir);
            } else {
                LogUtil::registerError($this->__('Error! Could not change thumbnails storage directory.'));
            }
        }

        $thumb_auto_cleanup = (bool)$this->request->getPost()->get('thumb_auto_cleanup');
        $this->plugin->setVar('thumb_auto_cleanup', $thumb_auto_cleanup);

        $presets = $this->request->getPost()->get('presets', array());

        $presetsToSave = array();
        foreach ($presets as $preset) {
            $name = $preset['name'];
            if (!empty($name)) {
                $presetsToSave[$name] = new SystemPlugin_Imagine_Preset($name, $preset);
            }
        }

        $this->plugin->setVar('presets', $presetsToSave);

        LogUtil::registerStatus($this->__('Done! Saved plugin configuration.'));

        $this->redirect(ModUtil::url('Extensions', 'adminplugin', 'dispatch', array(
            '_plugin' => 'Imagine',
            '_action' => 'configure'
        )));
    }

    /**
     * Calls cleanup routine
     */
    public function cleanup()
    {
        $manager = $this->getServiceManager()->getService('systemplugin.imagine.manager');
        $manager->cleanupThumbs();
        $this->registerStatus($this->__('Done! Imagine thumbnails were cleanup!'));

        $this->redirect(ModUtil::url('Extensions', 'adminplugin', 'dispatch', array(
            '_plugin' => 'Imagine',
            '_action' => 'configure'
        )));
    }
}
