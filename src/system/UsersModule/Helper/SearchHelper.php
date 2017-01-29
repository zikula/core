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

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\SearchModule\Entity\SearchResultEntity;
use Zikula\SearchModule\SearchableInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;

class SearchHelper implements SearchableInterface
{
    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * @var EngineInterface
     */
    private $templateEngine;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * SearchHelper constructor.
     * @param PermissionApi $permissionApi
     * @param EngineInterface $templateEngine
     * @param SessionInterface $session
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        PermissionApi $permissionApi,
        EngineInterface $templateEngine,
        SessionInterface $session,
        UserRepositoryInterface $userRepository
    ) {
        $this->permissionApi = $permissionApi;
        $this->templateEngine = $templateEngine;
        $this->session = $session;
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions($active, $modVars = null)
    {
        $options = '';
        if ($this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_READ)) {
            $options = $this->templateEngine->renderResponse('@ZikulaUsersModule/Search/options.html.twig', ['active' => $active])->getContent();
        }

        return $options;
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
            if ($user->getUid() != 1 && $this->permissionApi->hasPermission('ZikulaUsersModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_READ)) {
                $result = new SearchResultEntity();
                $result->setTitle($user->getUname())
                    ->setModule('ZikulaUsersModule')
                    ->setCreated($user->getUser_Regdate())
                    ->setSesid($this->session->getId());
                $results[] = $result;
            }
        }

        return $results;
    }

    public function getErrors()
    {
        return [];
    }
}
