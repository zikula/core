<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Helper;

use ModUtil;
use Zikula\Core\ModUrl;
use Zikula\SearchModule\AbstractSearchable;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserEntity;
use ZLanguage;

class SearchHelper extends AbstractSearchable
{
    /**
     * get the UI options for search form
     *
     * @param boolean $active
     * @param array|null $modVars
     * @return string
     */
    public function getOptions($active, $modVars = null)
    {
        $options = '';
        if ($this->container->get('zikula_permissions_module.api.permission')->hasPermission('ZikulaUsersModule::', '::', ACCESS_READ)) {
            $options = $this->getContainer()->get('templating')->renderResponse('@ZikulaUsersModule/Search/options.html.twig', ['active' => $active])->getContent();
        }

        return $options;
    }

    /**
     * Get the search results
     *
     * @param array $words array of words to search for
     * @param string $searchType AND|OR|EXACT
     * @param array|null $modVars module form vars passed though
     * @return array
     */
    public function getResults(array $words, $searchType = 'AND', $modVars = null)
    {
        $permissionApi = $this->container->get('zikula_permissions_module.api.permission');

        if (!$permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_READ)) {
            return [];
        }

        // decide if we have to search the DUDs from the Profile module
        $profileModule = $this->container->get('zikula_extensions_module.api.variable')->getSystemVar('profilemodule', '');
        $useProfileMod = !empty($profileModule) && ModUtil::available($profileModule); // @todo

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('u')
            ->from('ZikulaUsersModule:UserEntity', 'u')
            ->andWhere('u.activated <> :activated')
            ->setParameter('activated', UsersConstant::ACTIVATED_PENDING_REG);
        $where = $this->formatWhere($qb, $words, ['u.uname'], $searchType);
        $qb->andWhere($where);
        if ($useProfileMod) {
            $uids = ModUtil::apiFunc($profileModule, 'user', 'searchDynadata', ['dynadata' => ['all' => implode(' ', $words)]]);
            if (is_array($uids) && !empty($uids)) {
                $qb->orWhere($qb->expr()->in('u.uid', $uids));
            }
        }
        /** @var UserEntity[] $users */
        $users = $qb->getQuery()->getResult();

        $results = [];
        foreach ($users as $user) {
            if ($user->getUid() != 1 && $permissionApi->hasPermission('ZikulaUsersModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_READ)) {
                if ($useProfileMod) {
                    $text = $this->__("Click the user's name to view his/her complete profile.");
                    $url = new ModUrl($profileModule, 'user', 'view', ZLanguage::getLanguageCode(), ['uid' => $user->getUid()]); // @todo
                } else {
                    $text = null;
                    $url = null;
                }
                $results[] = [
                    'title' => $user->getUname(),
                    'text' => $text,
                    'module' => 'ZikulaUsersModule',
                    'created' => $user->getUser_Regdate(),
                    'sesid' => $this->container->get('session')->getId(),
                    'url' => $url
                ];
            }
        }

        return $results;
    }
}
