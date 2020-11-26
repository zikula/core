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

namespace Zikula\ExtensionsModule\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use function Symfony\Component\String\s;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\Response\PlainResponse;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\ThemeModule\Engine\AssetFilter;

/**
 * @Route("/help")
 */
class HelpController extends AbstractController
{
    /**
     * @Route("/{moduleName}")
     * @Theme("admin")
     *
     * Display a module's help page.
     */
    public function index(
        ZikulaHttpKernelInterface $kernel,
        Request $request,
        Filesystem $fileSystem,
        RouterInterface $router,
        AssetFilter $assetFilter,
        string $moduleName
    ): Response {
        $page = $request->query->get('page', 'README');
        if (s($page)->containsAny('..')) {
            throw new \Exception('Invalid page "' . $page . '".');
        }

        $locale = $request->getLocale();
        $extension = $kernel->getBundle($moduleName);
        if (null === $extension) {
            throw new \Exception('Invalid extension "' . $moduleName . '".');
        }

        $helpPath = $extension->getPath() . '/Resources/docs/help/';

        // check if requested page exists
        if (!$fileSystem->exists($helpPath . $locale . '/' . $page . '.md')) {
            if ('en' === $locale) {
                throw new \Exception('Invalid page "' . $page . '".');
            }
            // fallback to English
            $locale = 'en';
        }
        if (!$fileSystem->exists($helpPath . $locale . '/' . $page . '.md')) {
            throw new \Exception('Invalid page "' . $page . '".');
        }

        $raw = $request->query->getInt('raw', 0);

        $content = file_get_contents($helpPath . $locale . '/' . $page . '.md');

        // rewrite local links
        $content = preg_replace_callback(
            '/\[(.*?)\]\((.*?)\)/',
            function ($match) use ($router, $moduleName, $raw) {
                $pageName = s($match[2]);
                if (false === mb_strpos($match[2], '.md')) {
                    return $match[0];
                }
                if ($pageName->startsWith('http')) {
                    return $match[0];
                }

                // local link - rewrite
                $urlArgs = [
                    'moduleName' => $moduleName,
                    'page' => $pageName->beforeLast('.md')->toString()
                ];
                if (1 === $raw) {
                    $urlArgs['raw'] = 1;
                }
                $url = $router->generate('zikulaextensionsmodule_help_index', $urlArgs);

                return '[' . $match[1] . '](' . $url . ')';
            },
            $content
        );

        $output = $this->renderView('@ZikulaExtensionsModule/Help/page.html.twig', [
            'moduleName' => $moduleName,
            'content' => $content,
            'raw' => $raw
        ]);

        if (1 === $raw) {
            $output = $assetFilter->filter($output);
            $output = new PlainResponse($output);

            return $output;
        }

        return new Response($output);
    }
}
