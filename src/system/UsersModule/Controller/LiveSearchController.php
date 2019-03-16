<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\UsersModule\Collector\ProfileModuleCollector;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;

/**
 * @Route("/livesearch")
 */
class LiveSearchController extends AbstractController
{
    /**
     * Retrieves a list of users for a given search term (fragment).
     *
     * @Route("/getUsers", methods = {"GET"}, options={"expose"=true})
     *
     * @param Request $request Current request instance
     * @param UserRepositoryInterface $userRepository
     * @param ProfileModuleCollector $profileModuleCollector
     *
     * @return JsonResponse
     */
    public function getUsersAction(
        Request $request,
        UserRepositoryInterface $userRepository,
        ProfileModuleCollector $profileModuleCollector
    ) {
        if (!$this->hasPermission('ZikulaUsersModule::LiveSearch', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $fragment = $request->query->get('fragment', '');
        $results = $userRepository->searchActiveUser(['operator' => 'like', 'operand' => '%' . $fragment . '%'], 50);

        $profileModule = $profileModuleCollector->getSelected();

        $resultItems = [];
        if (count($results) > 0) {
            foreach ($results as $result) {
                $resultItems[] = [
                    'uid' => $result->getUid(),
                    'uname' => $result->getUname(),
                    'avatar' => $profileModule->getAvatar($result->getUid(), ['rating' => 'g'])
                ];
            }
        }

        // return response
        return new JsonResponse($resultItems);
    }
}
