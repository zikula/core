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
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Michelf\MarkdownExtra;
use Zikula\Common\Translator\TranslatorInterface;

/**
 * Class DocController
 */
class DocController
{
    private $kernel;

    private $router;

    private $templatingService;

    private $parser;

    private $basePath;

    private $translator;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel
     * @param RouterInterface $router The route generator
     * @param EngineInterface $templatingService
     * @param MarkdownExtra $parser
     * @param TranslatorInterface $translator
     */
    public function __construct(KernelInterface $kernel, RouterInterface $router, EngineInterface $templatingService, MarkdownExtra $parser, TranslatorInterface $translator)
    {
        $this->kernel = $kernel;
        $this->router = $router;
        $this->templatingService = $templatingService;
        $this->parser = $parser;
        $this->translator = $translator;
    }

    /**
     * @param Request $request
     * @param string $name
     * @return Response
     */
    public function displayAction(Request $request, $name = 'INSTALL-1.4.md')
    {
        // @TODO this is temporary method of restricting the user input
        if (!in_array($name, ['INSTALL-1.4.md', 'UPGRADE-1.4.md', 'CHANGELOG.md', 'README.md'])) {
            $name = 'INSTALL-1.4.md';
        }
        $this->setBasePath($request);

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

        return $this->templatingService->renderResponse('ZikulaCoreInstallerBundle::doc.html.twig', $templateParams);
    }

    /**
     * set the base path for doc files, computing whether this is a Github clone or CI build.
     * @param Request $request
     */
    private function setBasePath(Request $request)
    {
        if (file_exists(realpath($this->kernel->getRootDir() . '/../../composer.json'))) {
            // installation is clone of github repo and files are not moved
            $this->basePath = realpath($this->kernel->getRootDir() . '/../..');
        } else {
            $this->basePath = realpath($this->kernel->getRootDir() . '/../docs/' . $request->getLocale());
        }
    }
}
