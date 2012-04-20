<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Users
 * @subpackage Listeners
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace UsersModule\Listener;

use Zikula\Core\Event\GenericEvent;
use UsersModule\Constants as UsersConstant;
use UserUtil, LogUtil;

/**
 * Persistent event listener for user.login.veto events that forces the change of a user's password.
 */
class ForcedPasswordChangeListener
{
    /**
     * The module name.
     *
     * @var string
     */
    protected static $modname = UsersConstant::MODNAME;

    /**
     * Vetos (denies) a login attempt, and forces the user to change his password.
     *
     * This handler is triggered by the 'user.login.veto' event.  It vetos (denies) a
     * login attempt if the users's account record is flagged to force the user to change
     * his password maintained by the Users module. If the user does not maintain a
     * password on his Users account (e.g., he registered with and logs in with a Google
     * Account or an OpenID, and never established a Users password), then this handler
     * will not trigger a change of password.
     *
     * @param GenericEvent $event The event that triggered this handler.
     *
     * @return void
     */
    public static function forcedPasswordChangeListener(GenericEvent $event)
    {
        $userObj = $event->getSubject();

        $userMustChangePassword = UserUtil::getVar('_Users_mustChangePassword', $userObj['uid'], false);

        if ($userMustChangePassword && ($userObj['pass'] != UsersConstant::PWD_NO_USERS_AUTHENTICATION)) {
            $event->stopPropagation();
            $event->setData(array(
                'redirect_func'  => array(
                    'modname'   => self::$modname,
                    'type'      => 'user',
                    'func'      => 'changePassword',
                    'args'      => array(
                        'login'     => true,
                    ),
                    'session'   => array(
                        'var'       => 'Users_Controller_User_changePassword',
                        'namespace' => 'Zikula_Users',
                    )
                ),
            ));

            LogUtil::registerError(__("Your log-in request was not completed. You must change your web site account's password first."));
        }
    }
}
