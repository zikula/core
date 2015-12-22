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

namespace Zikula\UsersModule\Listener;

use ModUtil;
use SecurityUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\Core\Event\GenericEvent;
use Zikula_Collection_Container;
use Zikula_Provider_AggregateItem;

class PendingContentListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'get.pending_content' => array('pendingContent'),
        );
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
