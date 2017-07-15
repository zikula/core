<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ThemeModule\Engine\Engine;

/**
 * Class CreateThemedResponseListener
 *
 * This class intercepts the Response and modifies it to return a themed Response.
 * It is currently fully BC with Core-1.3 in order to return a smarty-based themed response.
 */
class CreateThemedResponseListener implements EventSubscriberInterface
{
    /**
     * @var Engine
     */
    private $themeEngine;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var bool
     */
    private $installed;

    public function __construct($installed, Engine $themeEngine, VariableApiInterface $variableApi)
    {
        $this->installed = $installed;
        $this->themeEngine = $themeEngine;
        $this->variableApi = $variableApi;
    }

    public function createThemedResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (!$this->installed) {
            return;
        }

        $response = $event->getResponse();
        $format = $event->getRequest()->getRequestFormat();
        $route = $event->getRequest()->attributes->has('_route') ? $event->getRequest()->attributes->get('_route') : '0'; // default must not be '_'
        if (!($response instanceof Response)
            || is_subclass_of($response, '\Symfony\Component\HttpFoundation\Response')
            || $event->getRequest()->isXmlHttpRequest()
            || $format != 'html'
            || false === strpos($response->headers->get('Content-Type'), 'text/html')
            || $route[0] === '_' // the profiler and other symfony routes begin with '_' @todo this is still too permissive
            || $response->getStatusCode() == 500 // Internal Server Error
        ) {
            return;
        }

        // all responses are assumed to be themed. PlainResponse will have already returned.
        $twigThemedResponse = $this->themeEngine->wrapResponseInTheme($response);
        $trimWhitespace = $this->variableApi->get('ZikulaThemeModule', 'trimwhitespace', false);
        if ($trimWhitespace) {
            $this->trimWhitespace($twigThemedResponse);
        }
        $event->setResponse($twigThemedResponse);
    }

    private function trimWhitespace(Response $response)
    {
        $content = $response->getContent();

        // Pull out the script blocks
        preg_match_all("!<script[^>]*?>.*?</script>!is", $content, $match);
        $scriptBlocks = $match[0];
        $content = preg_replace("!<script[^>]*?>.*?</script>!is",
                            '@@@TWIG:TRIM:SCRIPT@@@', $content);

        // Pull out the pre blocks
        preg_match_all("!<pre[^>]*?>.*?</pre>!is", $content, $match);
        $preBlocks = $match[0];
        $content = preg_replace("!<pre[^>]*?>.*?</pre>!is",
                            '@@@TWIG:TRIM:PRE@@@', $content);

        // Pull out the textarea blocks
        preg_match_all("!<textarea[^>]*?>.*?</textarea>!is", $content, $match);
        $textareaBlocks = $match[0];
        $content = preg_replace("!<textarea[^>]*?>.*?</textarea>!is",
                            '@@@TWIG:TRIM:TEXTAREA@@@', $content);

        // remove all leading spaces, tabs and carriage returns NOT
        // preceeded by a php close tag.
        $content = trim(preg_replace('/((?<!\?>)\n)[\s]+/m', '\1', $content));

        // replace textarea blocks
        $this->readdUntrimmedBlocks('@@@TWIG:TRIM:TEXTAREA@@@', $textareaBlocks, $content);

        // replace pre blocks
        $this->readdUntrimmedBlocks('@@@TWIG:TRIM:PRE@@@', $preBlocks, $content);

        // replace script blocks
        $this->readdUntrimmedBlocks('@@@TWIG:TRIM:SCRIPT@@@', $scriptBlocks, $content);

        $response->setContent($content);
    }

    /**
     * @param string $search
     */
    private function readdUntrimmedBlocks($search, $replace, &$subject)
    {
        $len = strlen($search);
        $pos = 0;
        for ($i = 0, $count = count($replace); $i < $count; $i++) {
            if (false !== ($pos = strpos($subject, $search, $pos))) {
                $subject = substr_replace($subject, $replace[$i], $pos, $len);
            } else {
                break;
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [
                ['createThemedResponse', -2]
            ]
        ];
    }
}
