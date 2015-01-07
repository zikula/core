<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula CoreInstaller bundle.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Michelf\MarkdownExtra;

/**
 * Class DocController
 * @package Zikula\Bundle\CoreInstallerBundle\Controller
 */
class DocController
{
    private $kernel;
    private $router;
    private $templatingService;
    private $parser;
    private $locale;
    private $basePath;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel
     * @param RouterInterface $router The route generator
     * @param EngineInterface $templatingService
     * @param MarkdownExtra $parser
     */
    public function __construct(KernelInterface $kernel, RouterInterface $router, EngineInterface $templatingService, MarkdownExtra $parser)
    {
        $this->kernel = $kernel;
        $this->router = $router;
        $this->templatingService = $templatingService;
        $this->parser = $parser;
        $this->locale = \ZLanguage::getLanguageCode();
    }

    /**
     * @param Request $request
     * @param string $name
     * @return Response
     */
    public function displayAction(Request $request, $name = "INSTALL-1.4.0.md")
    {
        // @TODO this is temporary method of restricting the user input
        if (!in_array($name, array("INSTALL-1.4.0.md", "UPGRADE-1.4.0.md", "CHANGELOG.md", "README.md"))) {
            $name = "INSTALL-1.4.0.md";
        }
        $this->setBasePath();

        if (file_exists($this->basePath . "/$name")) {
            $content = file_get_contents($this->basePath . "/$name");
        } else {
            $content = __f('The file you requested (%s) could not be found.', "$name");
        }
        $content = $this->parser->defaultTransform($content);
        $templateParams = array(
            'lang' => $this->locale,
            'charset' => \ZLanguage::getEncoding(),
            'content' => $content,
        );
        return $this->templatingService->renderResponse('ZikulaCoreInstallerBundle::doc.html.twig', $templateParams);
    }

    /**
     * set the base path for doc files, computing whether this is a Github clone or CI build.
     */
    public function setBasePath()
    {
        if (file_exists(realpath($this->kernel->getRootDir() . '/../../composer.json'))) {
            // installation is clone of github repo and files are not moved
            $this->basePath = realpath($this->kernel->getRootDir() . '/../..');
        } else {
            $this->basePath = realpath($this->kernel->getRootDir() . '/../docs/' . $this->locale);
        }
    }
}