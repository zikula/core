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

namespace Zikula\UsersBundle\Twig\Runtime;

use DateTime;
use Doctrine\Common\Collections\Criteria;
use Twig\Extension\RuntimeExtensionInterface;
use Zikula\ExtensionsBundle\Api\ApiInterface\VariableApiInterface;
use Zikula\SecurityCenterBundle\Constant;
use Zikula\UsersBundle\Entity\UserEntity;
use Zikula\UsersBundle\Repository\UserSessionRepositoryInterface;

class OnlineRuntime implements RuntimeExtensionInterface
{
    private string $sessionStorageInFile;

    public function __construct(
        private readonly UserSessionRepositoryInterface $sessionRepository,
        VariableApiInterface $variableApi
    ) {
        $this->sessionStorageInFile = $variableApi->getSystemVar('sessionstoretofile', 1);
    }

    /**
     * @return bool|void
     */
    public function onlineSince(UserEntity $userEntity = null, int $minutes = 10)
    {
        if (null === $userEntity) {
            return;
        }
        if (Constant::SESSION_STORAGE_FILE === $this->sessionStorageInFile) {
            return;
        }

        $since = new DateTime();
        $since->modify("-${minutes} minutes");
        $c = Criteria::create()
            ->where(Criteria::expr()->eq('uid', $userEntity->getUid()))
            ->andWhere(Criteria::expr()->gt('lastused', $since));
        $online = $this->sessionRepository->matching($c)->count();

        return (bool) $online;
    }
}
