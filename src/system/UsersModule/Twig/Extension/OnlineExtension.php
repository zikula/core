<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Twig\Extension;

use Doctrine\Common\Collections\Criteria;
use Zikula\SecurityCenterModule\Constant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserSessionRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;

class OnlineExtension extends \Twig_Extension
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
     * @param UserSessionRepositoryInterface $sessionRepository
     * @param string $sessionStorage
     */
    public function __construct(
        UserSessionRepositoryInterface $sessionRepository,
        $sessionStorage
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->sessionStorageInFile = $sessionStorage;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('onlineSince', [$this, 'onlineSince']),
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
        if ($this->sessionStorageInFile == Constant::SESSION_STORAGE_FILE) {
            return;
        }

        $since = new \DateTime();
        $since->modify("-$minutes minutes");
        $c = Criteria::create()
            ->where(Criteria::expr()->eq('uid', $userEntity->getUid()))
            ->andWhere(Criteria::expr()->gt('lastused', $since));
        $online = $this->sessionRepository->matching($c)->count();

        return (bool) $online;
    }
}
