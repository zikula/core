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

namespace Zikula\SecurityCenterModule\Api;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\CoreBundle\Event\GenericEvent;
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

    public function __construct(
        VariableApiInterface $variableApi,
        bool $installed,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->variableApi = $variableApi;
        $this->installed = $installed;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function filter($value)
    {
        if (!$this->installed) {
            return $value;
        }
        if ('cli' !== PHP_SAPI) {
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
        if (!isset($event)) {
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
                $value = $this->eventDispatcher->dispatch($event, self::HTML_STRING_FILTER)->getData();
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
                static function($matches) {
                        if (!$matches) {
                            return '';
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

    private function getAllowedTags(): string
    {
        $allowedHTML = [];
        $allowableHTML = $this->variableApi->getSystemVar('AllowableHTML');
        if (is_array($allowableHTML)) {
            foreach ($allowableHTML as $k => $v) {
                if ('!--' === $k) {
                    if (self::TAG_NOT_ALLOWED !== $v) {
                        $allowedHTML[] = "${k}.*?--";
                    }
                } else {
                    switch ($v) {
                        case self::TAG_NOT_ALLOWED:
                            break;
                        case self::TAG_ALLOWED_PLAIN:
                            $allowedHTML[] = "/?${k}\\s*/?";
                            break;
                        case self::TAG_ALLOWED_WITH_ATTRIBUTES:
                            $allowedHTML[] = "/?\\s*${k}" . "(\\s+[\\w\\-:]+\\s*=\\s*(\"[^\"]*\"|'[^']*'))*" . '\s*/?';
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
