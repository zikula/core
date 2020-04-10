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

namespace Zikula\UsersModule\Block;

use DateTime;
use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserSessionRepositoryInterface;

/**
 * A block that shows who is currently using the system.
 */
class OnlineBlock extends AbstractBlockHandler
{
    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var UserSessionRepositoryInterface
     */
    private $userSessionRepository;

    public function display(array $properties): string
    {
        if (!$this->hasPermission('Onlineblock::', $properties['bid'] . '::', ACCESS_READ)) {
            return '';
        }

        $inactiveLimit = $this->variableApi->getSystemVar('secinactivemins');
        $dateTime = new DateTime();
        $dateTime->modify('-' . $inactiveLimit . 'minutes');
        $amountOfUsers = $this->userSessionRepository->countUsersSince($dateTime);
        $amountOfGuests = $this->userSessionRepository->countGuestsSince($dateTime);

        return $this->renderView('@ZikulaUsersModule/Block/online.html.twig', [
            'registerallowed' => $this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_ENABLED),
            'usercount' => $amountOfUsers,
            'guestcount' => $amountOfGuests,
            'since' => $dateTime
        ]);
    }

    /**
     * @required
     */
    public function setVariableApi(VariableApiInterface $variableApi): void
    {
        $this->variableApi = $variableApi;
    }

    /**
     * @required
     */
    public function setUserSessionRepository(UserSessionRepositoryInterface $userSessionRepository): void
    {
        $this->userSessionRepository = $userSessionRepository;
    }
}
