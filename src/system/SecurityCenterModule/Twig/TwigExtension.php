<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Twig;

use Zikula\SecurityCenterModule\Api\ApiInterface\HtmlFilterApiInterface;

/**
 * Twig extension class.
 */
class TwigExtension extends \Twig_Extension
{
    /**
     * @var HtmlFilterApiInterface
     */
    private $htmlFilterApi;

    /**
     * TwigExtension constructor.
     *
     * @param HtmlFilterApiInterface $htmlFilterApi
     */
    public function __construct(HtmlFilterApiInterface $htmlFilterApi)
    {
        $this->htmlFilterApi = $htmlFilterApi;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('safeHtml', [$this, 'safeHtml'], ['is_safe' => ['html']])
        ];
    }

    /**
     * @param $string
     * @return string
     */
    public function safeHtml($string)
    {
        return $this->htmlFilterApi->filter($string);
    }
}
