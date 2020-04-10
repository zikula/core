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

namespace Zikula\SecurityCenterModule\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Zikula\SecurityCenterModule\Api\ApiInterface\HtmlFilterApiInterface;

/**
 * Twig extension class.
 */
class TwigExtension extends AbstractExtension
{
    /**
     * @var HtmlFilterApiInterface
     */
    private $htmlFilterApi;

    public function __construct(HtmlFilterApiInterface $htmlFilterApi)
    {
        $this->htmlFilterApi = $htmlFilterApi;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('safeHtml', [$this, 'safeHtml'], ['is_safe' => ['html']])
        ];
    }

    public function safeHtml(string $string): string
    {
        return $this->htmlFilterApi->filter($string);
    }
}
