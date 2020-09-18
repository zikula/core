<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
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
     * @PermissionCheck({"$_zkModule::LiveSearch", "::", "edit"})
     */
    public function getUsers(
        Request $request,
        UserRepositoryInterface $userRepository,
        ProfileModuleCollector $profileModuleCollector
    ): JsonResponse {
        $fragment = $request->query->get('fragment', '');
        $results = $userRepository->searchActiveUser(['operator' => 'like', 'operand' => '%' . $fragment . '%']);

        $profileModule = $profileModuleCollector->getSelected();

        $resultItems = [];
        if (count($results) > 0) {
            foreach ($results as $result) {
                $avatar = $profileModule->getAvatar($result->getUid(), ['rating' => 'g']);
                if (!$avatar) {
                    $avatar = '<img src="' . $request->getSchemeAndHttpHost() . $request->getBasePath() . '/modules/zikulausers/images/user.png" alt="user" />';
                }
                $resultItems[] = [
                    'uid' => $result->getUid(),
                    'uname' => $result->getUname(),
                    'avatar' => $avatar
                ];
            }
        }

        // return response
        return new JsonResponse($resultItems);
    }
}
