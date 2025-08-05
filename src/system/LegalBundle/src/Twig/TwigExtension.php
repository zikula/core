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

namespace Zikula\LegalBundle\Twig;

use Symfony\Component\Routing\RouterInterface;
use Twig\Attribute\AsTwigFunction;
use Twig\Environment;
use Twig\Loader\LoaderInterface;

class TwigExtension
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly Environment $twig,
        private readonly LoaderInterface $twigLoader,
        private readonly array $legalConfig
    ) {
    }

    /**
     * Returns the link to a specific policy for the Legal bundle.
     *
     * Example
     *     {{ zikulalegalbundle_getUrl('termsOfUse') }}
     */
    #[AsTwigFunction('zikulalegalbundle_getUrl')]
    public function getUrl(string $policy = ''): string
    {
        // see https://stackoverflow.com/questions/1993721/how-to-convert-pascalcase-to-snake-case
        $policyConfigName = mb_strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $policy));
        if ('accessibility_statement' === $policyConfigName) {
            $policyConfigName = 'accessibility';
        }
        $policyConfig = $this->legalConfig['policies'][$policyConfigName];

        return $policyConfig['custom_url'] ?: $this->router->generate('zikula_legal_user_' . mb_strtolower($policy));
    }

    /**
     * Displays a single inline user link of a specific policy for the Legal bundle.
     *
     * Example
     *     {{ zikulalegalbundle_inlineLink('termsOfUse') }}
     *
     * Templates used:
     *      User/Policy/InlineLink/*
     */
    #[AsTwigFunction('zikulalegalbundle_inlineLink', isSafe: ['html'])]
    public function inlineLink(string $policy = '', string $target = ''): string
    {
        $templatePath = '@ZikulaLegal/User/Policy/InlineLink/';
        $templateParameters = [
            'policyUrl' => $this->getUrl($policy),
            'target' => $target,
        ];

        if (!empty($policy)) {
            $template = $templatePath . $policy . '.html.twig';
            if ($this->twigLoader->exists($template)) {
                return $this->twig->render($template, $templateParameters);
            }
        }

        return $this->twig->render($templatePath . 'notFound.html.twig', $templateParameters);
    }

    /**
     * Returns the minimum age for the age policy.
     *
     * Example
     *     {{ zikulalegalbundle_minimumAge() }}
     */
    #[AsTwigFunction('zikulalegalbundle_minimumAge')]
    public function getMinimumAge(): int
    {
        return $this->legalConfig['minimum_age'];
    }
}
