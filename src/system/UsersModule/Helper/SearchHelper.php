<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Helper;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zikula\Core\RouteUrl;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\SearchModule\Entity\SearchResultEntity;
use Zikula\SearchModule\SearchableInterface;
use Zikula\UsersModule\Collector\ProfileModuleCollector;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;

class SearchHelper implements SearchableInterface
{
    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var ProfileModuleCollector
     */
    private $profileModuleCollector;

    /**
     * SearchHelper constructor.
     * @param PermissionApiInterface $permissionApi
     * @param SessionInterface $session
     * @param UserRepositoryInterface $userRepository
     * @param ProfileModuleCollector $profileModuleCollector
     */
    public function __construct(
        PermissionApiInterface $permissionApi,
        SessionInterface $session,
        UserRepositoryInterface $userRepository,
        ProfileModuleCollector $profileModuleCollector
    ) {
        $this->permissionApi = $permissionApi;
        $this->session = $session;
        $this->userRepository = $userRepository;
        $this->profileModuleCollector = $profileModuleCollector;
    }

    /**
     * {@inheritdoc}
     */
    public function amendForm(FormBuilderInterface $form)
    {
        // not needed because `active` child object is already added and that is all that is needed.
    }

    /**
     * {@inheritdoc}
     */
    public function getResults(array $words, $searchType = 'AND', $modVars = null)
    {
        if (!$this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_READ)) {
            return [];
        }
        $users = $this->userRepository->getSearchResults($words);

        $results = [];
        foreach ($users as $user) {
            if ($user->getUid() == 1 || !$this->permissionApi->hasPermission('ZikulaUsersModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_READ)) {
                continue;
            }
            $userDisplayName = $user->getUname();
            $profileUrl = $this->profileModuleCollector->getSelected()->getProfileUrl($user->getUid());
            if ($profileUrl != '#') {
                $userDisplayName = $this->profileModuleCollector->getSelected()->getDisplayName($user->getUid());
            }

            $result = new SearchResultEntity();
            $result->setTitle($userDisplayName)
                ->setModule('ZikulaUsersModule')
                ->setCreated($user->getUser_Regdate())
                ->setSesid($this->session->getId());
            if ($profileUrl != '#') {
                $result->setUrl(new RouteUrl($profileUrl));
            }
            $results[] = $result;
        }

        return $results;
    }

    public function getErrors()
    {
        return [];
    }
}
