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

namespace Zikula\AdminBundle\Helper;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\AdminBundle\Entity\AdminCategoryEntity;

/**
 * Helper function for non-dynamic admin categories.
 *
 * TODO think about using Categories bundle.
 */
class AdminCategoryHelper
{
    public function __construct(private readonly RequestStack $requestStack, private readonly TranslatorInterface $translator)
    {
    }

    public function getCategories(): array
    {
        $categoryData = [
            [
                'name' => $this->translator->trans('System'),
                'slug' => 'system',
                'description' => $this->translator->trans('Core modules at the heart of operation of the site.'),
                'icon' => 'fas fa-cogs',
                'sortOrder' => 0,
                'default' => false,
            ],
            [
                'name' => $this->translator->trans('Layout'),
                'slug' => 'layout',
                'description' => $this->translator->trans("Layout modules for controlling the site's look and feel."),
                'icon' => 'fas fa-palette',
                'sortOrder' => 1,
                'default' => false,
            ],
            [
                'name' => $this->translator->trans('Users'),
                'slug' => 'users',
                'description' => $this->translator->trans('Modules for controlling user membership, access rights and profiles.'),
                'icon' => 'fas fa-users-cog',
                'sortOrder' => 2,
                'default' => false,
            ],
            [
                'name' => $this->translator->trans('Content'),
                'slug' => 'content',
                'description' => $this->translator->trans('Modules for providing content to your users.'),
                'icon' => 'fas fa-file-contract',
                'sortOrder' => 3,
                'default' => true,
            ],
            [
                'name' => $this->translator->trans('Uncategorized'),
                'slug' => 'uncategorized',
                'description' => $this->translator->trans('Newly-installed or uncategorized modules.'),
                'icon' => 'fas fa-cubes',
                'sortOrder' => 4,
                'default' => false,
            ],
            [
                'name' => $this->translator->trans('Security'),
                'slug' => 'security',
                'description' => $this->translator->trans('Modules for managing the site\'s security.'),
                'icon' => 'fas fa-shield-alt',
                'sortOrder' => 5,
                'default' => false,
            ]
        ];

        $result = [];
        foreach ($categoryData as $row) {
            $result[] = (new AdminCategoryEntity())->setName($row['name'])->setSlug($row['slug'])->setDescription($row['description'])->setIcon($row['icon'])->setSortOrder($row['sortOrder'])->setDefault($row['default']);
        }

        return $result;
    }

    public function getCurrentCategory(): AdminCategoryEntity
    {
        $mainRequest = $this->requestStack->getMainRequest();
        $slug = $mainRequest->attributes->get('acslug', null);

        foreach ($this->getCategories() as $category) {
            if ($slug === $category->getSlug()) {
                return $category;
            }
        }

        return $this->getDefaultCategory();
    }

    public function getDefaultCategory(): AdminCategoryEntity
    {
        foreach ($this->getCategories() as $category) {
            if ($category->isDefault()) {
                return $category;
            }
        }

        throw new \InvalidArgumentException('No default category defined.');
    }

    public function getBundleAssignments(AdminCategoryEntity $category): array
    {
        return match ($category->getSlug()) {
            'system' => ['ZikulaExtensionsBundle', 'ZikulaRoutesBundle', 'ZikulaSettingsBundle'],
            'layout' => ['ZikulaAdminBundle', 'ZikulaDefaultThemeBundle', 'ZikulaMenuBundle', 'ZikulaThemeBundle'],
            'users' => ['ZikulaGroupsBundle', 'ZikulaLegalBundle', 'ZikulaPermissionsBundle', 'ZikulaProfileBundle', 'ZikulaUsersBundle', 'ZikulaZAuthBundle'],
            'content' => ['ZikulaStaticContentBundle'],
            'uncategorized' => [],
            'security' => ['ZikulaSecurityCenterBundle'],
        };
    }
}
