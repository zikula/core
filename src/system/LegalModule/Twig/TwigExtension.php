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

namespace Zikula\LegalModule\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Loader\LoaderInterface;
use Twig\TwigFunction;

/**
 * Twig extension class.
 */
class TwigExtension extends AbstractExtension
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var LoaderInterface
     */
    protected $twigLoader;

    public function __construct(Environment $twig, LoaderInterface $twigLoader)
    {
        $this->twig = $twig;
        $this->twigLoader = $twigLoader;
    }

    /**
     * Returns a list of custom Twig functions.
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('zikulalegalmodule_inlineLink', [$this, 'inlineLink'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * The zikulalegalmodule_inlineLink function displays a single inline user link of a
     * specific policy for the Legal module.
     *
     * Example
     *     {{ zikulalegalmodule_inlineLink('termsOfUse') }}
     *
     * Templates used:
     *      InlineLink/accessibilityStatement.html.twig
     *      InlineLink/cancellationRightPolicy.html.twig
     *      InlineLink/legalNotice.html.twig
     *      InlineLink/notFound.html.twig
     *      InlineLink/privacyPolicy.html.twig
     *      InlineLink/termsOfUse.html.twig
     *      InlineLink/tradeConditions.html.twig
     */
    public function inlineLink(string $policy = '', string $target = ''): string
    {
        $templatePath = '@ZikulaLegalModule/InlineLink/';
        $templateParameters = [
            'target' => $target
        ];

        if (!empty($policy)) {
            $template = $templatePath . $policy . '.html.twig';
            if ($this->twigLoader->exists($template)) {
                return $this->twig->render($template, $templateParameters);
            }
        }

        return $this->twig->render($templatePath . 'notFound.html.twig', $templateParameters);
    }
}
