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
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Helper\RegistrationHelper;

class PendingContentListener implements EventSubscriberInterface
{
    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * @var RegistrationHelper
     */
    private $registrationHelper;

    /**
     * PendingContentListener constructor.
     * @param $variableApi
     * @param $permissionApi
     */
    public function __construct(VariableApi $variableApi, PermissionApi $permissionApi, RegistrationHelper $registrationHelper)
    {
        $this->variableApi = $variableApi;
        $this->permissionApi = $permissionApi;
        $this->registrationHelper = $registrationHelper;
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
     * number of registration requests that are pending administrator approval.
     * If moderation of registrations is not enabled, then the value will always be 0.
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
            $numPendingApproval = $this->registrationHelper->countAll(['approved_by' => 0]);

            if (!empty($numPendingApproval)) {
                $collection = new Container(UsersConstant::MODNAME);
                $collection->add(new PendingContentCollectible('user_registrations', __('Registrations pending approval'), $numPendingApproval, 'zikulausersmodule_registrationadministration_list'));
                $event->getSubject()->add($collection);
            }
        }
    }
}
