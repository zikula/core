<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Event;

use Zikula\GroupsModule\Entity\GroupApplicationEntity;

class GroupApplicationPostProcessedEvent extends GroupApplicationEntityEvent
{
    private $message;

    public function __construct(GroupApplicationEntity $groupApplicationEntity, string $message)
    {
        parent::__construct($groupApplicationEntity);
        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
