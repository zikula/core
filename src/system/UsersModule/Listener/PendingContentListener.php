<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Common\Collection\Collectible\PendingContentCollectible;
use Zikula\Common\Collection\Container;
use Zikula\Core\Event\GenericEvent;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;

class PendingContentListener implements EventSubscriberInterface
{
    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * PendingContentListener constructor.
     * @param PermissionApi $permissionApi
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(PermissionApi $permissionApi, UserRepositoryInterface $userRepository)
    {
        $this->permissionApi = $permissionApi;
        $this->userRepository = $userRepository;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'get.pending_content' => array('pendingContent'),
        );
    }

    /**
     * Respond to 'get.pending_content' events with registration requests pending approval.
     * When a 'get.pending_content' event is fired, the Users module will respond with the
     * number of users that are pending administrator approval.
     * In accordance with the 'get_pending_content' conventions, the count of pending
     * registrations, along with information necessary to access the detailed list, is
     * assumed as a {@link PendingContentCollectible} and added to the event
     * subject's collection.
     *
     * @param GenericEvent $event The event that was fired, a 'get_pending_content' event.
     *
     * @return void
     */
    public function pendingContent(GenericEvent $event)
    {
        if ($this->permissionApi->hasPermission(UsersConstant::MODNAME . '::', '::', ACCESS_MODERATE)) {

            $numPendingApproval = $this->userRepository->count([
                'approved_by' => 0,
                'activated' => UsersConstant::ACTIVATED_PENDING_REG
            ]);

            if (!empty($numPendingApproval)) {
                $collection = new Container(UsersConstant::MODNAME);
                $collection->add(new PendingContentCollectible('user_registrations', __('Users pending approval'), $numPendingApproval, 'zikulausersmodule_useradministration_list'));
                $event->getSubject()->add($collection);
            }
        }
    }
}
