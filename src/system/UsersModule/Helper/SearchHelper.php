<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Helper;

use ModUtil;
use SecurityUtil;
use System;
use ZLanguage;
use Zikula\Core\ModUrl;
use Zikula\SearchModule\AbstractSearchable;
use Zikula\UsersModule\Constant as UsersConstant;

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

        if (SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_READ)) {
            $options = $this->view->assign('active', $active)
                ->fetch('Search/options.tpl');
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
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_READ)) {
            return array();
        }

        // decide if we have to search the DUDs from the Profile module
        $profileModule = System::getVar('profilemodule', '');
        $useProfileMod = (!empty($profileModule) && ModUtil::available($profileModule));

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('u')
            ->from('ZikulaUsersModule:UserEntity', 'u')
            ->andWhere('u.activated <> :activated')
            ->setParameter('activated', UsersConstant::ACTIVATED_PENDING_REG);
        $where = $this->formatWhere($qb, $words, array('u.uname'), $searchType);
        $qb->andWhere($where);
        if ($useProfileMod) {
            $uids = ModUtil::apiFunc($profileModule, 'user', 'searchDynadata', array('dynadata' => array('all' => implode(' ', $words))));
            if (is_array($uids) && !empty($uids)) {
                $qb->orWhere($qb->expr()->in('u.uid', $uids));
            }
        }
        $users = $qb->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $sessionId = session_id();

        $results = array();
        foreach ($users as $user) {
            if ($user['uid'] != 1 && SecurityUtil::checkPermission($this->name . '::', "$user[uname]::$user[uid]", ACCESS_READ)) {
                if ($useProfileMod) {
                    $text = $this->__("Click the user's name to view his/her complete profile.");
                    $url = new ModUrl($profileModule, 'user', 'view', ZLanguage::getLanguageCode(), array('uid' => $user['uid']));
                } else {
                    $text = null;
                    $url = null;
                }
                $results[] = array(
                    'title' => $user['uname'],
                    'text' => $text,
                    'module' => $this->name,
                    'created' => $user['user_regdate'],
                    'sesid' => $sessionId,
                    'url' => $url
                );
            }
        }

        return $results;
    }
}
