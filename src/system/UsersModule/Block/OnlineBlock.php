<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Block;

use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\UsersModule\Constant as UsersConstant;

/**
 * A block that shows who is currently using the system.
 */
class OnlineBlock extends AbstractBlockHandler
{
    /**
     * @param array $properties
     * @return string
     */
    public function display(array $properties)
    {
        if (!$this->hasPermission('Onlineblock::', $properties['bid'].'::', ACCESS_READ)) {
            return '';
        }
        $inactiveLimit = $this->get('zikula_extensions_module.api.variable')->get(VariableApi::CONFIG, 'secinactivemins');
        $dateTime = new \DateTime();
        $dateTime->modify('-' . $inactiveLimit . 'minutes');
        $numusers = $this->get('zikula_users_module.user_session_repository')->countUsersSince($dateTime);
        $numguests = $this->get('zikula_users_module.user_session_repository')->countGuestsSince($dateTime);

        $templateArgs = [
            'registerallowed' => $this->get('zikula_extensions_module.api.variable')->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_ENABLED),
            'usercount' => $numusers,
            'guestcount' => $numguests,
            'since' => $dateTime
        ];

        return $this->renderView('@ZikulaUsersModule/Block/online.html.twig', $templateArgs);
    }
}
