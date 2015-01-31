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

/**
 * A file uploaded through a form.
 *
 * @deprecated as of 1.4.0
 * @see \Symfony\Component\HttpFoundation\File\UploadedFile
 */
class Zikula_UploadedFile extends \Symfony\Component\HttpFoundation\File\UploadedFile
{
    /**
     * @deprecated as of 1.4.0
     * @param $property
     * @return int|null|string
     */
    public function __get($property)
    {
        LogUtil::log('Array access to file properties is deprecated. Please use SPL methods.', E_USER_DEPRECATED);

        switch ($property) {
            case 'name':
                $value = $this->getClientOriginalName();
                break;
            case 'type':
                $value = $this->getClientMimeType();
                break;
            case 'size':
                $value = $this->getClientSize();
                break;
            case 'tmp_name':
                $value = $this->getRealPath();
                break;
            case 'error':
                $value = $this->getError();
                break;
            default:
                $value = null;
        }

        return $value;
    }
}
