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

namespace Zikula\UsersBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Zikula\UsersBundle\ProfileBundle\ProfileBundleCollector;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;

#[Route('/users/livesearch')]
class LiveSearchController extends AbstractController
{
    /**
     * Retrieves a list of users for a given search term (fragment).
     */
    #[Route('/getUsers', name: 'zikulausersbundle_livesearch_getusers', methods: ['GET'], options: ['expose' => true])]
    #[IsGranted('ROLE_EDITOR')]
    public function getUsers(
        Request $request,
        UserRepositoryInterface $userRepository,
        ProfileBundleCollector $profileBundleCollector
    ): JsonResponse {
        $fragment = $request->query->get('fragment', '');
        $results = $userRepository->searchActiveUser(['operator' => 'like', 'operand' => '%' . $fragment . '%']);

        $profileBundle = $profileBundleCollector->getSelected();

        $resultItems = [];
        if (count($results) > 0) {
            foreach ($results as $result) {
                $avatar = $profileBundle->getAvatar($result->getUid(), ['rating' => 'g']);
                if (!$avatar) {
                    $avatar = '<img src="' . $request->getSchemeAndHttpHost() . $request->getBasePath() . '/bundles/zikulausers/images/user.png" alt="user" />';
                }
                $resultItems[] = [
                    'uid' => $result->getUid(),
                    'uname' => $result->getUname(),
                    'avatar' => $avatar,
                ];
            }
        }

        return new JsonResponse($resultItems);
    }
}
