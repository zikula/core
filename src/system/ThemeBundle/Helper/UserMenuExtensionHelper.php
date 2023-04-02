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

use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use Symfony\Component\Security\Core\User\UserInterface;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuCollector;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuInterface;

class UserMenuExtensionHelper
{
    public function __construct(private readonly ExtensionMenuCollector $extensionMenuCollector)
    {
    }

    public function configureUserMenu(UserMenu $menu, UserInterface $user): UserMenu
    {
        return $menu
            // TODO full name (with fallback to username)
            // ->setName($user->getFullName())

            // TODO avatar
            // the default user avatar is a generic avatar icon
            // you can return an URL with the avatar image
            // ->setAvatarUrl($user->getProfileImageUrl())
            // use this method if you don't want to display the user image
            // ->displayUserAvatar(false)
            // you can also pass an email address to use gravatar's service
            ->setGravatarEmail($user->getEmailCanonical())

            // additional account menu items contributed by extensions
            ->addMenuItems(iterator_to_array($this->configureUserMenuItems()))
        ;
    }

    private function configureUserMenuItems(): iterable
    {
        $menuItemsByBundle = $this->extensionMenuCollector->getAllByContext(ExtensionMenuInterface::CONTEXT_ACCOUNT);
        foreach ($menuItemsByBundle as $bundleName => $extensionMenuItems) {
            $menuItems = is_array($extensionMenuItems) ? $extensionMenuItems : iterator_to_array($extensionMenuItems);
            foreach ($menuItems as $item) {
                yield $item;
            }
        }
    }
}
