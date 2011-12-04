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

class SecurityCenter_Util
{
    /**
     * Retrieves default configuration array for HTML Purifier.
     *
     * @return array HTML Purifier default configuration settings.
     */
    private static function _getpurifierdefaultconfig()
    {
        $purifierDefaultConfig = HTMLPurifier_Config::createDefault();
        $purifierDefaultConfigValues = $purifierDefaultConfig->def->defaults;

        $config = array();

        foreach($purifierDefaultConfigValues as $key => $val)
        {
            $keys = explode(".", $key, 2);

            $config[$keys[0]][$keys[1]] = $val;
        }

        $charset = ZLanguage::getEncoding();
        if (strtolower($charset) != 'utf-8') {
            // set a different character encoding with iconv
            $config['Core']['Encoding'] = $charset;
            // Note that HTML Purifier's support for non-Unicode encodings is crippled by the
            // fact that any character not supported by that encoding will be silently
            // dropped, EVEN if it is ampersand escaped.  If you want to work around
            // this, you are welcome to read docs/enduser-utf8.html in the full package for a fix,
            // but please be cognizant of the issues the "solution" creates (for this
            // reason, I do not include the solution in this document).
        }

        // determine doctype of current theme
        // supported doctypes include:
        //
        // HTML 4.01 Strict
        // HTML 4.01 Transitional
        // XHTML 1.0 Strict
        // XHTML 1.0 Transitional (default)
        // XHTML 1.1
        //
        // TODO - we need a new theme field for doctype declaration
        // for now we will use non-strict modes
        $currentThemeID = ThemeUtil::getIDFromName(UserUtil::getTheme());
        $themeInfo = ThemeUtil::getInfo($currentThemeID);
        $useXHTML = (isset($themeInfo['xhtml']) && $themeInfo['xhtml']) ? true : false;

        // as XHTML 1.0 Transitional is the default, we only set HTML (for now)
        if (!$useXHTML) {
            $config['HTML']['Doctype'] = 'HTML 4.01 Transitional';
        }

        // allow nofollow and imageviewer to be used as document relationships in the rel attribute
        // see http://htmlpurifier.org/live/configdoc/plain.html#Attr.AllowedRel
        $config['Attr']['AllowedRel'] = array('nofollow' => true, 'imageviewer' => true, 'lightbox' => true);
        
        // allow Youtube by default
        $config['Filter']['YouTube'] = false; // technically deprecated in favour of HTML.SafeEmbed and HTML.Object

        // general enable for embeds and objects
        $config['HTML']['SafeObject'] = true;
        $config['Output']['FlashCompat'] = true;
        $config['HTML']['SafeEmbed'] = true;
                
        return $config;
    }

    /**
     * Retrieves configuration array for HTML Purifier.
     *
     * @param array $args All parameters for the function.
     *                    boolean $args['forcedefault'] true to force return of default config / false to auto detect
     * @param
     *
     * @return array HTML Purifier configuration settings.
     */
    public static function getpurifierconfig($args)
    {
        if (isset($args['forcedefault']) && $args['forcedefault'] == true) {
            $config = self::_getpurifierdefaultconfig();
        } else {
            // don't change the following statement to getVar()
            // $this is not allowed in functions declared as static
            $currentconfig = ModUtil::getVar('SecurityCenter', 'htmlpurifierConfig');

            if (!is_null($currentconfig) && ($currentconfig !== false)) {
                $config = unserialize($currentconfig);
            } else {
                $config = self::_getpurifierdefaultconfig();
            }
        }

        return $config;
    }

    /**
     * Retrieves an instance of HTMLPurifier.
     *
     * The instance returned is either a newly created instance, or previously created instance
     * that has been cached in a static variable.
     *
     * @param array $args All arguments for the function.
     *                    bool $args['force'] If true, the HTMLPurifier instance will be generated anew, rather than using an
     *                                          existing instance from the static variable.
     *
     * @staticvar array $purifier The HTMLPurifier instance.
     *
     * @return HTMLPurifier The HTMLPurifier instance, returned by reference.
     */
    public static function getpurifier($args = null)
    {
        $force = (isset($args['force']) ? $args['force'] : false);

        // prepare htmlpurifier class
        static $purifier;

        if (!isset($purifier) || $force) {
            $config = self::getpurifierconfig(array('forcedefault' => false));

            $config['Cache']['SerializerPath'] = CacheUtil::getLocalDir() . '/purifierCache';

            $purifier = new HTMLPurifier($config);
        }

        return $purifier;
    }
}
