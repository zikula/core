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

namespace Zikula\ThemeBundle\Helper;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\ThemeBundle\Entity\AdminCategory;

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
                'description' => $this->translator->trans('Core bundles at the heart of operation of the site.'),
                'icon' => 'fas fa-cogs',
                'sortOrder' => 0,
                'default' => false,
            ],
            [
                'name' => $this->translator->trans('Users'),
                'slug' => 'users',
                'description' => $this->translator->trans('Bundles for controlling user membership, access rights and profiles.'),
                'icon' => 'fas fa-users-cog',
                'sortOrder' => 2,
                'default' => false,
            ],
            [
                'name' => $this->translator->trans('Content'),
                'slug' => 'content',
                'description' => $this->translator->trans('Bundles for providing content to your users.'),
                'icon' => 'fas fa-file-contract',
                'sortOrder' => 3,
                'default' => true,
            ],
        ];

        $result = [];
        foreach ($categoryData as $row) {
            $result[] = (new AdminCategory())->setName($row['name'])->setSlug($row['slug'])->setDescription($row['description'])->setIcon($row['icon'])->setSortOrder($row['sortOrder'])->setDefault($row['default']);
        }

        return $result;
    }

    public function getCurrentCategory(): AdminCategory
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

    public function getDefaultCategory(): AdminCategory
    {
        foreach ($this->getCategories() as $category) {
            if ($category->isDefault()) {
                return $category;
            }
        }

        throw new \InvalidArgumentException('No default category defined.');
    }

    public function getBundleAssignments(AdminCategory $category): array
    {
        return match ($category->getSlug()) {
            'system' => ['ZikulaThemeBundle'],
            'users' => ['ZikulaGroupsBundle', 'ZikulaLegalBundle', 'ZikulaProfileBundle', 'ZikulaUsersBundle'],
            'content' => ['ZikulaCategoriesBundle'],
        };
    }
}
