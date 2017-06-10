<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormInterface;
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

    /**
     * @param UserEntity $userEntity
     * @param FormInterface $form
     */
    public function __construct(UserEntity $userEntity, FormInterface $form)
    {
        $this->userEntity = $userEntity;
        $this->form = $form;
    }

    /**
     * @param null $prefix
     * @return array
     */
    public function getFormData($prefix = null)
    {
        if (isset($prefix)) {
            return $this->form->get($prefix)->getData();
        }

        return $this->form->getData();
    }

    /**
     * @return UserEntity
     */
    public function getUserEntity()
    {
        return $this->userEntity;
    }
}
