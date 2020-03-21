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

namespace Zikula\ThemeModule\Engine;

use Symfony\Component\HttpFoundation\Response;

class ResponseTransformer
{
    public function trimWhitespace(Response $response): void
    {
        $content = $response->getContent();

        // Pull out the script blocks
        preg_match_all('!<script[^>]*?>.*?</script>!is', $content, $match);
        $scriptBlocks = $match[0];
        $content = preg_replace(
            '!<script[^>]*?>.*?</script>!is',
            '@@@TWIG:TRIM:SCRIPT@@@',
            $content
        );

        // Pull out the pre blocks
        preg_match_all('!<pre[^>]*?>.*?</pre>!is', $content, $match);
        $preBlocks = $match[0];
        $content = preg_replace(
            '!<pre[^>]*?>.*?</pre>!is',
            '@@@TWIG:TRIM:PRE@@@',
            $content
        );

        // Pull out the textarea blocks
        preg_match_all(
            '!<textarea[^>]*?>.*?</textarea>!is',
            $content,
            $match
        );
        $textareaBlocks = $match[0];
        $content = preg_replace(
            '!<textarea[^>]*?>.*?</textarea>!is',
            '@@@TWIG:TRIM:TEXTAREA@@@',
            $content
        );

        // remove all leading spaces, tabs and carriage returns NOT
        // preceeded by a php close tag.
        $content = trim(preg_replace('/((?<!\?>)\n)[\s]+/m', '\1', $content));

        // replace textarea blocks
        $this->returnUntrimmedBlocks('@@@TWIG:TRIM:TEXTAREA@@@', $textareaBlocks, $content);

        // replace pre blocks
        $this->returnUntrimmedBlocks('@@@TWIG:TRIM:PRE@@@', $preBlocks, $content);

        // replace script blocks
        $this->returnUntrimmedBlocks('@@@TWIG:TRIM:SCRIPT@@@', $scriptBlocks, $content);

        $response->setContent($content);
    }

    private function returnUntrimmedBlocks(string $search, string $replace, string &$subject): void
    {
        $len = mb_strlen($search);
        $pos = 0;
        for ($i = 0, $count = count($replace); $i < $count; $i++) {
            if (false !== ($pos = mb_strpos($subject, $search, $pos))) {
                $subject = substr_replace($subject, $replace[$i], $pos, $len);
            } else {
                break;
            }
        }
    }
}
