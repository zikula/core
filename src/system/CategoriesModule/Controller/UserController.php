<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Zikula\Core\Controller\AbstractController;

/**
 * @deprecated
 * User controllers for the categories module.
 */
class UserController extends AbstractController
{
    /**
     * @Route("")
     * @Route("/edit")
     * @Route("/edituser")
     * @Route("/update")
     * @Route("/refer")
     * @Route("/usercategories")
     * @Route("/delete")
     * @Route("/move/{cid}/{dr}/{direction}", requirements={"cid" = "^[1-9]\d*$", "dr" = "^[1-9]\d*$", "direction" = "up|down"}, defaults={"cid" = 1, "dr" = 1, "direction" = "up"})
     * @Route("/resequence/{dr}", requirements={"dr" = "^[1-9]\d*$"}, defaults={"dr" = 1})
     * @Route("/usercategoryname")
     */
    public function __call($method, $arguments)
    {
        @trigger_error('The User Categories feature has been removed.', E_USER_DEPRECATED);

        return $this->redirectToRoute('home');
    }
}
