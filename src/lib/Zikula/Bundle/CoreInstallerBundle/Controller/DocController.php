<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Michelf\MarkdownExtra;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\Response\PlainResponse;

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
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var MarkdownExtra
     */
    private $parser;

    /**
     * @var
     */
    private $basePath;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Constructor.
     *
     * @param ZikulaHttpKernelInterface $kernel
     * @param \Twig_Environment $twig
     * @param MarkdownExtra $parser
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        \Twig_Environment $twig,
        MarkdownExtra $parser,
        TranslatorInterface $translator
    ) {
        $this->kernel = $kernel;
        $this->twig = $twig;
        $this->parser = $parser;
        $this->translator = $translator;
    }

    /**
     * @param Request $request
     * @param string $name
     * @return Response
     */
    public function displayAction(Request $request, $name = 'INSTALL-2.0.md')
    {
        $this->setBasePath($request);

        $content = '';
        if (!file_exists($this->basePath . "/$name") && $request->getLocale() != 'en') {
            // fallback to English docs
            $this->basePath = str_replace('docs/' . $request->getLocale(), 'docs/en', $this->basePath);
        }
        if (file_exists($this->basePath . "/$name")) {
            $content = file_get_contents($this->basePath . "/$name");
        } else {
            $content = $this->translator->__f('The file you requested (%s) could not be found.', ['%s' => "$name"]);
        }
        $content = $this->parser->defaultTransform($content);
        $templateParams = [
            'lang' => $request->getLocale(),
            'charset' => $this->kernel->getCharset(),
            'content' => $content,
        ];
        $response = new PlainResponse();
        $response->setContent($this->twig->render('ZikulaCoreInstallerBundle::doc.html.twig', $templateParams));

        return $response;
    }

    /**
     * set the base path for doc files, computing whether this is a Github clone or CI build.
     * @param Request $request
     */
    private function setBasePath(Request $request)
    {
        $paths = [
            $this->kernel->getRootDir() . '/../docs/' . $request->getLocale(), // localized in ci build
            $this->kernel->getRootDir() . '/../docs/en', // default in ci build
            $this->kernel->getRootDir() . '/../..' // github clone
        ];
        foreach ($paths as $docPath) {
            if ($path = realpath($docPath)) {
                $this->basePath = $path;
            }
        }
    }
}
