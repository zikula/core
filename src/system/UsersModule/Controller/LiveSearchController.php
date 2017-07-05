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
        $results = $userRepository->searchActiveUser(['operator' => 'like', 'operand' => '%' . $fragment . '%'], 50);

        $profileModule = $this->get('zikula_users_module.internal.profile_module_collector')->getSelected();

        $resultItems = [];
        if (count($results) > 0) {
            foreach ($results as $result) {
                $resultItems[] = [
                    'uid' => $result->getUid(),
                    'uname' => $result->getUname(),
                    'avatar' => $profileModule->getAvatar(['uid' => $result->getUid(), ['rating' => 'g']])
                ];
            }
        }

        // return response
        return new JsonResponse($resultItems);
    }
}
