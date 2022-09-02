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

namespace Zikula\UsersBundle\Event;

use Symfony\Component\Form\FormInterface;
use Zikula\Bundle\FormExtensionBundle\Event\FormPostValidatedEvent;
use Zikula\UsersBundle\Entity\User;

class UserFormPostValidatedEvent extends FormPostValidatedEvent
{
    private User $user;

    public function __construct(FormInterface $form, User $user)
    {
        parent::__construct($form);
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
