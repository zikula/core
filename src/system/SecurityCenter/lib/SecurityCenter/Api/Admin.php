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


class SecurityCenter_Api_Admin extends Zikula_Api
{
    /**
     * This function does the actual export of the csv file.
     * the args array contains all information needed to export the csv file.
     * the options for the args array are:
     *  - exportTitles  (boolean) Flag true to export a title row.
     *  - exportFile    (string)  Filename for the new csv file.
     *  - delimiter     (string)  The delimiter to use in the csv file.
     *  - titles        (array)   array of titles for the csv.
     *  - data          (array)   array of data for the csv.
     *
     * @param array $args all arguments sent to this function.
     *
     * @return displays download to user then exits.
     */
    public function exportCSV($args)
    {
        // make sure we have a delimiter
        if (!isset($args['delimiter']) || $args['delimiter'] == '') {
            $args['delimiter'] = ',';
        }

        //Security check
        if (!SecurityUtil::checkPermission('SecurityCenter::', '::', ACCESS_EDIT)){
            return LogUtil::registerPermissionError();
        }

        //disable compression and set headers
        ob_end_clean();
        ini_set('zlib.output_compression', 0);
        header('Cache-Control: no-store, no-cache');
        header("Content-type: text/csv");
        header('Content-Disposition: attachment; filename="'.$args['exportFile'].'"');
        header("Content-Transfer-Encoding: binary");

        // open a file for csv writing
        $out = fopen("php://output", 'w');

        // write out title row if asked for
        if ($args['exportTitles']) {
            fputcsv($out, $args['titles'], $args['delimiter']);
        }

        // write out data
        foreach($args['data'] as $datarow) {
            fputcsv($out, $datarow, $args['delimiter']);
        }
        
        //close the out file
        $length = filesize($out);
        fclose($out);

        exit;
    }

    /**
     * Purge IDS Log.
     *
     * @param none
     *
     * @return bool true if successful, false otherwise.
     */
    public function purgeidslog($args)
    {
        if (!SecurityUtil::checkPermission('SecurityCenter::', '::', ACCESS_DELETE)) {
            return false;
        }

        if (!DBUtil::truncateTable('sc_intrusion')) {
                return false;
        }

        return true;
    }

    /**
     * get available admin panel links
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('SecurityCenter::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('SecurityCenter', 'admin', 'modifyconfig'), 'text' => $this->__('Settings'), 'class' => 'z-icon-es-config');
            $links[] = array('url' => ModUtil::url('SecurityCenter', 'admin', 'allowedhtml'), 'text' => $this->__('Allowed HTML settings'), 'class' => 'z-icon-es-config');
            $links[] = array('url' => ModUtil::url('SecurityCenter', 'admin', 'viewidslog'), 'text' => $this->__('View IDS Log'), 'class' => 'z-icon-es-log');

            $outputfilter = System::getVar('outputfilter');
            if ($outputfilter == 1) {
                $links[] = array('url' => ModUtil::url('SecurityCenter', 'admin', 'purifierconfig'), 'text' => $this->__('HTMLPurifier settings'), 'class' => 'z-icon-es-config');
            }
        }

        return $links;
    }
}