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

namespace Zikula\UsersBundle\Bundle\Initializer;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Zikula\CoreBundle\Bundle\Initializer\BundleInitializerInterface;
use Zikula\UsersBundle\Entity\User;
use Zikula\UsersBundle\UsersConstant;

class UsersInitializer implements BundleInitializerInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function init(): void
    {
        $now = new DateTime();
        $then = new DateTime('1970-01-01 00:00:00');

        // Anonymous
        $user = (new User())->setId(UsersConstant::USER_ID_ANONYMOUS);
        $user->setUsername('guest');
        $user->setEmail('');
        $user->setActivated(UsersConstant::ACTIVATED_ACTIVE)
            ->setApprovedDate($then)
            ->setApprovedBy(UsersConstant::USER_ID_ADMIN)
            ->setRegistrationDate($then)
            ->setLastLogin($then);
        $this->entityManager->persist($user);

        // Admin
        $user = (new User())->setId(UsersConstant::USER_ID_ADMIN);
        $user->setUsername('admin');
        $user->setEmail('');
        $user->setActivated(UsersConstant::ACTIVATED_ACTIVE)
            ->setApprovedDate($now)
            ->setApprovedBy(UsersConstant::USER_ID_ADMIN)
            ->setRegistrationDate($now)
            ->setLastLogin($then);
        $this->entityManager->persist($user);

        $this->entityManager->flush();
    }
}
