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

/**
 * Persistent event listener for pending content queries.
 */
class Users_Listener_PendingContent
{
    /**
     * The module name.
     *
     * @var string
     */
    protected static $modname = Users_Constant::MODNAME;

    /**
     * Respond to 'get.pending_content' events with registration requests pending approval.
     *
     * When a 'get.pending_content' event is fired, the Users module will respond with the
     * number of registration requests that are pending administrator approval. The number
     * pending may not equal the total number of outstanding registration requests, depending
     * on how the 'moderation_order' module configuration variable is set, and whether e-mail
     * address verification is required.
     *
     * If the 'moderation_order' variable is set to require approval after e-mail verification
     * (and e-mail verification is also required) then the number of pending registration
     * requests will equal the number of registration requested that have completed the
     * verification process but have not yet been approved. For other values of
     * 'moderation_order', the number should equal the number of registration requests that
     * have not yet been approved, without regard to their current e-mail verification state.
     * If moderation of registrations is not enabled, then the value will always be 0.
     *
     * In accordance with the 'get_pending_content' conventions, the count of pending
     * registrations, along with information necessary to access the detailed list, is
     * assemped as a {@link Zikula_Provider_AggregateItem} and added to the event
     * subject's collection.
     *
     * @param Zikula_Event $event The event that was fired, a 'get_pending_content' event.
     *
     * @return void
     */
    public static function pendingContentListener(Zikula_Event $event)
    {
        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            $approvalOrder = ModUtil::getVar(self::$modname, 'moderation_order', Users_Constant::APPROVAL_ANY);
            if ($approvalOrder == Users_Constant::APPROVAL_AFTER) {
                $numPendingApproval = ModUtil::apiFunc(self::$modname, 'registration', 'countAll', array('filter' => array('approved_by' => 0, 'isverified' => true)));
            } else {
                $numPendingApproval = ModUtil::apiFunc(self::$modname, 'registration', 'countAll', array('filter' => array('approved_by' => 0)));
            }

            if (!empty($numPendingApproval)) {
                $collection = new Zikula_Collection_Container(self::$modname);
                $collection->add(new Zikula_Provider_AggregateItem('registrations', __('Registrations pending approval'), $numPendingApproval, 'admin', 'viewRegistrations'));
                $event->getSubject()->add($collection);
            }
        }
    }
}
