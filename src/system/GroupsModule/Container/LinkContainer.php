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

namespace Zikula\GroupsModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\GroupsModule\Entity\Repository\GroupApplicationRepository;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

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
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var GroupApplicationRepository
     */
    private $groupApplicationRepository;

    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        PermissionApiInterface $permissionApi,
        GroupRepositoryInterface $groupRepository,
        GroupApplicationRepository $groupApplicationRepository
    ) {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
        $this->groupRepository = $groupRepository;
        $this->groupApplicationRepository = $groupApplicationRepository;
    }

    public function getLinks(string $type = LinkContainerInterface::TYPE_ADMIN): array
    {
        if (LinkContainerInterface::TYPE_ADMIN === $type) {
            return $this->getAdmin();
        }
        if (LinkContainerInterface::TYPE_USER === $type) {
            return $this->getUser();
        }
        if (LinkContainerInterface::TYPE_ACCOUNT === $type) {
            return $this->getAccount();
        }

        return [];
    }

    /**
     * Get the admin links for this extension.
     */
    private function getAdmin(): array
    {
        $links = [];

        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_EDIT)) {
            $links[] = [
                'url' => $this->router->generate('zikulagroupsmodule_group_adminlist'),
                'text' => $this->translator->__('Groups list'),
                'icon' => 'list'
            ];
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADD)) {
            $links[] = [
                'url' => $this->router->generate('zikulagroupsmodule_group_create'),
                'text' => $this->translator->__('New group'),
                'icon' => 'plus'
            ];
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $links[] = [
                'url' => $this->router->generate('zikulagroupsmodule_config_config'),
                'text' => $this->translator->__('Settings'),
                'icon' => 'wrench'
            ];
        }
        $apps = $this->groupApplicationRepository->findAll();
        $appCount = count($apps);
        if (($appCount > 0) && $this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_EDIT)) {
            $links[] = [
                'url' => $this->router->generate('zikulagroupsmodule_group_adminlist') . '#applications',
                'text' => $this->translator->__f('%n Pending applications', ['%n' => $appCount]),
                'icon' => 'exclamation-triangle'
            ];
        }

        return $links;
    }

    /**
     * Get the account links for this extension.
     */
    private function getUser(): array
    {
        $links = [];
        $links[] = [
            'url' => $this->router->generate('zikulagroupsmodule_group_list'),
            'text' => $this->translator->__('Group list'),
            'icon' => 'group'
        ];
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_EDIT)) {
            $links[] = [
                'url' => $this->router->generate('zikulagroupsmodule_group_adminlist'),
                'text' => $this->translator->__('Groups admin'),
                'icon' => 'wrench'
            ];
        }

        return $links;
    }

    /**
     * Get the account links for this extension.
     */
    private function getAccount(): array
    {
        $links = [];

        // Check if there is at least one group to show
        $groups = $this->groupRepository->findAll();
        if (count($groups) > 0) {
            $links[] = [
                'url' => $this->router->generate('zikulagroupsmodule_group_list'),
                'text' => $this->translator->__('Groups manager'),
                'icon' => 'group'
            ];
        }

        return $links;
    }

    public function getBundleName(): string
    {
        return 'ZikulaGroupsModule';
    }
}
