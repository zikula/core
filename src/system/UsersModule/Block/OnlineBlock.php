<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Block;

use BlockUtil;
use ModUtil;
use SecurityUtil;
use System;
use UserUtil;
use Zikula_View;

/**
 * A block that shows who is currently using the system.
 */
class OnlineBlock extends \Zikula_Controller_AbstractBlock
{
    /**
     * Initialise the block.
     *
     * Adds the blocks security schema to the PN environment.
     *
     * @return void
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Onlineblock::', 'Block ID::');
    }

    /**
     * Return the block info.
     *
     * @return array The blockinfo structure.
     */
    public function info()
    {
        return array(
            'module'         => $this->name,
            'text_type'      => $this->__("Who's on-line"),
            'text_type_long' => $this->__('On-line block'),
            'allow_multiple' => false,
            'form_content'   => false,
            'form_refresh'   => false,
            'show_preview'   => true,
        );
    }

    /**
     * Display the output of the online block.
     *
     * @param mixed[] $blockinfo {
     *      @type string $title   the title of the block
     *      @type int    $bid     the id of the block
     *      @type string $content the seralized block content array
     *                            }
     *
     * @todo Move sql queries to calls to relevant API's.
     *
     * @return string|void The rendered block output if the user has read permissions over the block, void otherwise
     */
    public function display($blockinfo)
    {
        if (!SecurityUtil::checkPermission('Onlineblock::', $blockinfo['bid'].'::', ACCESS_READ)) {
            return;
        }

        if ($this->view->getCaching()) {
            // Here we use the user id as the cache id since the block shows user based
            // information; username and number of private messages.
            $uid = UserUtil::getVar('uid');
            $cacheid = $blockinfo['bkey'].'/bid'.$blockinfo['bid'].'/'.($uid ? $uid : 'guest');
            // We use an individual cache with a lifetime specified on the block configuration.
            $this->view->setCaching(Zikula_View::CACHE_INDIVIDUAL)
//                       ->setCacheLifetime($blockinfo['refresh'])
                       ->setCacheId($cacheid);

            // check out if the contents are cached.
            // If this is the case, we do not need to make DB queries.
            if ($this->view->is_cached('Block/online.tpl')) {
                $blockinfo['content'] = $this->view->fetch('Block/online.tpl');

                return BlockUtil::themeBlock($blockinfo);
            }
        }

        $activetime = strftime('%Y-%m-%d %H:%M:%S', time() - (System::getVar('secinactivemins') * 60));

        $query = $this->entityManager->createQueryBuilder()
                      ->select('count(s.uid)')
                      ->from('ZikulaUsersModule:UserSessionEntity', 's')
                      ->where('s.lastused > :activetime')
                      ->setParameter('activetime', $activetime)
                      ->andWhere('s.uid <> 0')
                      ->getQuery();
        $numusers = (int)$query->getSingleScalarResult();

        $query = $this->entityManager->createQueryBuilder()
                      ->select('count(s.uid)')
                      ->from('ZikulaUsersModule:UserSessionEntity', 's')
                      ->where('s.lastused > :activetime')
                      ->setParameter('activetime', $activetime)
                      ->andWhere('s.uid = 0')
                      ->getQuery();
        $numguests = (int)$query->getSingleScalarResult();

        $msgmodule = System::getVar('messagemodule', '');

        if ($msgmodule && SecurityUtil::checkPermission($msgmodule.'::', '::', ACCESS_READ) && UserUtil::isLoggedIn()) {
            // check if message module is available and add the necessary info
            if (ModUtil::available($msgmodule)) {
                $this->view->assign('messages', ModUtil::apiFunc($msgmodule, 'user', 'getmessagecount'));
            } else {
                $this->view->assign('messages', array());
            }
        }

        $this->view->assign('registerallowed', $this->getVar('reg_allowreg'))
                   ->assign('userscount', $numusers)
                   ->assign('guestcount', $numguests)
                   ->assign('msgmodule', $msgmodule);

        $blockinfo['content'] = $this->view->fetch('Block/online.tpl');

        return BlockUtil::themeBlock($blockinfo);
    }
}
