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

namespace Zikula\UsersModule\Twig\Extension;

use Doctrine\Common\Collections\Criteria;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\SecurityCenterModule\Constant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserSessionRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;

class OnlineExtension extends AbstractExtension
{
    /**
     * @var UserSessionRepositoryInterface
     */
    private $sessionRepository;

    /**
     * @var string
     */
    private $sessionStorageInFile;

    /**
     * OnlineExtension constructor.
     *
     * @param UserSessionRepositoryInterface $sessionRepository
     * @param VariableApiInterface $variableApi
     */
    public function __construct(
        UserSessionRepositoryInterface $sessionRepository,
        VariableApiInterface $variableApi
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->sessionStorageInFile = $variableApi->getSystemVar('sessionstoretofile', 1);
    }

    public function getFilters()
    {
        return [
            new TwigFilter('onlineSince', [$this, 'onlineSince']),
        ];
    }

    /**
     * @param UserEntity|null $userEntity
     * @param int $minutes
     * @return bool|void
     */
    public function onlineSince(UserEntity $userEntity = null, $minutes = 10)
    {
        if (empty($userEntity)) {
            return;
        }
        if (Constant::SESSION_STORAGE_FILE === $this->sessionStorageInFile) {
            return;
        }

        $since = new \DateTime();
        $since->modify("-${minutes} minutes");
        $c = Criteria::create()
            ->where(Criteria::expr()->eq('uid', $userEntity->getUid()))
            ->andWhere(Criteria::expr()->gt('lastused', $since));
        $online = $this->sessionRepository->matching($c)->count();

        return (bool)$online;
    }
}
