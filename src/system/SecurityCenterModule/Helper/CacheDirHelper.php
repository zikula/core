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

namespace Zikula\SecurityCenterModule\Helper;

use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CacheDirHelper
{
    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        Filesystem $fileSystem,
        SessionInterface $session,
        TranslatorInterface $translator
    ) {
        $this->fileSystem = $fileSystem;
        $this->session = $session;
        $this->translator = $translator;
    }

    public function ensureCacheDirectoryExists(
        string $cacheDirectory,
        bool $forHtmlPurifier = false
    ): void {
        $fs = $this->fileSystem;

        try {
            if (!$fs->exists($cacheDirectory)) {
                if (true === $forHtmlPurifier) {
                    // this uses always a fixed environment (e.g. "prod") that is serialized
                    // in purifier configuration
                    // so ensure the main directory exists even if another environment is currently used
                    $parentDirectory = mb_substr($cacheDirectory, 0, -9);
                    if (!$fs->exists($parentDirectory)) {
                        $fs->mkdir($parentDirectory);
                    }
                }
                $fs->mkdir($cacheDirectory);
            }
        } catch (IOExceptionInterface $exception) {
            $this->session->getFlashBag()->add(
                'error',
                $this->translator->trans(
                    'An error occurred while creating cache directory at %path%',
                    ['%path%' => $exception->getPath()]
                )
            );
        }
    }
}
