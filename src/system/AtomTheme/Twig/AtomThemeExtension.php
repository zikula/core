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

namespace Zikula\AtomTheme\Twig;

use Gedmo\Sluggable\Util as Sluggable;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
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

    public function __construct(VariableApiInterface $variableApi, RequestStack $requestStack)
    {
        $this->variableApi = $variableApi;
        $this->requestStack = $requestStack;
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
        $host = null !== $this->requestStack->getMasterRequest() ? $this->requestStack->getMasterRequest()->getSchemeAndHttpHost() : '';
        $startDate = $this->variableApi->getSystemVar('startdate');
        $startDateParts = explode('/', $startDate);
        $startTimestamp = strtotime($startDateParts[1] . '-' . $startDateParts[0] . '-01');
        $startDate = strftime('%Y-%m-%d', $startTimestamp);
        $sitename = Sluggable\Urlizer::urlize($this->variableApi->getSystemVar('sitename'));

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
