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

namespace Zikula\Module\UsersModule\Listener;

use ModUtil;
use UserUtil;
use SecurityUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Module\UsersModule\Constant as UsersConstant;
use Zikula\Core\Event\GenericEvent;
use Zikula_Collection_Container;
use Zikula_Provider_AggregateItem;

class CoreEventListener implements EventSubscriberInterface
{

    private $session;

    public static function getSubscribedEvents()
    {
        return array(
            'user.logout.succeeded' => array('clearUsersNamespace'),
            'frontcontroller.exception' => array('clearUsersNamespace'),
            'user.login.veto' => array('forcedPasswordChange'),
            'get.pending_content' => array('pendingContent'),
        );
    }

    public function __construct(\Zikula_Session $session)
    {
        $this->session = $session;
    }

    /**
     * Clears the session variable namespace used by the Users module.
     * Triggered by the 'user.logout.succeeded' and 'frontcontroller.exception' events.
     * This is to ensure no leakage of authentication information across sessions or between critical
     * errors. This prevents, for example, the login process from becoming confused about its state
     * if it detects session variables containing authentication information which might make it think
     * that a re-attempt is in progress.
     *
     * @param GenericEvent $event The event that triggered this handler.
     *
     * @return void
     */
    public function clearUsersNamespace(GenericEvent $event)
    {
        $eventName = $event->getName();
        $modinfo = $event->hasArgument('modinfo') ? $event->getArgument('modinfo') : array();

        $doClear = ($eventName == 'user.logout.succeeded') || (($eventName == 'frontcontroller.exception')
                && isset($modinfo) && is_array($modinfo) && !empty($modinfo) && !isset($modinfo['name']) && ($modinfo['name'] == UsersConstant::MODNAME));

        if ($doClear) {
            $this->session->clearNamespace(UsersConstant::SESSION_VAR_NAMESPACE);
            //Do not setNotified. Not handling the exception, just reacting to it.
        }
    }

    /**
     * Vetos (denies) a login attempt, and forces the user to change his password.
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
     *
     * @throws \RuntimeException Thrown if the user hasn't changed the account password
     */
    public static function forcedPasswordChange(GenericEvent $event)
    {
        $userObj = $event->getSubject();

        $userMustChangePassword = UserUtil::getVar('_Users_mustChangePassword', $userObj['uid'], false);

        if ($userMustChangePassword && ($userObj['pass'] != UsersConstant::PWD_NO_USERS_AUTHENTICATION)) {
            $event->stopPropagation();
            $event->setData(array(
                'redirect_func' => array(
                    'modname' => UsersConstant::MODNAME,
                    'type'    => 'user',
                    'func'    => 'changePassword',
                    'args'    => array(
                        'login' => true,
                    ),
                    'session' => array(
                        'var'       => 'User_changePassword',
                        'namespace' => UsersConstant::SESSION_VAR_NAMESPACE,
                    )
                ),
            ));

            throw new \RuntimeException(__("Your log-in request was not completed. You must change your web site account's password first."));
        }
    }

    /**
     * Respond to 'get.pending_content' events with registration requests pending approval.
     * When a 'get.pending_content' event is fired, the Users module will respond with the
     * number of registration requests that are pending administrator approval. The number
     * pending may not equal the total number of outstanding registration requests, depending
     * on how the 'moderation_order' module configuration variable is set, and whether e-mail
     * address verification is required.
     * If the 'moderation_order' variable is set to require approval after e-mail verification
     * (and e-mail verification is also required) then the number of pending registration
     * requests will equal the number of registration requested that have completed the
     * verification process but have not yet been approved. For other values of
     * 'moderation_order', the number should equal the number of registration requests that
     * have not yet been approved, without regard to their current e-mail verification state.
     * If moderation of registrations is not enabled, then the value will always be 0.
     * In accordance with the 'get_pending_content' conventions, the count of pending
     * registrations, along with information necessary to access the detailed list, is
     * assemped as a {@link Zikula_Provider_AggregateItem} and added to the event
     * subject's collection.
     *
     * @param GenericEvent $event The event that was fired, a 'get_pending_content' event.
     *
     * @return void
     */
    public static function pendingContent(GenericEvent $event)
    {
        if (SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_MODERATE)) {
            $approvalOrder = ModUtil::getVar(UsersConstant::MODNAME, 'moderation_order', UsersConstant::APPROVAL_ANY);
            if ($approvalOrder == UsersConstant::APPROVAL_AFTER) {
                $numPendingApproval = ModUtil::apiFunc(UsersConstant::MODNAME, 'registration', 'countAll', array('filter' => array('approved_by' => 0, 'isverified' => true)));
            } else {
                $numPendingApproval = ModUtil::apiFunc(UsersConstant::MODNAME, 'registration', 'countAll', array('filter' => array('approved_by' => 0)));
            }

            if (!empty($numPendingApproval)) {
                $collection = new Zikula_Collection_Container(UsersConstant::MODNAME);
                $collection->add(new Zikula_Provider_AggregateItem('registrations', __('Registrations pending approval'), $numPendingApproval, 'admin', 'viewRegistrations'));
                $event->getSubject()->add($collection);
            }
        }
    }

}