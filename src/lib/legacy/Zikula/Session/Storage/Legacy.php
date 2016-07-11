<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

/**
 * Legacy session storage class.
 *
 * This Storage driver couples directly to the old SessionUtil methodology.
 * This will eventually be phased out.
 *
 * @deprecated
 */
class Zikula_Session_Storage_Legacy extends NativeSessionStorage
{
    /**
     * {@inheritdoc}
     */
    public function start()
    {
        // create IP finger print
        $current_ipaddr = '';
        $_REMOTE_ADDR = System::serverGetVar('REMOTE_ADDR');
        $_HTTP_X_FORWARDED_FOR = System::serverGetVar('HTTP_X_FORWARDED_FOR');

        // create the ip fingerprint
        $current_ipaddr = md5($_REMOTE_ADDR . $_HTTP_X_FORWARDED_FOR);

        // start session check expiry and ip fingerprint if required
        if (parent::start()) {
            // check if session has expired or not
            $now = time();
            $inactive = ($now - (int)(System::getVar('secinactivemins') * 60));
            $daysold = ($now - (int)(System::getVar('secmeddays') * 86400));
            $lastused = $this->getMetadataBag()->getLastUsed();
            $rememberme = SessionUtil::getVar('rememberme');
            $uid = $this->getBag('attributes')->get('uid');

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
                        $this->expire();
                    }
                    break;
                case 'High':
                default:
                    // High security - delete session info if user is inactive
                    //if ($rememberme && ($lastused < $inactive)) { // see #427
                    if ($lastused < $inactive) {
                        $this->expire();
                    }
                    break;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function expire()
    {
        SessionUtil::expire();
    }

    /**
     * {@inheritdoc}
     */
    public function regenerate($destroy = false, $lifetime = null)
    {
        parent::regenerate($destroy);
    }
}
