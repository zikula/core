<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\SearchModule\Entity\RepositoryInterface\SearchStatRepositoryInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;

class LinkContainer implements LinkContainerInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var CurrentUserApiInterface
     */
    private $currentUserApi;

    /**
     * @var SearchStatRepositoryInterface
     */
    private $statRepo;

    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        PermissionApiInterface $permissionApi,
        CurrentUserApiInterface $currentUserApi,
        SearchStatRepositoryInterface $searchStatRepository
    ) {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
        $this->currentUserApi = $currentUserApi;
        $this->statRepo = $searchStatRepository;
    }

    public function getLinks(string $type = LinkContainerInterface::TYPE_ADMIN): array
    {
        if (LinkContainerInterface::TYPE_ADMIN === $type) {
            return $this->getAdmin();
        }
        if (LinkContainerInterface::TYPE_USER === $type) {
            return $this->getUser();
        }

        return [];
    }

    /**
     * Get the admin links for this extension.
     */
    private function getAdmin(): array
    {
        $links = [];

        $links[] = [
            'url' => $this->router->generate('zikulasearchmodule_search_execute'),
            'text' => $this->translator->trans('Frontend'),
            'icon' => 'search'
        ];

        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $links[] = [
                'url' => $this->router->generate('zikulasearchmodule_config_config'),
                'text' => $this->translator->trans('Settings'),
                'icon' => 'wrench'
            ];
        }

        return $links;
    }

    /**
     * Get the user links for this extension.
     */
    private function getUser(): array
    {
        $links = [];

        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $links[] = [
                'url' => $this->router->generate('zikulasearchmodule_config_config'),
                'text' => $this->translator->trans('Backend'),
                'icon' => 'wrench'
            ];
        }

        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_READ)) {
            $links[] = [
                'url' => $this->router->generate('zikulasearchmodule_search_execute'),
                'text' => $this->translator->trans('New search'),
                'icon' => 'search'
            ];
            if ($this->currentUserApi->isLoggedIn() && $this->statRepo->countStats() > 0) {
                $links[] = [
                    'url' => $this->router->generate('zikulasearchmodule_search_recent'),
                    'text' => $this->translator->trans('Recent searches list'),
                    'icon' => 'list'
                ];
            }
        }

        return $links;
    }

    public function getBundleName(): string
    {
        return 'ZikulaSearchModule';
    }
}
