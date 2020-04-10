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

namespace Zikula\UsersModule\Helper;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\SearchModule\Entity\SearchResultEntity;
use Zikula\SearchModule\SearchableInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;

class SearchHelper implements SearchableInterface
{
    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    public function __construct(
        PermissionApiInterface $permissionApi,
        RequestStack $requestStack,
        UserRepositoryInterface $userRepository
    ) {
        $this->permissionApi = $permissionApi;
        $this->requestStack = $requestStack;
        $this->userRepository = $userRepository;
    }

    public function amendForm(FormBuilderInterface $form): void
    {
        // not needed because `active` child object is already added and that is all that is needed.
    }

    public function getResults(array $words, string $searchType = 'AND', ?array $modVars = []): array
    {
        if (!$this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_READ)) {
            return [];
        }
        $users = $this->userRepository->getSearchResults($words);

        $request = $this->requestStack->getCurrentRequest();
        $sessionId = $request->hasSession() ? $request->getSession()->getId() : '';

        $results = [];
        foreach ($users as $user) {
            if (1 >= $user->getUid()
                || !$this->permissionApi->hasPermission('ZikulaUsersModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_READ)
            ) {
                continue;
            }
            $result = new SearchResultEntity();
            $result->setTitle($user->getUname())
                ->setModule($this->getBundleName())
                ->setCreated($user->getRegistrationDate())
                ->setSesid($sessionId)
            ;
            $results[] = $result;
        }

        return $results;
    }

    public function getErrors(): array
    {
        return [];
    }

    public function getBundleName(): string
    {
        return 'ZikulaUsersModule';
    }
}
