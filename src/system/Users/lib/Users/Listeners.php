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
 * Provides listeners (handlers) for events.
 */
class Users_Listeners
{

    public static function pendingContentListener(Zikula_Event $event)
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            return;
        }

        $approvalOrder = ModUtil::getVar('Users', 'moderation_order', UserUtil::APPROVAL_ANY);
        if ($approvalOrder == UserUtil::APPROVAL_AFTER) {
            $numPendingApproval = ModUtil::apiFunc('Users', 'registration', 'countAll', array('filter' => array('approved_by' => 0, 'isverified' => true)));
        } else {
            $numPendingApproval = ModUtil::apiFunc('Users', 'registration', 'countAll', array('filter' => array('approved_by' => 0)));
        }

        if (!empty($numPendingApproval)) {
            $collection = new Zikula_Collection_Container('Users');
            $collection->add(new Zikula_Provider_AggregateItem('registrations', __('Registrations pending approval'), $numPendingApproval, 'admin', 'viewRegistrations'));
            $event->getSubject()->add($collection);
        }
    }
}
