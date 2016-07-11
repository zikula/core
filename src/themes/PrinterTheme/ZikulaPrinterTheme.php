<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PrinterTheme;

use Symfony\Component\HttpFoundation\Response;
use Zikula\Bundle\CoreBundle\Bundle\AbstractCoreTheme;

class ZikulaPrinterTheme extends AbstractCoreTheme
{
    private $links = [];

    /**
     * Override parent method in order to add Content-type header to Response
     * @param string $realm
     * @param Response $response
     * @param null $moduleName
     * @return mixed
     */
    public function generateThemedResponse($realm, Response $response, $moduleName = null)
    {
        $mainContent = $response->getContent();
        $mainContent = $this->createFootnotes($mainContent);
        $mainContent .= $this->renderFootnotes();

        return $this->getContainer()->get('templating')->renderResponse('ZikulaPrinterTheme::master.html.twig', ['maincontent' => $mainContent]);
    }

    /**
     * filter the content and replace links with footnotes. store the links.
     * @param $string
     * @return mixed
     */
    private function createFootnotes($string)
    {
        $text = preg_replace_callback(
            '/<a [^>]*href\s*=\s*\"?([^>\"]*)\"?[^>]*>(.*?)<\/a.*?>/i',
            function ($matches) {
                // @todo - work out why some links need decoding twice (&amp;amp;....)
                $this->links[] = html_entity_decode(html_entity_decode($matches[1]));
                // return the replaced link
                return '<strong><em>' . $matches[2] . '</em></strong> <small>[' . count($this->links) . ']</small>';
            },
            $string);

        return $text;
    }

    /**
     * Render the links into a list and return html.
     * @return string
     */
    private function renderFootnotes()
    {
        $text = '';
        if (!empty($this->links)) {
            $text .= '<div><strong>' . __('Links') . '</strong>';
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
                $linkText = \DataUtil::formatForDisplay($linkText);
                $link = \DataUtil::formatForDisplay($link);
                // output link
                $text .= '<li><a class="print-normal" href="' . $link . '">' . $linkText . '</a></li>' . "\n";
            }
            $text .= '</ol>';
            $text .= '</div>';
        }

        return $text;
    }
}
