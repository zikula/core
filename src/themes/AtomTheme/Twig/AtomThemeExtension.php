<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AtomTheme\Twig;

use Gedmo\Sluggable\Util as Sluggable;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\ExtensionsModule\Api\VariableApi;

class AtomThemeExtension extends \Twig_Extension
{
    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(VariableApi $variableApi, RequestStack $requestStack)
    {
        $this->variableApi = $variableApi;
        $this->requestStack = $requestStack;
    }

    /**
     * Register provided functions.
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('atomId', [$this, 'id']),
            new \Twig_SimpleFunction('atomFeedLastUpdated', [$this, 'atomFeedLastUpdated']),
        ];
    }

    public function id()
    {
        $host = $this->requestStack->getMasterRequest()->getSchemeAndHttpHost();
        $startDate = $this->variableApi->getSystemVar('startdate');
        $starttimestamp = strtotime($startDate);
        $startdate = strftime('%Y-%m-%d', $starttimestamp);
        $sitename = Sluggable\Urlizer::urlize($this->variableApi->getSystemVar('sitename'));

        return "tag:{$host},{$startdate}:{$sitename}";
    }

    public function atomFeedLastUpdated()
    {
        if (!isset($GLOBALS['atom_feed_lastupdated'])) {
            $GLOBALS['atom_feed_lastupdated'] = time();
        }

        return strftime('%Y-%m-%dT%H:%M:%SZ', $GLOBALS['atom_feed_lastupdated']);
    }
}
