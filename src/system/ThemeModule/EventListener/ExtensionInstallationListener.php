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

namespace Zikula\ThemeModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Event\ModuleStateEvent;
use Zikula\ExtensionsModule\ExtensionEvents;

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
            ExtensionEvents::MODULE_INSTALL => ['clearCombinedAssetCache'],
            ExtensionEvents::MODULE_UPGRADE => ['clearPublishedAssets'],
            ExtensionEvents::MODULE_ENABLE => ['clearCombinedAssetCache'],
            ExtensionEvents::MODULE_DISABLE => ['clearCombinedAssetCache'],
            ExtensionEvents::MODULE_REMOVE => ['clearPublishedAssets']
        ];
    }

    public function clearCombinedAssetCache(): void
    {
        if ($this->mergerActive) {
            $this->cacheClearer->clear('assets');
        }
    }

    public function clearPublishedAssets(ModuleStateEvent $event): void
    {
        $this->clearCombinedAssetCache();

        $extension = $event->getModule();
        if (null === $extension) {
            return;
        }

        $publicDir = realpath($this->kernel->getProjectDir() . '/public');
        $extensionName = $extension->getName();
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
