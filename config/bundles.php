<?php

declare(strict_types=1);

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle::class => ['all' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],
    FOS\JsRoutingBundle\FOSJsRoutingBundle::class => ['all' => true],
    Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle::class => ['all' => true],
    Liip\ImagineBundle\LiipImagineBundle::class => ['all' => true],
    Translation\Bundle\TranslationBundle::class => ['all' => true],
    EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle::class => ['all' => true],
    Zikula\Bundle\CoreBundle\CoreBundle::class => ['all' => true],
    Zikula\Bundle\FormExtensionBundle\ZikulaFormExtensionBundle::class => ['all' => true],
    Zikula\CategoriesBundle\ZikulaCategoriesBundle::class => ['all' => true],
    Zikula\LegalBundle\ZikulaLegalBundle::class => ['all' => true],
    Zikula\ProfileBundle\ZikulaProfileBundle::class => ['all' => true],
    Zikula\ThemeBundle\ZikulaThemeBundle::class => ['all' => true],
    Zikula\UsersBundle\ZikulaUsersBundle::class => ['all' => true],
    Nucleos\UserBundle\NucleosUserBundle::class => ['all' => true],
    Nucleos\ProfileBundle\NucleosProfileBundle::class => ['all' => true],
];
