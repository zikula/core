<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\KernelInterface;
use Zikula\ThemeModule\Entity\ThemeEntity;

class ThemeEntityRepository extends EntityRepository
{
    const STATE_ALL = 0;
    const STATE_ACTIVE = 1;
    const STATE_INACTIVE = 2;

    const TYPE_ALL = 0;
    const TYPE_XANTHIA3 = 3;

    const FILTER_ALL = 0;
    const FILTER_USER = 1;
    const FILTER_SYSTEM = 2;
    const FILTER_ADMIN = 3;

    private $filteredGetCache;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @return KernelInterface
     */
    private function getKernel()
    {
        if (!$this->kernel instanceof KernelInterface) {
            $message = __('The "kernel" attribute is NULL. ')
                . __('Did you retrieved this repository using "$doctrine->getRepository()"? ')
                . __('If so, retrieve it instead directly from the container');
            throw new \LogicException($message);
        }

        return $this->kernel;
    }

    public function get($filter = self::FILTER_ALL, $state = self::STATE_ACTIVE, $type = self::TYPE_ALL)
    {
        $key = md5((string)$filter . (string)$state . (string)$type);

        if (empty($this->filteredGetCache[$key])) {
            $qb = $this->getEntityManager()->createQueryBuilder()
                ->select('t')
                ->from('ZikulaThemeModule:ThemeEntity', 't');

            if ($state != self::STATE_ALL) {
                $qb->andWhere('t.state = :state')
                    ->setParameter('state', $state);
            }
            if ($type != self::TYPE_ALL) {
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
            foreach ($result as $theme) {
                $this->filteredGetCache[$key][$theme->getDirectory()] = $theme->toArray();
                $kernel = $this->getKernel(); // allow to throw exception outside the try/catch block
                try {
                    $themeBundle = $kernel->getTheme($theme['name']);
                } catch (\Exception $e) {
                    $themeBundle = null;
                }
                $this->filteredGetCache[$key][$theme['directory']]['isTwigBased'] = isset($themeBundle) ? $themeBundle->isTwigBased() : false;
                $this->filteredGetCache[$key][$theme['directory']]['vars'] = isset($themeBundle) ? $themeBundle->getThemeVars() : false;
            }

            if (!$this->filteredGetCache[$key]) {
                return false;
            }
        }

        return $this->filteredGetCache[$key];
    }

    public function removeAndFlush($entity)
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }

    public function persistAndFlush($entity)
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }
}
