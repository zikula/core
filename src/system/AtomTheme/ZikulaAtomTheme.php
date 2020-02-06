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

namespace Zikula\AtomTheme;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Zikula\ExtensionsModule\AbstractCoreTheme;

class ZikulaAtomTheme extends AbstractCoreTheme
{
    /**
     * Override parent method in order to add Content-type header to Response.
     */
    public function generateThemedResponse(string $realm, Response $response, string $moduleName = null): Response
    {
        /* @var Environment $twig */
        $twig = $this->getContainer()->get('twig');

        $output = $twig->render('@ZikulaAtomTheme/master.html.twig', ['maincontent' => $response->getContent()]);
        $newResponse = new Response($output);
        $newResponse->headers->add(['Content-type' => 'application/atom+xml']);

        return $newResponse;
    }
}
