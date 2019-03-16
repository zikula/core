<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Block;

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

    /**
     * @param array $properties
     * @return string
     */
    public function display(array $properties)
    {
        if (!$this->hasPermission('Onlineblock::', $properties['bid'] . '::', ACCESS_READ)) {
            return '';
        }

        $inactiveLimit = $this->variableApi->getSystemVar('secinactivemins');
        $dateTime = new \DateTime();
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
     * @param VariableApiInterface $variableApi
     */
    public function setVariableApi(VariableApiInterface $variableApi)
    {
        $this->variableApi = $variableApi;
    }

    /**
     * @required
     * @param UserSessionRepositoryInterface $userSessionRepository
     */
    public function setUserSessionRepository(UserSessionRepositoryInterface $userSessionRepository)
    {
        $this->userSessionRepository = $userSessionRepository;
    }
}
