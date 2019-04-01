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

namespace Zikula\ThemeModule\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Exception;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ThemeModule\Entity\ThemeEntity;

class ThemeEntityRepository extends ServiceEntityRepository
{
    public const STATE_ALL = 0;

    public const STATE_ACTIVE = 1;

    public const STATE_INACTIVE = 2;

    public const TYPE_ALL = 0;

    public const TYPE_XANTHIA3 = 3;

    public const FILTER_ALL = 0;

    public const FILTER_USER = 1;

    public const FILTER_SYSTEM = 2;

    public const FILTER_ADMIN = 3;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThemeEntity::class);
    }

    /**
     * @return array|bool
     */
    public function get(
        int $filter = self::FILTER_ALL,
        int $state = self::STATE_ACTIVE,
        int $type = self::TYPE_ALL
    ) {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('t')
            ->from('ZikulaThemeModule:ThemeEntity', 't');

        if (self::STATE_ALL !== $state) {
            $qb->andWhere('t.state = :state')
                ->setParameter('state', $state);
        }
        if (self::TYPE_ALL !== $type) {
            $qb->andWhere('t.type = :type')
                ->setParameter('type', $type);
        }
        switch ($filter) {
            case self::FILTER_USER:
                $qb->andWhere('t.user = 1');
                break;
            case self::FILTER_SYSTEM:
                $qb->andWhere('t.system = 1');
                break;
            case self::FILTER_ADMIN:
                $qb->andWhere('t.admin = 1');
                break;
        }

        $qb->orderBy('t.displayname', 'ASC');
        $query = $qb->getQuery();

        /** @var $result ThemeEntity[] */
        $result = $query->getResult();
        $themesArray = [];
        foreach ($result as $theme) {
            $themesArray[$theme->getName()] = $theme->toArray();
            $kernel = $this->getKernel(); // allow to throw exception outside the try/catch block
            try {
                $themeName = (string)$theme['name'];
                $themeBundle = $kernel->getTheme($themeName);
            } catch (Exception $exception) {
                $themeBundle = null;
            }
            $themesArray[$theme['name']]['vars'] = isset($themeBundle) ? $themeBundle->getThemeVars() : false;
        }

        return !empty($themesArray) ? $themesArray : false;
    }

    public function removeAndFlush(ThemeEntity $entity): void
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }

    public function persistAndFlush(ThemeEntity $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    /**
     * @required
     */
    public function setKernel(ZikulaHttpKernelInterface $kernel): void
    {
        $this->kernel = $kernel;
    }

    private function getKernel(): ZikulaHttpKernelInterface
    {
        return $this->kernel;
    }
}
