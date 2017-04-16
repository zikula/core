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
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\SecurityCenterModule\Api\ApiInterface\HtmlFilterApiInterface;

class HtmlFilterApi implements HtmlFilterApiInterface
{
    /**
     * @var VariableApiInterface
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
     * @param VariableApiInterface $variableApi
     * @param bool $installed
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        VariableApiInterface $variableApi,
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
        if (php_sapi_name() !== 'cli') {
            // don't use static vars when testing
            static $allowedTags = null;
            static $outputFilter;
            static $event;
        }

        if (!isset($allowedTags)) {
            $allowedTags = $this->getAllowedTags();
        }
        if (!isset($outputFilter)) {
            $outputFilter = $this->variableApi->getSystemVar('outputfilter', 0);
        }
        if (!$event) {
            $event = new GenericEvent();
        }

        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->filter($v);
            }
        } else {
            // Run additional filters
            if ($outputFilter > 0) {
                $event->setData($value);
                $value = $this->eventDispatcher->dispatch(self::HTML_STRING_FILTER, $event)->getData();
            }
            // Preparse var to mark the HTML that we want
            if (!empty($allowedTags)) {
                $value = preg_replace($allowedTags, "\022\\1\024", $value);
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
            if ($this->variableApi->getSystemVar('htmlentities', 0)) {
                $value = preg_replace('/&amp;([a-z#0-9]+);/i', "&\\1;", $value);
            }
        }

        return $value;
    }

    private function getAllowedTags()
    {
        $allowedHTML = [];
        $allowableHTML = $this->variableApi->getSystemVar('AllowableHTML');
        if (is_array($allowableHTML)) {
            foreach ($allowableHTML as $k => $v) {
                if ($k == '!--') {
                    if ($v != self::TAG_NOT_ALLOWED) {
                        $allowedHTML[] = "$k.*?--";
                    }
                } else {
                    switch ($v) {
                        case self::TAG_NOT_ALLOWED:
                            break;
                        case self::TAG_ALLOWED_PLAIN:
                            $allowedHTML[] = "/?$k\s*/?";
                            break;
                        case self::TAG_ALLOWED_WITH_ATTRIBUTES:
                            $allowedHTML[] = "/?\s*$k" . "(\s+[\w\-:]+\s*=\s*(\"[^\"]*\"|'[^']*'))*" . '\s*/?';
                            break;
                    }
                }
            }
        }
        if (count($allowedHTML) > 0) {
            $allowedTags = '~<\s*(' . implode('|', $allowedHTML) . ')\s*>~is';
        } else {
            $allowedTags = '';
        }

        return $allowedTags;
    }
}
