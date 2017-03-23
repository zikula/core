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
use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\Controller\AbstractController;

/**
 * @deprecated
 * User controllers for the categories module
 */
class UserController extends AbstractController
{
    /**
     * @Route("")
     */
    public function indexAction(Request $request)
    {
        return $this->setResponse();
    }

    /**
     * Route not needed here because method is legacy-only
     */
    public function mainAction()
    {
        return $this->setResponse();
    }

    /**
     * @Route("/edit")
     */
    public function editAction(Request $request)
    {
        return $this->setResponse();
    }

    /**
     * @Route("/edituser")
     */
    public function edituserAction(Request $request)
    {
        return $this->setResponse();
    }

    /**
     * @Route("/update")
     */
    public function updateAction(Request $request)
    {
        return $this->setResponse();
    }

    /**
     * @Route("/refer")
     */
    public function referBackAction(Request $request)
    {
        return $this->setResponse();
    }

    /**
     * @Route("/usercategories")
     */
    public function getusercategoriesAction()
    {
        return $this->setResponse();
    }

    /**
     * @Route("/delete")
     */
    public function deleteAction(Request $request)
    {
        return $this->setResponse();
    }

    /**
     * @Route("/move/{cid}/{dr}/{direction}", requirements={"cid" = "^[1-9]\d*$", "dr" = "^[1-9]\d*$", "direction" = "up|down"})
     */
    public function moveFieldAction(Request $request, $cid, $dr, $direction = null)
    {
        return $this->setResponse();
    }

    /**
     * @Route("/resequence/{dr}", requirements={"dr" = "^[1-9]\d*$"})
     */
    public function resequenceAction(Request $request, $dr)
    {
        return $this->setResponse();
    }

    /**
     * @Route("/usercategoryname")
     */
    public function getusercategorynameAction()
    {
        return $this->setResponse();
    }

    private function setResponse()
    {
        @trigger_error('The User Categories feature has been removed.', E_USER_DEPRECATED);
        $this->addFlash('info', $this->__('User Categories feature has been removed.'));

        return $this->redirectToRoute('home');
    }
}
