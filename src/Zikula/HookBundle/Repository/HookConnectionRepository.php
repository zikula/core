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
    private $data;

    public function __construct(ManagerRegistry $registry)
    {
//        parent::__construct($registry, HookRuntimeEntity::class);
        $this->data = [
            new Connection('App\\HookEvent\\AppDisplayHookEvent', 'App\\HookListener\\AppDisplayHookEventListener', 5),
            new Connection('App\\HookEvent\\AppFilterHookEvent', 'App\\HookListener\\AppFilterHookEventListener', 2),
            new Connection('App\\HookEvent\\AppPostValidationFormHookEvent', 'App\\HookListener\\AppPostValidationFormHookEventListener'),
//            new Connection('App\\HookEvent\\AppPreHandleRequestFormHookEvent', 'App\\HookListener\\AppPreHandleRequestFormHookEventListener')
        ];
    }

    public function isConnected(string $event, string $listener): ?Connection
    {
        foreach ($this->data as $connection) {
            if ($event === $connection->getEvent() && $listener === $connection->getListener()) {
                return $connection;
            }
        }

        return null;
    }

    public function getAll(): array
    {
        return $this->data;
    }
}
