<?php
/**
 * Copyright 2015 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Exception
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Zikula_UploadedFile as UploadedFile;

/**
 * FileBag is a container for uploaded files.
 *
 * @deprecated as of 1.4.0
 * @see \Symfony\Component\HttpFoundation\FileBag
 */
class Zikula_Bag_FileBag extends \Symfony\Component\HttpFoundation\FileBag
{
    private static $fileKeys = array('error', 'name', 'size', 'tmp_name', 'type');

    /**
     * Converts uploaded files to UploadedFile instances.
     *
     * @deprecated as of 1.4.0
     *
     * @param array|UploadedFile $file A (multi-dimensional) array of uploaded file information
     *
     * @return array A (multi-dimensional) array of UploadedFile instances
     */
    protected function convertFileInformation($file)
    {
        if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
            return $file;
        }

        $file = $this->fixPhpFilesArray($file);
        if (is_array($file)) {
            $keys = array_keys($file);
            sort($keys);

            if ($keys == self::$fileKeys) {
                if (UPLOAD_ERR_NO_FILE == $file['error']) {
                    $file = null;
                } else {
                    $file = new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['size'], $file['error']);
                }
            } else {
                $file = array_map(array($this, 'convertFileInformation'), $file);
            }
        }

        return $file;
    }

}
