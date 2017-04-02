<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Api;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\SecurityCenterModule\Api\ApiInterface\HtmlFilterApiInterface;

class HtmlFilterApi implements HtmlFilterApiInterface
{
    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var bool
     */
    private $installed;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * HtmlFilterApi constructor.
     * @param VariableApi $variableApi
     * @param bool $installed
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        VariableApi $variableApi,
        $installed,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->variableApi = $variableApi;
        $this->installed = $installed;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        if (!$this->installed) {
            return $value;
        }
        static $allowedtags = null;
        static $outputfilter;
        static $event;
        if (!$event) {
            $event = new GenericEvent();
        }
        if (!isset($allowedtags)) {
            $allowedHTML = [];
            $allowableHTML = $this->variableApi->getSystemVar('AllowableHTML');
            if (is_array($allowableHTML)) {
                foreach ($allowableHTML as $k => $v) {
                    if ($k == '!--') {
                        if ($v != 0) {
                            $allowedHTML[] = "$k.*?--";
                        }
                    } else {
                        switch ($v) {
                            case 0:
                                break;
                            case 1:
                                $allowedHTML[] = "/?$k\s*/?";
                                break;
                            case 2:
                                $allowedHTML[] = "/?\s*$k" . "(\s+[\w\-:]+\s*=\s*(\"[^\"]*\"|'[^']*'))*" . '\s*/?';
                                break;
                        }
                    }
                }
            }
            if (count($allowedHTML) > 0) {
                $allowedtags = '~<\s*(' . implode('|', $allowedHTML) . ')\s*>~is';
            } else {
                $allowedtags = '';
            }
        }
        if (!isset($outputfilter)) {
            $outputfilter = $this->variableApi->getSystemVar('outputfilter', 0);
        }
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->filter($v);
            }
        } else {
            // Run additional filters
            if ($outputfilter > 0) {
                $event->setData($value);
                $value = $this->eventDispatcher->dispatch(self::HTML_STRING_FILTER, $event)->getData();
            }
            // Preparse var to mark the HTML that we want
            if (!empty($allowedtags)) {
                $value = preg_replace($allowedtags, "\022\\1\024", $value);
            }
            // Fix html entities
            $value = htmlspecialchars($value);
            // Fix the HTML that we want
            $value = preg_replace_callback(
                '#\022([^\024]*)\024#',
                    function ($matches) {
                        if (!$matches) {
                            return;
                        }

                        return '<' . strtr($matches[1], ['&gt;' => '>', '&lt;' => '<', '&quot;' => '"']) . '>';
                    },
                    $value
            );
            // Fix entities if required
            if ($this->variableApi->getSystemVar('htmlentities')) {
                $value = preg_replace('/&amp;([a-z#0-9]+);/i', "&\\1;", $value);
            }
        }

        return $value;
    }
}
