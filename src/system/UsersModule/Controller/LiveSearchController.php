<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\Controller\AbstractController;
use Zikula\UsersModule\Constant as UsersConstant;

/**
 * @Route("/livesearch")
 */
class LiveSearchController extends AbstractController
{
    /**
     * Retrieves a list of users for a given search term (fragment).
     *
     * @Route("/getUsers", options={"expose"=true})
     * @Method("GET")
     *
     * @param Request $request Current request instance
     *
     * @return JsonResponse
     */ 
    public function getUsersAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaUsersModule::LiveSearch', '::', ACCESS_EDIT)) {
            return true;
        }

        $fragment = $request->query->get('fragment', '');
        $userRepository = $this->get('zikula_users_module.user_repository');
        $limit = 50;
        $filter = [
            'activated' => ['operator' => 'notIn', 'operand' => [
                UsersConstant::ACTIVATED_PENDING_REG,
                UsersConstant::ACTIVATED_PENDING_DELETE
            ]],
            'uname' => ['operator' => 'like', 'operand' => '%' . $fragment . '%']
        ];
        $results = $userRepository->query($filter, ['uname' => 'asc'], $limit);

        // load avatar plugin
        // @todo fix this as part of https://github.com/zikula-modules/Profile/issues/80
        include_once 'lib/legacy/viewplugins/function.useravatar.php';
        $view = \Zikula_View::getInstance('ZikulaUsersModule', false);

        $resultItems = [];
        if (count($results) > 0) {
            foreach ($results as $result) {
                $resultItems[] = [
                    'uid' => $result->getUid(),
                    'uname' => $result->getUname(),
                    'avatar' => smarty_function_useravatar(['uid' => $result->getUid(), 'rating' => 'g'], $view)
                ];
            }
        }

        // return response
        return new JsonResponse($resultItems);
    }
}
