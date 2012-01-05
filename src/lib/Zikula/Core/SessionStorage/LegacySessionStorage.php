<?php

/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Session
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core\SessionStorage;

use Symfony\Component\HttpFoundation\AttributeBagInterface;
use Symfony\Component\HttpFoundation\FlashBagInterface;
use Symfony\Component\HttpFoundation\SessionStorage\SessionSaveHandlerInterface;
use Symfony\Component\HttpFoundation\SessionStorage\AbstractSessionStorage;
use \SessionUtil;
use \System;
use \DataUtil;
use \DBUtil;

/**
 * Legacy session storage class.
 *
 * This Storage driver couples directly to the old SessionUtil methodology.
 * This will eventually be phased out.
 */
class LegacySessionStorage extends AbstractSessionStorage implements SessionSaveHandlerInterface
{
    private $isNew = true;

    private $object = array();

    private $isRegenerated = false;

    private $previousId = '';

    private $expired = false;

    public function __construct(AttributeBagInterface $attributes = null, FlashBagInterface $flashes = null, array $options = array())
    {
        // create IP finger print
        $current_ipaddr = '';
        $_REMOTE_ADDR = System::serverGetVar('REMOTE_ADDR');
        $_HTTP_X_FORWARDED_FOR = System::serverGetVar('HTTP_X_FORWARDED_FOR');

        if (System::getVar('sessionipcheck')) {
            // feature for future release
        }

        // create the ip fingerprint
        $current_ipaddr = md5($_REMOTE_ADDR . $_HTTP_X_FORWARDED_FOR);

        $this->object = array(
            'lastused' => date('Y-m-d H:i:s', time()),
            'uid' => 0,
            'ipaddr' => $current_ipaddr,
            'remember' => 0,
            'vars' => '',
        );

        $path = System::getBaseUri();
        if (empty($path)) {
            $path = '/';
        } elseif (substr($path, -1, 1) != '/') {
            $path .= '/';
        }

        $options = array_merge(array(
            'auto_start' => 0,
            'use_cookies' => 1,
            'gc_probability' => System::getVar('gc_probability'),
            'gc_divisor' => 10000,
            'gc_maxlifetime' => System::getVar('secinactivemins') * 60,
            'hash_function' => 1,
            'cookie_path' => $path,
            ), $options);

        parent::__construct($attributes, $flashes, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        if ($this->started && !$this->closed) {
            return true;
        }

        $host = System::serverGetVar('HTTP_HOST');

        if (($pos = strpos($host, ':')) !== false) {
            $host = substr($host, 0, $pos);
        }

        ini_set('session.name', SessionUtil::getCookieName()); // Name of our cookie
        // Set lifetime of session cookie
        $seclevel = System::getVar('seclevel');
        switch ($seclevel) {
            case 'High':
                // Session lasts duration of browser
                $lifetime = 0;
                // Referer check
                ini_set('session.referer_check', $host);
                break;
            case 'Medium':
                // Session lasts set number of days
                $lifetime = System::getVar('secmeddays') * 86400;
                break;
            case 'Low':
            default:
                // Session lasts unlimited number of days (well, lots, anyway)
                // (Currently set to 25 years)
                $lifetime = 788940000;
                break;
        }

        ini_set('session.cookie_lifetime', $lifetime);

        // create IP finger print
        $current_ipaddr = '';
        $_REMOTE_ADDR = System::serverGetVar('REMOTE_ADDR');
        $_HTTP_X_FORWARDED_FOR = System::serverGetVar('HTTP_X_FORWARDED_FOR');

        if (System::getVar('sessionipcheck')) {
            // feature for future release
        }

        // create the ip fingerprint
        $current_ipaddr = md5($_REMOTE_ADDR . $_HTTP_X_FORWARDED_FOR);

        // start session check expiry and ip fingerprint if required
        if (session_start() && !$this->isNew) {
            $this->loadSession();
            // check if session has expired or not
            $now = time();
            $inactive = ($now - (int)(System::getVar('secinactivemins') * 60));
            $daysold = ($now - (int)(System::getVar('secmeddays') * 86400));
            $lastused = $this->object['lastused'];
            $this->attributeBag->set('lastused', date('Y-m-d H:i:s', time()));
            $this->attributeBag->set('uid', $this->object['uid']);
            $rememberme = $this->attributeBag->get('rememberme', 0);

            switch (System::getVar('seclevel')) {
                case 'Low':
                    // Low security - users stay logged in permanently
                    //                no special check necessary

                    break;
                case 'Medium':
                    // Medium security - delete session info if session cookie has
                    // expired or user decided not to remember themself and inactivity timeout
                    // OR max number of days have elapsed without logging back in
                    if ((!$rememberme && $lastused < $inactive) || ($lastused < $daysold) || ($uid == '0' && $lastused < $inactive)) {
                        //$this->expire();
                    }

                    break;
                case 'High':
                default:
                    // High security - delete session info if user is inactive
                    if ($lastused < $inactive) {
                        //$this->expire();
                    }

                    break;
            }
        } else {
            $this->createNew(session_id(), $current_ipaddr);
        }

        $this->started = true;
        $this->closed = false;

        return true;
    }

    private function createNew($sessid, $ipaddr)
    {
        $this->object = array(
            'sessid' => $sessid,
            'lastused' => date('Y-m-d H:i:s', time()),
            'uid' => 0,
            'ipaddr' => $ipaddr,
            'remember' => 0,
            'vars' => '',
        );

        $_SESSION = array();
        $this->loadSession();

        $this->attributeBag->set('uid', 0);
        $this->attributeBag->set('rememberme', 0);
        $this->attributeBag->set('useragent', sha1(System::serverGetVar('HTTP_USER_AGENT')));

        $this->isNew = true;

        return true;
    }

    /**
     * Let session expire nicely
     *
     * @return void
     */
    public function expire()
    {
        if ($this->attributeBag->get('uid') == '0') {
            // no need to do anything for guests without sessions
            if ($this->attributeBag->get('anonymoussessions') == '0' && !session_id()) {
                return;
            }

            // no need to display expiry for anon users with sessions since it's invisible anyway
            // handle expired sessions differently
            $this->createNew(session_id(), $this->object['ipaddr']);
            // session is not new, remove flag
            $this->isNew = false;
            $this->regenerate(true);
            return;
        }

        // for all logged in users with session destroy session and set flag
        //session_destroy();
        $this->expired = true;
    }

    public function isRegenerated()
    {
        return $this->isRegenerated;
    }

    public function isExpired()
    {
        return $this->expired;
    }

    public function isNew()
    {
        return $this->isNew;
    }

    /**
     * {@inheritdoc}
     */
    public function regenerate($destroy = false)
    {
        return;
        // only regenerate if set in admin
        if ($destroy == false) {
            if (!System::getVar('sessionregenerate') || System::getVar('sessionregenerate') == 0) {
                // there is no point changing a newly generated session.
                if (isset($this->isNew)) {
                    return;
                }

                return;
            }
        }

        // dont allow multiple regerations
        if ($this->isRegenerated) {
            return;
        }

        $this->previousId = session_id(); // save old session id

        session_regenerate_id($destroy);

        $this->object['sessid'] = session_id(); // commit new sessid
        $this->isRegenerated = true; // flag regeneration
    }

    /**
     * {@inheritdoc}
     */
    public function sessionOpen($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function sessionClose()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function sessionRead($sessionId)
    {
        $result = DBUtil::selectObjectByID('session_info', $sessionId, 'sessid');
        if (!$result) {
            $this->isNew = true;
            return '';
        }

        $this->object = $result;
        $this->isNew = false;

        return (isset($result['vars']) ? $result['vars'] : '');
    }

    /**
     * {@inheritdoc}
     */
    public function sessionWrite($sessionId, $vars)
    {
        $this->object['vars'] = $vars;
        $this->object['remember'] = $this->attributeBag->get('rememberme', 0);
        $this->object['uid'] = $this->attributeBag->get('uid', 0);
        $this->object['lastused'] = date('Y-m-d H:i:s', time());

        $obj = $this->object;
        $obj['sessid'] = $sessionId;

        if ($this->isNew) {
            $res = DBUtil::insertObject($obj, 'session_info', 'sessid', true);
            $this->isNew = false;
        } else {
            // check for regenerated session and update ID in database
            if ($this->isRegenerated) {
                $sessiontable = DBUtil::getTables();
                $columns = $sessiontable['session_info_column'];
                $where = "WHERE $columns[sessid] = '" . DataUtil::formatForStore($this->previousId) . "'";
                $res = DBUtil::updateObject($obj, 'session_info', $where, 'sessid', true, true);
            } else {
                $res = DBUtil::updateObject($obj, 'session_info', '', 'sessid', true);
            }
        }


        return (bool)$res;
    }

    /**
     * {@inheritdoc}
     */
    public function sessionDestroy($sessionId)
    {
        $res = DBUtil::deleteObjectByID('session_info', $sessionId, 'sessid');
        return (bool)$res;
    }

    /**
     * {@inheritdoc}
     */
    public function sessionGc($lifetime)
    {
        $now = time();
        $inactive = ($now - (int)(System::getVar('secinactivemins') * 60));
        $daysold = ($now - (int)(System::getVar('secmeddays') * 86400));

        // find the hash length dynamically
        $hash = ini_get('session.hash_function');
        if (empty($hash) || $hash == 0) {
            $sessionlength = 32;
        } else {
            $sessionlength = 40;
        }

        // DB based GC
        $dbtable = DBUtil::getTables();
        $sessioninfocolumn = $dbtable['session_info_column'];
        $inactive = DataUtil::formatForStore(date('Y-m-d H:i:s', $inactive));
        $daysold = DataUtil::formatForStore(date('Y-m-d H:i:s', $daysold));

        switch (System::getVar('seclevel')) {
            case 'Low':
                // Low security - delete session info if user decided not to
                //                remember themself and inactivity timeout
                $where = "WHERE $sessioninfocolumn[remember] = 0
                          AND $sessioninfocolumn[lastused] < '$inactive'";
                break;
            case 'Medium':
                // Medium security - delete session info if session cookie has
                // expired or user decided not to remember themself and inactivity timeout
                // OR max number of days have elapsed without logging back in
                $where = "WHERE ($sessioninfocolumn[remember] = 0
                          AND $sessioninfocolumn[lastused] < '$inactive')
                          OR ($sessioninfocolumn[lastused] < '$daysold')
                          OR ($sessioninfocolumn[uid] = 0 AND $sessioninfocolumn[lastused] < '$inactive')";
                break;
            case 'High':
            default:
                // High security - delete session info if user is inactive
                $where = "WHERE $sessioninfocolumn[lastused] < '$inactive'";
                break;
        }

        $res = DBUtil::deleteWhere('session_info', $where);
        return (bool)$res;
    }
}