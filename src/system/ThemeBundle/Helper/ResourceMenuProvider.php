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

use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use function Symfony\Component\Translation\t;

class ResourceMenuProvider
{
    public static function getResources(): iterable
    {
        yield MenuItem::section(t('Resources'), 'fas fa-book');
        yield MenuItem::subMenu(t('Zikula'), 'fas fa-rocket')->setSubItems([
            MenuItem::linkToUrl(t('Website'), 'fas fa-house', 'https://ziku.la/')->setLinkTarget('_blank'),
            MenuItem::linkToUrl(t('Docs'), 'fas fa-file-contract', 'https://docs.ziku.la/')->setLinkTarget('_blank'),
            MenuItem::linkToUrl(t('Support Slack'), 'fab fa-slack', 'https://joinslack.ziku.la/')->setLinkTarget('_blank'),
            MenuItem::linkToUrl(t('ModuleStudio'), 'fas fa-wand-sparkles', 'https://modulestudio.de/en/documentation/')->setLinkTarget('_blank'),
        ]);
        yield MenuItem::subMenu(t('Foundation'), 'fas fa-cubes-stacked')->setSubItems([
            MenuItem::linkToUrl(t('Symfony'), 'fab fa-symfony', 'https://symfony.com/')->setLinkTarget('_blank'),
            MenuItem::linkToUrl(t('Twig'), 'fas fa-file-lines', 'https://twig.symfony.com/')->setLinkTarget('_blank'),
            MenuItem::linkToUrl(t('Doctrine'), 'fas fa-database', 'https://www.doctrine-project.org/')->setLinkTarget('_blank'),
            MenuItem::linkToUrl(t('EasyAdmin'), 'fas fa-screwdriver-wrench', 'https://symfony.com/bundles/EasyAdminBundle/current/index.html')->setLinkTarget('_blank'),
            MenuItem::linkToUrl(t('Bootstrap'), 'fab fa-bootstrap', 'https://getbootstrap.com/')->setLinkTarget('_blank'),
        ]);
    }
}
