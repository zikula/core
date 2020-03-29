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

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class => ['all' => true],
    Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle::class => ['all' => true],
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],
    Zikula\Bundle\CoreBundle\CoreBundle::class => ['all' => true],
    Zikula\Bundle\CoreInstallerBundle\ZikulaCoreInstallerBundle::class => ['all' => true],
    Zikula\Bundle\FormExtensionBundle\ZikulaFormExtensionBundle::class => ['all' => true],
    Zikula\Bundle\HookBundle\ZikulaHookBundle::class => ['all' => true],
    JMS\I18nRoutingBundle\JMSI18nRoutingBundle::class => ['all' => true],
    FOS\JsRoutingBundle\FOSJsRoutingBundle::class => ['all' => true],
    Matthias\SymfonyConsoleForm\Bundle\SymfonyConsoleFormBundle::class => ['all' => true],
    Knp\Bundle\MenuBundle\KnpMenuBundle::class => ['all' => true],
    Liip\ImagineBundle\LiipImagineBundle::class => ['all' => true],
    Translation\Bundle\TranslationBundle::class => ['all' => true],
    Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle::class => ['all' => true],
    Zikula\Bundle\WorkflowBundle\ZikulaWorkflowBundle::class => ['all' => true],

    // dev-only bundles
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true, 'test' => true],
    Oro\TwigInspector\Bundle\OroTwigInspectorBundle::class => ['dev' => true],

    // System Extensions - always installed
    Zikula\AdminModule\ZikulaAdminModule::class => ['all' => true],
    Zikula\BlocksModule\ZikulaBlocksModule::class => ['all' => true],
    Zikula\CategoriesModule\ZikulaCategoriesModule::class => ['all' => true],
    Zikula\ExtensionsModule\ZikulaExtensionsModule::class => ['all' => true],
    Zikula\GroupsModule\ZikulaGroupsModule::class => ['all' => true],
    Zikula\MailerModule\ZikulaMailerModule::class => ['all' => true],
    Zikula\MenuModule\ZikulaMenuModule::class => ['all' => true],
    Zikula\PermissionsModule\ZikulaPermissionsModule::class => ['all' => true],
    Zikula\RoutesModule\ZikulaRoutesModule::class => ['all' => true],
    Zikula\SearchModule\ZikulaSearchModule::class => ['all' => true],
    Zikula\SecurityCenterModule\ZikulaSecurityCenterModule::class => ['all' => true],
    Zikula\SettingsModule\ZikulaSettingsModule::class => ['all' => true],
    Zikula\ThemeModule\ZikulaThemeModule::class => ['all' => true],
    Zikula\UsersModule\ZikulaUsersModule::class => ['all' => true],
    Zikula\ZAuthModule\ZikulaZAuthModule::class => ['all' => true],
    // System themes
    Zikula\AtomTheme\ZikulaAtomTheme::class => ['all' => true],
    Zikula\BootstrapTheme\ZikulaBootstrapTheme::class => ['all' => true],
    Zikula\PrinterTheme\ZikulaPrinterTheme::class => ['all' => true],
    Zikula\RssTheme\ZikulaRssTheme::class => ['all' => true],
];
