<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * AjaxUtil
 * @author Frank Schummertz
 */
class AjaxUtil
{
    /**
     * error()
     *
     * Immediately stops execution and returns an error message
     *
     * @param error - error text
     * @param code - optional error code, default '400 Bad data'
     * @author Frank Schummertz
     *
     */
    public static function error($error='', $code='400 Bad data')
    {
        if (!empty($error)) {
            header('HTTP/1.0 ' . $code);
            echo DataUtil::convertToUTF8($error);
            System::shutdown();
        }
    }

    /**
     * encode data in JSON and return
     * This functions can add a new authid if requested to do so (default).
     * If the supplied args is not an array, it will be converted to an
     * array with 'data' as key.
     * Authid field will always be named 'authid'. Any other field 'authid'
     * will be overwritten!
     * Script execution stops here
     *
     * @param args - string or array of data
     * @param createauthid - create a new authid and send it back to the calling javascript
     * @param xjsonheader - send result in X-JSON: header for prototype.js
     * @param statusmsg - include statusmsg in output
     * @author Frank Schummertz
     *
     */
    public static function output($args, $createauthid = false, $xjsonheader = false, $statusmsg = true)
    {
        // check if an error message is set
        $msgs = LogUtil::getErrorMessagesText('<br />');
        if ($msgs != false && !empty($msgs)) {
            self::error($msgs);
        }

        if (!is_array($args)) {
            $data = array('data' => $args);
        } else {
            $data = $args;
        }

        if ($statusmsg === true) {
            // now check if a status message is set
            $msgs = LogUtil::getStatusMessagesText('<br />');
            $data['statusmsg'] = $msgs;
        }

        if ($createauthid === true) {
            $data['authid'] = SecurityUtil::generateAuthKey(ModUtil::getName());
        }

        // set locale to en_US to ensure correct decimal delimiters
        if (stristr(getenv('OS'), 'windows')) {
            setlocale(LC_ALL, 'eng');
        } else {
            setlocale(LC_ALL, 'en_US');
        }

        // convert the data to UTF-8 if not already encoded as such
        // Note: this isn't strict test but relying on the site language pack encoding seems to be a good compromise
        if (ZLanguage::getEncoding() != 'utf-8') {
            $data = DataUtil::convertToUTF8($data);
        }

        $output = json_encode($data);

        header('HTTP/1.0 200 OK');
        header('Content-type: application/json');
        if ($xjsonheader == true) {
            header('X-JSON:(' . $output . ')');
        }
        echo $output;
        System::shutdown();
    }

}
