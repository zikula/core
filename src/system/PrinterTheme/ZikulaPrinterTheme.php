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

namespace Zikula\PrinterTheme;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Zikula\ExtensionsModule\AbstractCoreTheme;

class ZikulaPrinterTheme extends AbstractCoreTheme
{
    private $links = [];

    /**
     * Override parent method in order to put content into the printer page.
     */
    public function generateThemedResponse(string $realm, Response $response, string $moduleName = null): Response
    {
        $mainContent = $response->getContent();
        $mainContent = $this->createFootnotes($mainContent);
        $mainContent .= $this->renderFootnotes();

        /* @var Environment $twig */
        $twig = $this->getContainer()->get('twig');

        $output = $twig->render('@ZikulaPrinterTheme/master.html.twig', ['maincontent' => $mainContent]);

        return new Response($output);
    }

    /**
     * Filter the content and replace links with footnotes; store the links.
     */
    private function createFootnotes(string $string): string
    {
        $text = preg_replace_callback(
            '/<a [^>]*href\s*=\s*\"?([^>\"]*)\"?[^>]*>(.*?)<\/a.*?>/i',
            function ($matches) {
                $this->links[] = html_entity_decode($matches[1]);
                // return the replaced link
                return '<strong><em>' . $matches[2] . '</em></strong> <small>[' . count($this->links) . ']</small>';
            },
            $string
        );

        return $text;
    }

    /**
     * Render the links into a list and return html.
     */
    private function renderFootnotes(): string
    {
        $translator = $this->getContainer()->get('translator');
        $text = '';
        if (empty($this->links)) {
            return $text;
        }

        $text .= '<div><strong>' . $translator->trans('Links') . '</strong>';
        $text .= '<ol>';
        $this->links = array_unique($this->links);
        foreach ($this->links as $key => $link) {
            // check for an e-mail address
            if (preg_match("/^([a-z0-9_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,4}$/i", $link)) {
                $linkText = $link;
                $link = 'mailto:' . $link;
            } else {
                $linkText = $link;
            }
            $linkText = htmlspecialchars($linkText, ENT_QUOTES);
            $link = htmlspecialchars($link, ENT_QUOTES);
            // output link
            $text .= '<li><a class="print-normal" href="' . $link . '">' . $linkText . '</a></li>' . "\n";
        }
        $text .= '</ol>';
        $text .= '</div>';

        return $text;
    }
}
