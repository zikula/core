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

namespace Zikula\Bundle\CoreInstallerBundle\Controller;

use Michelf\MarkdownExtra;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\Response\PlainResponse;

/**
 * Class DocController
 */
class DocController
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $basePath;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        Environment $twig,
        TranslatorInterface $translator
    ) {
        $this->kernel = $kernel;
        $this->twig = $twig;
        $this->translator = $translator;
    }

    public function displayAction(Request $request, string $name = 'INSTALL-2.0.md'): Response
    {
        $this->setBasePath($request);

        if (!file_exists($this->basePath . '/' . $name) && 'en' !== $request->getLocale()) {
            // fallback to English docs
            $this->basePath = str_replace('docs/' . $request->getLocale(), 'docs/en', $this->basePath);
        }

        if (file_exists($this->basePath . '/' . $name)) {
            $content = file_get_contents($this->basePath . '/' . $name);
        } else {
            $content = $this->translator->trans('The file you requested (%name%) could not be found.', ['%name%' => $name]);
        }
        $content = MarkdownExtra::defaultTransform($content);
        $templateParams = [
            'lang' => $request->getLocale(),
            'charset' => $this->kernel->getCharset(),
            'content' => $content,
        ];
        $response = new PlainResponse();
        $response->setContent($this->twig->render('@ZikulaCoreInstaller/doc.html.twig', $templateParams));

        return $response;
    }

    /**
     * Set the base path for doc files, computing whether this is a Github clone or CI build.
     */
    private function setBasePath(Request $request): void
    {
        $paths = [
            $this->kernel->getProjectDir() . '/docs/' . $request->getLocale(), // localized in ci build
            $this->kernel->getProjectDir() . '/docs/en', // default in ci build
            $this->kernel->getProjectDir() . '/..' // github clone
        ];
        foreach ($paths as $docPath) {
            if ($path = realpath($docPath)) {
                $this->basePath = $path;
            }
        }
    }
}
