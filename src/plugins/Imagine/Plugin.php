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

class SystemPlugin_Imagine_Plugin extends Zikula_AbstractPlugin implements Zikula_Plugin_AlwaysOnInterface, Zikula_Plugin_ConfigurableInterface
{
    /**
     * Defined presets
     *
     * @var array
     */
    private $presets;

    /**
     * Get plugin meta data.
     *
     * @return array Meta data.
     */
    protected function getMeta()
    {
        return array(
            'displayname' => $this->__('Imagine'),
            'description' => $this->__('Provides Imagine image manipulation library'),
            'version'     => '1.0.0'
        );
    }

    /**
     * Checks plugin version and performs install/upgrade routine when needed.
     */
    public function preInitialize()
    {
        $version = $this->getVar('version', false);
        if (!$version) {
            $this->install();
        } elseif ($version !==  $this->getMetaVersion()) {
            $this->upgrade($version);
        }
    }

    /**
     * Initialise.
     *
     * Runs at plugin init time.
     *
     * @return void
     */
    public function initialize()
    {
        $autoloader = new Zikula_KernelClassLoader();
        $autoloader->spl_autoload_register();
        $autoloader->register('Imagine', dirname(__FILE__) . '/lib/vendor', '\\');

        $definition = new Zikula_ServiceManager_Definition('SystemPlugin_Imagine_Manager', array(
            new Zikula_ServiceManager_Reference('zikula.servicemanager'),
            new Zikula_ServiceManager_Reference($this->getServiceId())
        ));
        $this->serviceManager->registerService('systemplugin.imagine.manager', $definition, false);

        $this->addHandlerDefinition('view.init', 'registerPlugins');
        $this->addHandlerDefinition('module_dispatch.preexecute', 'clearCacheObserver');

        if ($this->getVar('thumb_auto_cleanup')) {
            $this->addHandlerDefinition('module_dispatch.postloadgeneric', 'cleanupThumbnails');
        }

        $this->setupThumbDir();
    }

    /**
     * Registers Imagine smarty plugins dir.
     *
     * @param Zikula_Event $event
     */
    public function registerPlugins(Zikula_Event $event)
    {
        $event->getSubject()->addPluginDir("{$this->baseDir}/templates/plugins");
    }

    /**
     * Runs thumbnails cleanup before execution of Theme clear cache methods.
     *
     * @param Zikula_Event $event
     */
    public function clearCacheObserver(Zikula_Event $event)
    {
        if ($event['modname'] == 'Theme') {
            // clear thumb when render cache is cleared
            // what with theme cache?
            $themeClearMethods = array('clear_cache', 'render_clear_cache', 'clearallcompiledcaches');
            if ($event['modfunc'][0] instanceof Theme_Controller_Admin && in_array($event['modfunc'][1], $themeClearMethods)) {
                $this->getManager()->cleanupThumbs();
            }
        }
    }

    /**
     * Periodically runs thumbnails cleanup on Admin module load (pseudo-cron job).
     *
     * @param Zikula_Event $event
     */
    public function cleanupThumbnails(Zikula_Event $event)
    {
        if ($event['modinfo']['name'] == 'Admin') {
            // check thumb validity
            $lastCleanup = new DateTime($this->getVar('last_cleanup'));
            $nextCleanup = $lastCleanup->setTime(0,0,0)->add(new DateInterval('P1D'));
            $now = new DateTime('now');
            if ($now > $nextCleanup) {
                $this->setVar('last_cleanup', $now->setTime(0,0,0)->format('Y-m-d H:i:s'));
                $this->getManager()->cleanupThumbs();
            }
        }
    }

    /**
     * Return configuration controller instance.
     *
     * @return SystemPlugin_Imagine_Configuration
     */
    public function getConfigurationController()
    {
        return new SystemPlugin_Imagine_Configuration($this->getServiceManager(), $this);
    }

    /**
     * Performs install routine (setup default settings, create thumbnails storage dir).
     *
     * @return bool
     */
    public function install()
    {
        $defaults = $this->defaultSettings();
        if (!file_exists(CacheUtil::getLocalDir($defaults['thumb_dir']))) {
            $this->setupThumbDir($defaults['thumb_dir']);
        }
        $this->setVars($defaults);

        return true;
    }

    /**
     * Performs upgrade routine.
     *
     * @param string $oldVersion
     *
     * @return bool
     */
    public function upgrade($oldVersion)
    {
        return true;
    }

    /**
     * Returns plugin default settings.
     *
     * @return array
     */
    public function defaultSettings()
    {
        $settings = array(
            'version' => $this->getMetaVersion(),
            'thumb_dir' => $this->getServiceId(),
            'thumb_auto_cleanup' => false,
            'presets' => array(
                'default' => new SystemPlugin_Imagine_Preset('default', array(
                    'width' => 100,
                    'height' => 100
                ))
            )
        );

        return $settings;
    }

    /**
     * Gets thumbnails storage directory.
     *
     * @return string
     */
    public function getThumbDir()
    {
        return CacheUtil::getLocalDir($this->getVar('thumb_dir'));
    }

    /**
     * Setup or restore storage directory.
     *
     * @param string $dir Storage directory (inside Zikula "ztemp" dir)
     *
     * @return bool
     */
    public function setupThumbDir($dir = null)
    {
        if (is_null($dir)) {
            $dir = $this->getVar('thumb_dir');
        }
        if (!$result = file_exists(CacheUtil::getLocalDir($dir))) {
            $result = CacheUtil::createLocalDir($dir);
        }

        if ($result) {
            $dir = CacheUtil::getLocalDir($dir);
            $htaccess = "{$dir}/.htaccess";
            if (!file_exists($htaccess)) {
                $template = "{$this->getBaseDir()}/templates/default.htaccess";
                $result = copy($template, $htaccess);
            }
        }

        return $result;
    }

    /**
     * Gets Imagine image interface.
     *
     * Returns first valid Imagine engine.
     *
     * @return \Imagine\Image\ImagineInterface
     */
    public function getImagineEngine()
    {
        $imagine = null;
        $engines = array('Imagick', 'Gmagick', 'Gd');
        foreach ($engines as $engine) {
            try {
                $class = "\\Imagine\\{$engine}\\Imagine";
                $imagine = new $class;
            } catch (RuntimeException $e) {}
        }

        return $imagine;
    }

    /**
     * Gets Manager service
     *
     * @return SystemPlugin_Imagine_Manager
     */
    public function getManager()
    {
        return $this->getServiceManager()->getService('systemplugin.imagine.manager');
    }

    /**
     * Checks whenever preset exists.
     *
     * @param string    $name Preset name
     *
     * @return bool
     */
    public function hasPreset($name)
    {
        $presets = $this->getPresets();
        return is_string($name) ? isset($presets[$name]) : false;
    }

    /**
     * Gets preset.
     *
     * Tries to return Imagine preset by given name. If such does not exists - creates temporary preset.
     *
     * @param string|mixed  $name   Preset name (defined in Imagine)
     * @param array         $data   Preset data
     *
     * @return SystemPlugin_Imagine_Preset
     */
    public function getPreset($name, $data = null)
    {
        if ($this->hasPreset($name)) {
            $preset = $this->presets[$name];
        } elseif ($name == 'default') {
            // this is the case when something happen to default preset so we need to recreate it
            $defaults = $this->defaultSettings();
            $preset = $defaults['presets']['default'];
        } else {
            $preset = new SystemPlugin_Imagine_Preset($name, $data);
        }

        return $preset;
    }

    /**
     * Stores preset in Imagine vars.
     *
     * @param SystemPlugin_Imagine_Preset $preset   Preset to store.
     *
     * @return SystemPlugin_Imagine_Plugin
     */
    public function setPreset(SystemPlugin_Imagine_Preset $preset)
    {
        $presets = $this->getPresets(true);
        $presets[$preset->getName()] = $preset;
        $this->setVar('presets', $presets);
        $this->presets = $presets;

        return $this;
    }

    /**
     * Deletes preset from Imagine vars.
     *
     * @param string $name Preset name
     *
     * @return SystemPlugin_Imagine_Plugin
     */
    public function delPreset($name)
    {
        if ($this->hasPreset($name)) {
            unset($this->presets[$name]);
            $this->setVar('presets', $this->presets);
        }

        return $this;
    }

    public function getPresets($force = false)
    {
        if ($force || is_null($this->presets)) {
            $this->presets = $this->getVar('presets');
        }
        return $this->presets;
    }


    /**
     * Convenience Module SetVar.
     *
     * @param string $key   Key.
     * @param mixed  $value Value, default empty.
     *
     * @return object This.
     */
    public function setVar($key, $value='')
    {
        ModUtil::setVar($this->getServiceId(), $key, $value);

        return $this;
    }

    /**
     * Convenience Module SetVars.
     *
     * @param array $vars Array of key => value.
     *
     * @return object This.
     */
    public function setVars(array $vars)
    {
        ModUtil::setVars($this->getServiceId(), $vars);

        return $this;
    }

    /**
     * Convenience Module GetVar.
     *
     * @param string  $key     Key.
     * @param boolean $default Default, false if not found.
     *
     * @return mixed
     */
    public function getVar($key, $default=false)
    {
        return ModUtil::getVar($this->getServiceId(), $key, $default);
    }

    /**
     * Convenience Module GetVars for all keys in this module.
     *
     * @return mixed
     */
    public function getVars()
    {
        return ModUtil::getVar($this->getServiceId());
    }

    /**
     * Convenience Module DelVar.
     *
     * @param string $key Key.
     *
     * @return object This.
     */
    public function delVar($key)
    {
        ModUtil::delVar($this->getServiceId(), $key);

        return $this;
    }

    /**
     * Convenience Module DelVar for all keys for this module.
     *
     * @return object This.
     */
    public function delVars()
    {
        ModUtil::delVar($this->getServiceId());

        return $this;
    }
}
