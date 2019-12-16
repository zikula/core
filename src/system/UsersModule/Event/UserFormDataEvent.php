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

namespace Zikula\UsersModule\Event;

use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Zikula\UsersModule\Entity\UserEntity;

class UserFormDataEvent extends Event
{
    /**
     * @var FormInterface
     */
    private $form;

    /**
     * @var UserEntity
     */
    private $userEntity;

    public function __construct(UserEntity $userEntity, FormInterface $form)
    {
        $this->userEntity = $userEntity;
        $this->form = $form;
    }

    /**
     * @return mixed
     */
    public function getFormData(string $prefix = null)
    {
        if (isset($prefix)) {
            return $this->form->get($prefix)->getData();
        }

        return $this->form->getData();
    }

    public function getUserEntity(): UserEntity
    {
        return $this->userEntity;
    }
}
