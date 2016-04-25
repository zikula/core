<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
            'mode' => array('inset', 'outbound'),
            'extension' => array('jpg', 'png', 'gif'),
        );

        $this->getView()
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

        $thumb_auto_cleanup = (bool)$this->request->request->get('thumb_auto_cleanup');
        $this->plugin->setVar('thumb_auto_cleanup', $thumb_auto_cleanup);

        $thumb_auto_cleanup_period = $this->request->request->get('thumb_auto_cleanup_period');
        $this->plugin->setVar('thumb_auto_cleanup_period', $thumb_auto_cleanup_period);

        $presets = $this->request->getPost()->get('presets', array());

        $presetsToSave = array();
        foreach ($presets as $preset) {
            // validate jpeg qual and png_compression
            if (!is_numeric($preset['options']['jpeg_quality']) || $preset['options']['jpeg_quality'] < 0 || $preset['options']['jpeg_quality'] > 100) {
                $preset['options']['jpeg_quality'] = 75; // default 75%
            }
            if (!is_numeric($preset['options']['png_compression_level']) || $preset['options']['png_compression_level'] < 0 || $preset['options']['png_compression_level'] > 9) {
                $preset['options']['png_compression_level'] = 7; // default 7
            }
            $name = $preset['name'];
            if (!empty($name)) {
                $presetsToSave[$name] = new SystemPlugin_Imagine_Preset($name, $preset);
            }
        }

        $this->plugin->setVar('presets', $presetsToSave);

        $this->registerStatus($this->__('Done! Saved plugin configuration.'));

        $this->redirect(ModUtil::url('ZikulaExtensionsModule', 'adminplugin', 'dispatch', array(
            '_plugin' => 'Imagine',
            '_action' => 'configure'
        )));
    }

    /**
     * Calls cleanup routine
     */
    public function cleanup()
    {
        // check to see all thumbnails should be removed (force=true), or only when the source image is removed
        $force = $this->request->query->filter('force', false, FILTER_VALIDATE_BOOLEAN);
        $manager = $this->getContainer()->get('systemplugin.imagine.manager');
        $manager->cleanupThumbs($force);
        if ($force) {
            $this->registerStatus($this->__('Done! All Imagine thumbnails are removed and will be re-generated when requested again!'));
        } else {
            $this->registerStatus($this->__('Done! Imagine thumbnails are cleaned up of source images that were removed!'));
        }

        $this->redirect(ModUtil::url('ZikulaExtensionsModule', 'adminplugin', 'dispatch', array(
            '_plugin' => 'Imagine',
            '_action' => 'configure'
        )));
    }
}
