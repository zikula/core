<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\BlocksModule\Collectible\PendingContentCollectible;
use Zikula\BlocksModule\Event\PendingContentEvent;
use Zikula\Bundle\CoreBundle\Collection\Container;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;

class PendingContentListener implements EventSubscriberInterface
{
    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        PermissionApiInterface $permissionApi,
        UserRepositoryInterface $userRepository,
        TranslatorInterface $translator
    ) {
        $this->permissionApi = $permissionApi;
        $this->userRepository = $userRepository;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            PendingContentEvent::class => ['pendingContent']
        ];
    }

    /**
     * Respond to 'PendingContentEvent' with registration requests pending approval.
     * When a 'PendingContentEvent' is dispatched, the Users module will respond with the
     * number of users that are pending administrator approval.
     * In accordance with the 'get_pending_content' conventions, the count of pending
     * registrations, along with information necessary to access the detailed list, is
     * assumed as a {@link PendingContentCollectible} and added to the event's collection.
     */
    public function pendingContent(PendingContentEvent $event): void
    {
        if (!$this->permissionApi->hasPermission(UsersConstant::MODNAME . '::', '::', ACCESS_MODERATE)) {
            return;
        }

        $numPendingApproval = $this->userRepository->count([
            'approved_by' => 0,
            'activated' => UsersConstant::ACTIVATED_PENDING_REG
        ]);

        if (!empty($numPendingApproval)) {
            $collection = new Container(UsersConstant::MODNAME);
            $collection->add(new PendingContentCollectible('user_registrations', $this->translator->trans('Users pending approval'), $numPendingApproval, 'zikulausersmodule_useradministration_listusers'));
            $event->addCollection($collection);
        }
    }
}
