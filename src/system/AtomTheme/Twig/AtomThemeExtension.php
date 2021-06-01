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

namespace Zikula\AtomTheme\Twig;

use Gedmo\Sluggable\Util as Sluggable;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Zikula\Bundle\CoreBundle\Site\SiteDefinitionInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;

class AtomThemeExtension extends AbstractExtension
{
    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var SiteDefinitionInterface
     */
    private $site;

    public function __construct(
        VariableApiInterface $variableApi,
        RequestStack $requestStack,
        SiteDefinitionInterface $site
    ) {
        $this->variableApi = $variableApi;
        $this->requestStack = $requestStack;
        $this->site = $site;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('atomId', [$this, 'id']),
            new TwigFunction('atomFeedLastUpdated', [$this, 'atomFeedLastUpdated']),
        ];
    }

    public function id(): string
    {
        $host = null !== $this->requestStack->getMainRequest() ? $this->requestStack->getMainRequest()->getSchemeAndHttpHost() : '';
        $startDate = $this->variableApi->getSystemVar('startdate');
        $startDateParts = explode('/', $startDate);
        $startTimestamp = strtotime($startDateParts[1] . '-' . $startDateParts[0] . '-01');
        $startDate = strftime('%Y-%m-%d', $startTimestamp);
        $sitename = Sluggable\Urlizer::urlize($this->site->getName());

        return "tag:{$host},{$startDate}:{$sitename}";
    }

    public function atomFeedLastUpdated(): string
    {
        if (!$this->variableApi->has('ZikulaAtomTheme', 'atom_feed_lastupdated')) {
            $this->variableApi->set('ZikulaAtomTheme', 'atom_feed_lastupdated', time());
        }
        $time = $this->variableApi->get('ZikulaAtomTheme', 'atom_feed_lastupdated');

        return strftime('%Y-%m-%dT%H:%M:%SZ', $time);
    }
}
