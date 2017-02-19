<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\Controller\AbstractController;

/**
 * User controllers for the search module
 * @deprecated remove at Core-2.0
 */
class UserController extends AbstractController
{
    /**
     * Main user function
     *
     * @return RedirectResponse
     */
    public function mainAction()
    {
        return $this->indexAction();
    }

    /**
     * Main user function
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        @trigger_error('The zikulasearchmodule_user_index route is deprecated. please use zikulasearchmodule_search_execute instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulasearchmodule_search_execute');
    }

    /**
     * @Route("/redirect-to-search")
     */
    public function formAction()
    {
        @trigger_error('The zikulasearchmodule_user_form route is deprecated. please use zikulasearchmodule_search_execute instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulasearchmodule_search_execute');
    }

    /**
     * @Route("/results/{page}", requirements={"page"="\d+"})
     */
    public function searchAction($page = -1)
    {
        @trigger_error('The zikulasearchmodule_user_form route is deprecated. please use zikulasearchmodule_search_execute instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulasearchmodule_search_execute', ['page' => $page]);
    }

    /**
     * @Route("/recent-searches")
     */
    public function recentAction(Request $request)
    {
        @trigger_error('The zikulasearchmodule_user_recent route is deprecated. please use zikulasearchmodule_search_recent instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulasearchmodule_search_recent');
    }

    /**
     * @Route("/opensearch", options={"i18n"=false})
     */
    public function opensearchAction()
    {
        @trigger_error('The zikulasearchmodule_user_opensearch route is deprecated. please use zikulasearchmodule_search_opensearch instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulasearchmodule_search_opensearch');
    }
}
