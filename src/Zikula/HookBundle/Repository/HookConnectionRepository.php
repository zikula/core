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

namespace Zikula\Bundle\HookBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Zikula\Bundle\HookBundle\Hook\Connection;

class HookConnectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
//        parent::__construct($registry, HookRuntimeEntity::class);
    }

    public function getAll(): array
    {
        return [
            new Connection('App\\HookEvent\\AppDisplayHookEvent', 'App\\HookListener\\AppDisplayHookEventListener'),
            new Connection('App\\HookEvent\\AppFilterHookEvent', 'App\\HookListener\\AppFilterHookEventListener'),
            new Connection('App\\HookEvent\\AppPostValidationFormHookEvent', 'App\\HookListener\\AppPostValidationFormHookEventListener'),
            new Connection('App\\HookEvent\\AppPreHandleRequestFormHookEvent', 'App\\HookListener\\AppPreHandleRequestFormHookEventListener')
        ];
    }
}
