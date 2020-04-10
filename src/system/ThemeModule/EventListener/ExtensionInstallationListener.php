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

namespace Zikula\ThemeModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Event\ExtensionPostDisabledEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostEnabledEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostInstallEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostRemoveEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostUpgradeEvent;
use Zikula\ExtensionsModule\Event\ExtensionStateEvent;

/**
 * Clear the combined asset cache when a module or theme state is changed
 */
class ExtensionInstallationListener implements EventSubscriberInterface
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CacheClearer
     */
    private $cacheClearer;

    /**
     * @var bool
     */
    private $mergerActive;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        CacheClearer $cacheClearer,
        bool $mergerActive
    ) {
        $this->kernel = $kernel;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->cacheClearer = $cacheClearer;
        $this->mergerActive = $mergerActive;
    }

    public static function getSubscribedEvents()
    {
        return [
            ExtensionPostInstallEvent::class => ['clearCombinedAssetCache'],
            ExtensionPostUpgradeEvent::class => ['clearPublishedAssets'],
            ExtensionPostEnabledEvent::class => ['clearCombinedAssetCache'],
            ExtensionPostDisabledEvent::class => ['clearCombinedAssetCache'],
            ExtensionPostRemoveEvent::class => ['clearPublishedAssets']
        ];
    }

    public function clearCombinedAssetCache(): void
    {
        if ($this->mergerActive) {
            $this->cacheClearer->clear('assets');
        }
    }

    public function clearPublishedAssets(ExtensionStateEvent $event): void
    {
        $this->clearCombinedAssetCache();

        $extension = $event->getExtensionBundle();
        if (null === $extension) {
            return;
        }

        $publicDir = realpath($this->kernel->getProjectDir() . '/public');
        $assetDirectory = $publicDir . '/' . $extension->getRelativeAssetPath() . '/';

        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($assetDirectory)) {
            return;
        }

        try {
            $fileSystem->remove($assetDirectory);
        } catch (IOExceptionInterface $exception) {
            $request = $this->requestStack->getCurrentRequest();
            if (null !== $request && $request->hasSession() && $session = $request->getSession()) {
                $session->getFlashBag()->add(
                    'warning',
                    $this->translator->trans(
                        'The directory %directory% could not be removed. Please remove it manually. Error message: %message%',
                        [
                            '%directory%' => $assetDirectory,
                            '%message%' => $exception->getMessage()
                        ]
                    )
                );
            }
        }
    }
}
