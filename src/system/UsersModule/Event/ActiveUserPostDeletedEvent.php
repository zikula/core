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

namespace Zikula\UsersModule\Event;

use Zikula\UsersModule\Entity\UserEntity;

/**
 * Occurs after the deletion of a user account.
 * This is a storage-level event, not a UI event. It should not be used for UI-level actions such as redirects.
 */
class ActiveUserPostDeletedEvent extends UserEntityEvent
{
    /**
     * Has the user been fully deleted (true) or converted to ghost (false)
     * In general, listening extensions should respond the same in both cases - removing all private user data.
     * When converted to ghost, it is acceptable to retain the UID as the source of data if needed, as the UID
     * will continue to remain valid and reference a UserEntity record.
     * @var bool
     */
    private $fullDeletion;

    public function __construct(?UserEntity $user, bool $fullDeletion = false)
    {
        parent::__construct($user);
        $this->fullDeletion = $fullDeletion;
    }

    public function isFullDeletion(): bool
    {
        return $this->fullDeletion;
    }
}
