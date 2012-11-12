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

class SystemPlugin_Imagine_Image extends SplFileInfo
{
    /**
     * Thumbnail type identifier (preset name)
     *
     * @var string
     */
    protected $thumbKey;

    /**
     * Thumbnail extension.
     *
     * @var string
     */
    protected $thumbExtension;

    /**
     * Thumbnail storage directory.
     *
     * @var SplFileInfo
     */
    protected $thumbDir;

    /**
     * Thumbnail for image.
     *
     * @var SplFileInfo
     */
    protected $thumb;

    /**
     * Constructor.
     *
     * @param string    $fileName           Source image
     * @param string    $thumbDir           Thumbnail storage directory
     * @param string    $thumbKey           Thumbnail type identifier (preset name)
     * @param string    $thumbExtension     Thumbnail extension (default null means source image extension)
     */
    public function __construct($fileName, $thumbDir, $thumbKey = null, $thumbExtension = null)
    {
        parent::__construct($fileName);
        $this->setThumb($thumbDir, $thumbKey, $thumbExtension);
    }

    /**
     * Setup thumb params and constructs thumb object.
     *
     * @param string    $thumbDir           Thumbnail storage directory
     * @param string    $thumbKey           Thumbnail type identifier (preset name)
     * @param string    $thumbExtension     Thumbnail extension (default null means soruce image extension)
     *
     * @return SystemPlugin_Imagine_Image
     */
    public function setThumb($thumbDir, $thumbKey = 'default', $thumbExtension = null)
    {
        $this->thumbKey = $thumbKey;
        $this->thumbExtension = $thumbExtension;
        $this->thumbDir = new SplFileInfo($thumbDir);
        $this->thumb = new SplFileInfo("{$this->thumbDir}/{$this->getThumbFileName()}");

        return $this;
    }

    /**
     * Checks whether thumbnail already exists.
     *
     * @return bool
     */
    public function hasThumb()
    {
        return $this->thumb && $this->thumb->isWritable();
    }

    /**
     * Getter for thumbnail.
     *
     * @return SplFileInfo
     */
    public function getThumb()
    {
        return $this->thumb;
    }

    /**
     * Gets thumbnail base name.
     *
     * @return string
     */
    public function getThumbName()
    {
        return $this->getThumb()->getBasename();
    }

    /**
     * Gets thumbnail name pattern (regexp for name lookup with wildcard instead of timestamp)
     *
     * @return string
     */
    public function getThumbNamePattern()
    {
        return $this->getThumbFileName(true);
    }

    /**
     * Gets thumbnail parent directory path.
     *
     * @return string
     */
    public function getThumbPath()
    {
        return $this->getThumb()->getPath();
    }

    /**
     * Gets thumbnail path.
     *
     * @return string
     */
    public function getThumbPathname()
    {
        return $this->getThumb()->getPathname();
    }

    /**
     * Gets thumbnail real path, if thumbnail parent directory does not exists - it's created.
     *
     * @return string
     */
    public function getThumbRealPath()
    {
        $dir = $this->getThumb()->getPathInfo()->getRealPath();
        if (!$dir) {
            $sm = ServiceUtil::getManager();
            mkdir($this->getThumb()->getPathInfo(), $sm['system.chmod_dir'], true);
            $dir = $this->getThumb()->getPathInfo()->getRealPath();
        }

        return "{$dir}/{$this->getThumb()->getBasename()}";
    }

    /**
     * Generates hash part of thumbnail file name
     *
     * @param bool $pattern True to get hash as pattern.
     *
     * @return string Generated hash for thumbnail name.
     */
    private function getThumbHash($pattern = false)
    {
        $time = $pattern ? '*' : $this->getMTime();
        $dot = $pattern ? '\.' : '.';

        return "{$this->thumbKey}{$dot}{$time}";
    }

    /**
     * Generates thumbnail file name
     *
     * @param bool $pattern True to get regexp pattern for name lookup (with wildcard instead of timestamp)
     *
     * @return string
     */
    private function getThumbFileName($pattern = false)
    {
        $dot = $pattern ? '\.' : '.';
        $ext = $this->thumbExtension ? $this->thumbExtension : $this->getExtension();

        return "{$this->getthumbHash($pattern)}{$dot}{$ext}";
    }
}