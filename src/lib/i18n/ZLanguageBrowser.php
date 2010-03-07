<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class ZLanguageBrowser
{
    private $available;

    public function __construct($langList)
    {
        $this->available = $langList;
    }

    public function discover()
    {
        return $this->getPreferredLanguage();
    }

    private function getPreferredLanguage()
    {
        // Get system languages
        $sysLang = $this->available;

        // Get browser languages
        $browserLang = (empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? false : $_SERVER['HTTP_ACCEPT_LANGUAGE']);

        // Check arguments
        if (!$browserLang || !$sysLang || empty($sysLang))
        {
            return false;
        }

        // Explode the browser languages into a table
        $browserLang      = explode(',', $browserLang);
        $browserLangArray = array();

        foreach ($browserLang as $curLang)
        {
            $curLang = trim($curLang);
            $curLang = explode(';', $curLang);

            if(!empty($curLang[1])) {
                $curLangScore = explode('=', $curLang[1]);
                $browserLangArray[$curLang[0]] = (float)$curLangScore[1];
            } else {
                $browserLangArray[$curLang[0]] = (float)1.0;
            }
        }

        // Check if one of the specific browser language is in the list of system languages
        $langScore = 0;

        foreach ($browserLangArray as $key => $value)
        {
            if (in_array($key, $sysLang) && ($value > $langScore)) {
                $langScore = $value;
                $langName  = $key;
            }
        }

        // Return language name or false
        if ($langScore != 0) {
            return $langName;
        }

        return false;
    }
}
