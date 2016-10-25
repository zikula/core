<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Imagine manager.
 * @deprecated
 */
class SystemPlugin_Imagine_Manager extends Zikula_Controller_AbstractPlugin
{
    /**
     * Parent plugin instance.
     *
     * @var SystemPlugin_Imagine_Plugin
     */
    protected $plugin;

    /**
     * Module name for thumbnail grouping
     *
     * @var string
     */
    protected $module;

    /**
     * Thumbnail base storage directory
     *
     * @var string
     */
    protected $thumbDir;

    /**
     * Imagine instance
     *
     * @var \Imagine\Image\ImagineInterface
     */
    protected $imagine;

    /**
     * Imagine Transformation instance
     *
     * @var \Imagine\Filter\Transformation
     */
    protected $transformation;

    /**
     * Options preset
     *
     * @var SystemPlugin_Imagine_Preset
     */
    protected $preset;

    /**
     * View wont be needed so override this.
     */
    protected function configureView()
    {
    }

    /**
     * Gets Imagine plugin
     *
     * @return SystemPlugin_Imagine_Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * Sets thumbDir
     *
     * @param string    $thumbDir   Thumbnail storage dir
     *
     * @return SystemPlugin_Imagine_Manager
     */
    public function setThumbDir($thumbDir = null)
    {
        if (is_null($thumbDir) || !file_exists($thumbDir)) {
            $this->thumbDir = $this->plugin->getThumbDir();
        } else {
            $this->thumbDir = $thumbDir;
        }

        return $this;
    }

    /**
     * Gets thumbDir
     *
     * @return string
     */
    public function getThumbDir()
    {
        if (is_null($this->thumbDir)) {
            $this->setThumbDir();
        }

        return $this->thumbDir;
    }

    /**
     * Sets module.
     *
     * @param string $module
     *
     * @return SystemPlugin_Imagine_Manager
     */
    public function setModule($module = null)
    {
        if (is_null($module) || !is_string($module)) {
            $this->module = $this->plugin->getModuleName();
        } else {
            $this->module = $module;
        }

        return $this;
    }

    /**
     * Gets module.
     *
     * @return string
     */
    public function getModule()
    {
        if (is_null($this->module)) {
            $this->setModule();
        }

        return $this->module;
    }

    /**
     * Sets Imagine image interface.
     *
     * @param Imagine\Image\ImagineInterface $imagine
     *
     * @return SystemPlugin_Imagine_Manager
     */
    public function setImagine(\Imagine\Image\ImagineInterface $imagine = null)
    {
        if ($imagine instanceof \Imagine\Image\ImagineInterface) {
            $this->imagine = $imagine;
        } else {
            $this->imagine = $this->plugin->getImagineEngine();
        }

        return $this;
    }

    /**
     * Gets Imagine image interface.
     *
     * @return Imagine\Image\ImagineInterface
     */
    public function getImagine()
    {
        if (is_null($this->imagine)) {
            $this->setImagine();
        }

        return $this->imagine;
    }

    /**
     * Sets transformation instance.
     *
     * @param Imagine\Filter\Transformation $transformation
     *
     * @return SystemPlugin_Imagine_Manager
     */
    public function setTransformation(\Imagine\Filter\Transformation $transformation = null)
    {
        if ($transformation instanceof \Imagine\Filter\Transformation) {
            $this->transformation = $transformation;
        } else {
            $this->transformation = new \Imagine\Filter\Transformation();
        }

        return $this;
    }

    /**
     * Gets transformation.
     *
     * @return Imagine\Filter\Transformation
     */
    public function getTransformation()
    {
        if (is_null($this->transformation)) {
            $this->setTransformation();
        }

        return $this->transformation;
    }

    /**
     * Sets preset.
     *
     * Preset can be set in several ways:
     * - preset name to get one of presets defined in Imagine
     * - preset name and preset data to create preset on the fly
     * - instance of SystemPlugin_Imagine_Preset with custom preset
     * - only with data as "anonymous" preset (it name will be set to width + x + height)
     *
     * @param SystemPlugin_Imagine_Preset|string|array  $preset Preset instance, preset name or preset data as array
     * @param array                                     $data   Preset data
     *
     * @return SystemPlugin_Imagine_Manager
     */
    public function setPreset($preset = null, $data = null)
    {
        if ($preset instanceof SystemPlugin_Imagine_Preset) {
            $this->preset = $preset;
        } else {
            if (is_null($preset)) {
                $preset = 'default';
            }
            $this->preset = $this->plugin->getPreset($preset, $data);
        }

        if (!is_null($this->preset['__module'])) {
            $this->setModule($this->preset['__module']);
        }
        if (!is_null($this->preset['__imagine']) && $this->preset['__imagine'] instanceof \Imagine\Image\ImagineInterface) {
            $this->setImagine($this->preset['__imagine']);
        }
        if (!is_null($this->preset['__transformation']) && $this->preset['__transformation'] instanceof \Imagine\Filter\Transformation) {
            $this->setTransformation($this->preset['__transformation']);
        }

        return $this;
    }

    /**
     * Gets preset.
     *
     * @return SystemPlugin_Imagine_Preset
     */
    public function getPreset()
    {
        if (is_null($this->preset)) {
            $this->setPreset();
        }

        return $this->preset;
    }

    /**
     * Generates thumbnail.
     *
     * Main plugin routine, which creates thumbnail for given image using manager options
     * (module, preset, transformation etc.)
     *
     * @param string    $imagePath  Path to source image
     * @param string    $objectId   Object identifier (for example ID)
     *
     * @return string Thumbnail path, source image path (if thumbnail generation failed, empty string if image is not readable)
     */
    public function getThumb($imagePath, $objectId = null)
    {
        if (!is_readable($imagePath)) {
            return $imagePath;
        }
        if (!is_string($objectId)) {
            $objectId = $this->getObjectId($imagePath);
        }

        $thumbDir = $this->getFullThumbDir($objectId, $imagePath);
        $preset = $this->getPreset();

        $image = new SystemPlugin_Imagine_Image($imagePath, $thumbDir, $preset->getName(), $preset['extension']);

        if (!$image->hasThumb()) {
            try {
                $this->removePresetThumbs($imagePath, $objectId)
                    ->createThumbnail($image, $preset);
            } catch (Exception $e) {
                //! %1$s is source image path, %2$s is error message
                LogUtil::log($this->__f('An error occurred during thumbnail creation for image [%1$s]. Error details: %2$s', [$imagePath, $e->getMessage()]), \Monolog\Logger::INFO);

                return $imagePath;
            }
        }
        // Replace backslashes on Windows.
        $rootPath = str_replace('\\', '/', realpath('.'));

        return str_replace($rootPath . '/', '', $image->getThumbPathname());
    }

    /**
     * Removes image thumbnails for given preset.
     *
     * @param string    $imagePath  Source image path
     * @param string    $objectId   Object identifier (for example ID)
     *
     * @return SystemPlugin_Imagine_Manager
     */
    public function removePresetThumbs($imagePath, $objectId = null)
    {
        if (!is_string($objectId)) {
            $objectId = $this->getObjectId($imagePath);
        }

        $thumbDir = $this->getFullThumbDir($objectId, basename($imagePath));
        $preset = $this->getPreset();

        $image = new SystemPlugin_Imagine_Image($imagePath, $thumbDir, $preset->getName(), $preset['extension']);
        $this->removeFiles($image->getThumbPath(), $image->getThumbNamePattern(), true);

        return $this;
    }

    /**
     * Removes all thumbnails for given image.
     *
     * @param string    $imagePath  Source image path
     * @param string    $objectId   Object identifier (for example ID)
     *
     * @return SystemPlugin_Imagine_Manager
     */
    public function removeImageThumbs($imagePath, $objectId = null)
    {
        if (!is_string($objectId)) {
            $objectId = $this->getObjectId($imagePath);
        }

        $thumbDir = $this->getFullThumbDir($objectId, basename($imagePath));
        $preset = $this->getPreset();

        $image = new SystemPlugin_Imagine_Image($imagePath, $thumbDir, $preset->getName(), $preset['extension']);
        $this->removeFiles($image->getThumbPath(), false, true);

        return $this;
    }

    /**
     * Removes all thumbnails for given object.
     *
     * @param string    $objectId   Object identifier (for example ID)
     *
     * @return SystemPlugin_Imagine_Manager
     */
    public function removeObjectThumbs($objectId)
    {
        $thumbDir = $this->getFullThumbDir($objectId);
        $this->removeFiles($thumbDir, false, true);

        return $this;
    }

    /**
     * Cleanups module thumbnails.
     *
     * By default only thumbnails, which source image does not exists, are removed.
     * When $force is set to true - all thumbnails will be deleted.
     *
     * @param bool $force Set to true to delete all thumbnails
     *
     * @return SystemPlugin_Imagine_Manager
     */
    public function cleanupModuleThumbs($force = false)
    {
        $thumbDir = $this->getFullThumbDir();
        $this->removeFiles($thumbDir, false, $force);

        return $this;
    }

    /**
     * Cleanups Imagine thumbnails.
     *
     * By default only thumbnails, which source image does not exists, are removed.
     * When $force is set to true - all thumbnails will be deleted.
     *
     * @param bool $force Set to true to delete all thumbnails
     *
     * @return SystemPlugin_Imagine_Manager
     */
    public function cleanupThumbs($force = false)
    {
        $thumbDir = $this->getThumbDir();
        $this->removeFiles($thumbDir, false, $force);
        $this->plugin->setupThumbDir();

        return $this;
    }

    /**
     * Creates thumbnail.
     *
     * Internal routine for creating thumbnail.
     *
     * @param SystemPlugin_Imagine_Image  $image    Base image for thumbnail
     * @param SystemPlugin_Imagine_Preset $preset   Preset with options
     *
     * @throws Exception Rethrows Imagine exception on thumbnail creation failure
     *
     * @return SystemPlugin_Imagine_Image
     */
    private function createThumbnail(SystemPlugin_Imagine_Image $image, SystemPlugin_Imagine_Preset $preset)
    {
        $options = [];
        if (isset($preset['options']) || !is_array($preset['options'])) {
            $options = $preset['options'];
        }

        if (isset($preset['mode']) && $preset['mode'] === 'inset') {
            $mode = \Imagine\Image\ImageInterface::THUMBNAIL_INSET;
        } else {
            $mode = \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
        }

        // check for w/h autoscaling and scale to ratio
        if ($preset['height'] == 'auto') {
            $imageSize = @getimagesize($image->getRealPath());
            $preset['height'] = round($imageSize[1] / $imageSize[0] * $preset['width']);
        }
        if ($preset['width'] == 'auto') {
            $imageSize = @getimagesize($image->getRealPath());
            $preset['width'] = round($imageSize[0] / $imageSize[1] * $preset['height']);
        }

        $size = new \Imagine\Image\Box($preset['width'], $preset['height']);

        // Clone the transformation here, because we don't want the thumbnail transformations to bubble up.
        $transformation = clone $this->getTransformation();
        $transformation->add(new \Imagine\Filter\Basic\Thumbnail($size, $mode), 50);

        try {
            $transformation
                ->apply($this->getImagine()->open($image->getRealPath()))
                ->save($image->getThumbRealPath(), $options);
        } catch (Exception $exception) {
            throw $exception;
        }

        return $image;
    }

    /**
     * Generates full thumbnail storage path based on previously passed options (module, object id, preset etc).
     *
     * @param string    $objectId   Object identifier
     * @param string    $imagePath  Source image path
     *
     * @return string
     */
    private function getFullThumbDir($objectId = null, $imagePath = null)
    {
        $parts = [
            $this->getThumbDir(),
            $this->getModule()
        ];
        if (!is_null($objectId)) {
            $parts[] = $objectId;
        }
        if (!is_null($imagePath)) {
            $parts[] = base64_encode($imagePath);
        }

        return implode('/', $parts);
    }

    /**
     * Generates object identifier based on image path.
     *
     * @param string $imagePath Source image path
     *
     * @return string
     */
    private function getObjectId($imagePath)
    {
        return substr(hash('sha1', $imagePath), 0, 8);
    }

    /**
     * Internal routine for deleting thumbnails.
     *
     * @param string    $source     Directory to cleanup/clear
     * @param bool      $pattern    Filename patter
     * @param bool      $force      Set to true to delete all thumbnails
     *
     * @return SystemPlugin_Imagine_Manager
     */
    private function removeFiles($source, $pattern = false, $force = false)
    {
        if (file_exists($source)) {
            $files = Symfony\Component\Finder\Finder::create()
                ->files();
            if ($pattern) {
                $files = $files->name($pattern);
            }
            $files = $files->in($source);
            foreach ($files as $file) {
                $unlink = true;
                if (!$force) {
                    $sourceImage = base64_decode(basename($file->getPath()), true);
                    $unlink = !file_exists($sourceImage);
                }
                if ($unlink) {
                    unlink($file);
                }
            }
            // try to remove empty dirs
            $directories = Symfony\Component\Finder\Finder::create()
                ->directories()
                ->in($source);
            $dirs = [];
            foreach ($directories as $dir) {
                $dirs[] = $dir;
            }
            foreach (array_reverse($dirs) as $dir) {
                @rmdir($dir);
            }
            // try to remove also source dir but not if it's base thumb dir
            if ($source != $this->getThumbDir()) {
                @rmdir($source);
            }
        }

        return $this;
    }
}
