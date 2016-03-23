<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\SecurityCenterModule\Listener;

use Zikula_Core;
use System;
use CacheUtil;
use SessionUtil;
use UserUtil;
use DateUtil;
use ModUtil;
use ServiceUtil;
use Zikula\SecurityCenterModule\Util as SecurityCenterUtil;
use Zikula_Event;
use Zikula\SecurityCenterModule\Entity\IntrusionEntity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Event handler for the security center module
 *
 * Adds the intrustion detection filter to the core.init phase and an output filter to the system.outputfiler phase.
 */
class FilterListener extends \Zikula_AbstractEventHandler
{
    /**
     * Setup this handler.
     *
     * @return void
     */
    protected function setupHandlerDefinitions()
    {
        $this->addHandlerDefinition('core.init', 'idsInputFilter');
        $this->addHandlerDefinition('system.outputfilter', 'outputFilter');
    }

    /**
     * Protects against basic attempts of Cross-Site Scripting (XSS).
     *
     * @see    http://technicalinfo.net/papers/CSS.html
     *
     * @return void
     *
     * @throws \Exception Thrown if there was a problem running ids detection
     */
    public function idsInputFilter(Zikula_Event $event)
    {
        if ($event['stage'] & Zikula_Core::STAGE_MODS && System::getVar('useids') == 1) {
            // Run IDS if desired
            try {
                $request = array();
                // build request array defining what to scan
                // @todo: change the order of the arrays to merge if ini_get('variables_order') != 'EGPCS'
                if (isset($_REQUEST)) {
                    $request['REQUEST'] = $_REQUEST;
                }
                if (isset($_GET)) {
                    $request['GET'] = $_GET;
                }
                if (isset($_POST)) {
                    $request['POST'] = $_POST;
                }
                if (isset($_COOKIE)) {
                    $request['COOKIE'] = $_COOKIE;
                }
                if (isset($_SERVER['HTTP_HOST'])) {
                    $request['HOST'] = $_SERVER['HTTP_HOST'];
                }
                if (isset($_SERVER['HTTP_ACCEPT'])) {
                    $request['ACCEPT'] = $_SERVER['HTTP_ACCEPT'];
                }
                if (isset($_SERVER['USER_AGENT'])) {
                    $request['USER_AGENT'] = $_SERVER['USER_AGENT'];
                }
                // while i think that REQUEST_URI is unnecessary,
                // the REFERER would be important, but results in way too many false positives
                /*
                if (isset($_SERVER['REQUEST_URI'])) {
                    $request['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
                }
                if (isset($_SERVER['HTTP_REFERER'])) {
                    $request['REFERER'] = $_SERVER['HTTP_REFERER'];
                }
                */

                // initialise configuration object
                $init = \IDS\Init::init();

                // set configuration options
                $init->config = $this->_getidsconfig();

                // create new IDS instance
                $ids = new \IDS\Monitor($init);

                // run the request check and fetch the results
                $result = $ids->run($request);

                // analyze the results
                if (!$result->isEmpty()) {
                    // process the \IDS\Report object
                    $this->_processIdsResult($init, $result);
                } else {
                    // no attack detected
                }
            } catch (\Exception $e) {
                // sth went wrong - maybe the filter rules weren't found
                throw new \Exception(__f('An error occured during executing PHPIDS: %s', $e->getMessage()));
            }
        }
    }

    /**
     * Retrieves configuration array for PHPIDS.
     *
     * @return array IDS configuration settings.
     */
    private function _getidsconfig()
    {
        $config = array();

        // General configuration settings
        $config['General'] = array();

        $config['General']['filter_type'] = System::getVar('idsfilter', 'xml');
        if (empty($config['General']['filter_type'])) {
            $config['General']['filter_type'] = 'xml';
        }

        $config['General']['base_path'] = ''; //PHPIDS_PATH_PREFIX;
        // we don't use the base path because the tmp directory is in zkTemp (see below)
        $config['General']['use_base_path'] = false;

        // path to the filters used
        $config['General']['filter_path'] = System::getVar('idsrulepath', 'config/phpids_zikula_default.xml');
        // path to (writable) tmp directory
        $config['General']['tmp_path'] = CacheUtil::getLocalDir() . '/idsTmp';
        $config['General']['scan_keys'] = false;

        // we use a different HTML Purifier source
        // by default PHPIDS does also contain those files
        // we do this more efficiently in boostrap (drak).
        $config['General']['HTML_Purifier_Path'] = ''; // this must be set or IDS/Monitor will never fill in the HTML_Purifier_Cache property (drak).
        $config['General']['HTML_Purifier_Cache'] = CacheUtil::getLocalDir() . '/purifierCache';

        // define which fields contain html and need preparation before hitting the PHPIDS rules
        $config['General']['html'] = System::getVar('idshtmlfields', array());

        // define which fields contain JSON data and should be treated as such for fewer false positives
        $config['General']['json'] = System::getVar('idsjsonfields', array());

        // define which fields shouldn't be monitored (a[b]=c should be referenced via a.b)
        $config['General']['exceptions'] = System::getVar('idsexceptions', array());

        // PHPIDS should run with PHP 5.1.2 but this is untested - set this value to force compatibilty with minor versions
        $config['General']['min_php_version'] = '5.1.6';

        // caching settings
        // @todo: add UI for those caching settings
        $config['Caching'] = array();

        // caching method (session|file|database|memcached|none), default file
        $config['Caching']['caching'] = 'none'; // deactivate caching for now
        $config['Caching']['expiration_time'] = 600;

        // file cache
        $config['Caching']['path'] = $config['General']['tmp_path'] . '/default_filter.cache';

        // database cache
        //$config['Caching']['wrapper'] = 'mysql:host=localhost;port=3306;dbname=phpids';
        //$config['Caching']['user'] = 'phpids_user';
        //$config['Caching']['password'] = '123456';
        //$config['Caching']['table'] = 'cache';

        // memcached
        //$config['Caching']['host'] = 'localhost';
        //$config['Caching']['port'] = 11211;
        //$config['Caching']['key_prefix'] = 'PHPIDS';
        //$config['Caching']['tmp_path'] = $config['General']['tmp_path'] . '/memcache.timestamp';

        return $config;
    }

    /**
     * Process results from IDS scan.
     *
     * @param \IDS\Init   $init   PHPIDS init object reference.
     * @param \IDS\Report $result The result object from PHPIDS.
     *
     * @return void
     */
    private function _processIdsResult(\IDS\Init $init, \IDS\Report $result)
    {
        // $result contains any suspicious fields enriched with additional info

        // Note: it is moreover possible to dump this information by simply doing
        //"echo $result", calling the \IDS\Report::$this->__toString() method implicitely.

        $requestImpact = $result->getImpact();
        if ($requestImpact < 1) {
            // nothing to do
            return;
        }

        // update total session impact to track an attackers activity for some time
        $sessionImpact = SessionUtil::getVar('idsImpact', 0) + $requestImpact;
        SessionUtil::setVar('idsImpact', $sessionImpact);

        // let's see which impact mode we are using
        $idsImpactMode = System::getVar('idsimpactmode', 1);
        $idsImpactFactor = 1;
        if ($idsImpactMode == 1) {
            $idsImpactFactor = 1;
        } elseif ($idsImpactMode == 2) {
            $idsImpactFactor = 10;
        } elseif ($idsImpactMode == 3) {
            $idsImpactFactor = 5;
        }

        // determine our impact threshold values
        $impactThresholdOne   = System::getVar('idsimpactthresholdone', 1) * $idsImpactFactor;
        $impactThresholdTwo   = System::getVar('idsimpactthresholdtwo', 10) * $idsImpactFactor;
        $impactThresholdThree = System::getVar('idsimpactthresholdthree', 25) * $idsImpactFactor;
        $impactThresholdFour  = System::getVar('idsimpactthresholdfour', 75) * $idsImpactFactor;

        $usedImpact = ($idsImpactMode == 1) ? $requestImpact : $sessionImpact;

        // react according to given impact
        if ($usedImpact > $impactThresholdOne) {
            // db logging

            // determine IP address of current user
            $_REMOTE_ADDR = System::serverGetVar('REMOTE_ADDR');
            $_HTTP_X_FORWARDED_FOR = System::serverGetVar('HTTP_X_FORWARDED_FOR');
            $ipAddress = ($_HTTP_X_FORWARDED_FOR) ? $_HTTP_X_FORWARDED_FOR : $_REMOTE_ADDR;

            $currentPage = System::getCurrentUri();
            $currentUid = UserUtil::getVar('uid');
            if (!$currentUid) {
                $currentUid = 1;
            }

            // get entity manager
            $em = ServiceUtil::get('doctrine.orm.default_entity_manager');

            $intrusionItems = array();

            foreach ($result as $event) {
                $eventName = $event->getName();
                $malVar = explode(".", $eventName, 2);

                $filters = array();
                foreach ($event as $filter) {
                    array_push($filters, array(
                                            'id' => $filter->getId(),
                                            'description' => $filter->getDescription(),
                                            'impact' => $filter->getImpact(),
                                            'tags' => $filter->getTags(),
                                            'rule' => $filter->getRule()));
                }

                $tagVal = $malVar[1];

                $newIntrusionItem = array(
                    'name'    => array($eventName),
                    'tag'     => $tagVal,
                    'value'   => $event->getValue(),
                    'page'    => $currentPage,
                    'user'    => $em->getReference('ZikulaUsersModule:UserEntity', $currentUid),
                    'ip'      => $ipAddress,
                    'impact'  => $result->getImpact(),
                    'filters' => serialize($filters),
                    'date'    => new \DateTime("now")
                );

                if (array_key_exists($tagVal, $intrusionItems)) {
                    $intrusionItems[$tagVal]['name'][] = $newIntrusionItem['name'][0];
                } else {
                    $intrusionItems[$tagVal] = $newIntrusionItem;
                }
            }

            // log details to database
            foreach ($intrusionItems as $tag => $intrusionItem) {
                $intrusionItem['name'] = implode(", ", $intrusionItem['name']);

                $obj = new IntrusionEntity();
                $obj->merge($intrusionItem);
                $em->persist($obj);
            }

            $em->flush();
        }

        if (System::getVar('idsmail') && ($usedImpact > $impactThresholdTwo)) {
            // mail admin

            // prepare mail text
            $mailBody = __('The following attack has been detected by PHPIDS') . "\n\n";
            $mailBody .= __f('IP: %s', $ipAddress) . "\n";
            $mailBody .= __f('UserID: %s', $currentUid) . "\n";
            $mailBody .= __f('Date: %s', DateUtil::strftime(__('%b %d, %Y'), (time()))) . "\n";
            if ($idsImpactMode == 1) {
                $mailBody .= __f('Request Impact: %d', $requestImpact) . "\n";
            } else {
                $mailBody .= __f('Session Impact: %d', $sessionImpact) . "\n";
            }
            $mailBody .= __f('Affected tags: %s', implode(' ', $result->getTags())) . "\n";

            $attackedParameters = '';
            foreach ($result as $event) {
                $attackedParameters .= $event->getName() . '=' . urlencode($event->getValue()) . ", ";
            }

            $mailBody .= __f('Affected parameters: %s', trim($attackedParameters)) . "\n";
            $mailBody .= __f('Request URI: %s', urlencode($currentPage));

            // prepare other mail arguments
            $siteName = System::getVar('sitename');
            $adminmail = System::getVar('adminmail');
            $mailTitle = __('Intrusion attempt detected by PHPIDS');

            if (ModUtil::available('ZikulaMailerModule')) {
                $args = array();
                $args['fromname']    = $siteName;
                $args['fromaddress'] = $adminmail;
                $args['toname']      = 'Site Administrator';
                $args['toaddress']   = $adminmail;
                $args['subject']     = $mailTitle;
                $args['body']        = $mailBody;

                $rc = ModUtil::apiFunc('ZikulaMailerModule', 'user', 'sendmessage', $args);
            } else {
                $headers = "From: $siteName <$adminmail>\n"
                        ."X-Priority: 1 (Highest)";
                System::mail($adminmail, $mailTitle, $mailBody, $headers);
            }
        }

        if ($usedImpact > $impactThresholdThree) {
            // block request

            if (System::getVar('idssoftblock')) {
                // warn only for debugging the ruleset
                throw new \RuntimeException(__('Malicious request code / a hacking attempt was detected. This request has NOT been blocked!'));
            } else {
                throw new AccessDeniedException(__('Malicious request code / a hacking attempt was detected. Thus this request has been blocked.'), null, $result);
            }
        }

        return;
    }

    /**
     * output filter to implement html purifier
     *
     * @param \Zikula_Event $event event object
     *
     * @return mixed modified event data
     */
    public function outputFilter(Zikula_Event $event)
    {
        if (System::getVar('outputfilter') > 1) {
            return;
        }

        // recursive call for arrays
        // [removed as it's duplicated in datautil]

        // prepare htmlpurifier class
        static $safecache;
        $purifier = SecurityCenterUtil::getpurifier();

        $md5 = md5($event->data);
        // check if the value is in the safecache
        if (isset($safecache[$md5])) {
            $event->data = $safecache[$md5];
        } else {

            // save renderer delimiters
            $event->data = str_replace('{', '%VIEW_LEFT_DELIMITER%', $event->data);
            $event->data = str_replace('}', '%VIEW_RIGHT_DELIMITER%', $event->data);
            $event->data = $purifier->purify($event->data);

            // restore renderer delimiters
            $event->data = str_replace('%VIEW_LEFT_DELIMITER%', '{', $event->data);
            $event->data = str_replace('%VIEW_RIGHT_DELIMITER%', '}', $event->data);

            // cache the value
            $safecache[$md5] = $event->data;
        }

        return $event->data;
    }
}
