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

use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\SecurityCenterModule\Util as SecurityCenterUtil;

/**
 * Twig extension class.
 */
class TwigExtension extends \Twig_Extension
{
    /**
     * @var bool
     */
    private $isInstalled;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * TwigExtension constructor.
     *
     * @param bool        $isInstalled Installed flag
     * @param VariableApi $variableApi VariableApi service instance
     */
    public function __construct($isInstalled, VariableApi $variableApi)
    {
        $this->isInstalled = $isInstalled;
        $this->variableApi = $variableApi;
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
        // @todo!!!
        return $string;

        $string = \DataUtil::formatForDisplayHTML($string);

        if (!$this->isInstalled) {
            return $string;
        }
        if (\System::isInstalling() || \System::isUpgrading()) {
            return $string;
        }

        if ($this->variableApi->getSystemVar('outputfilter') > 1) {
            return $string;
        }

        /*if (!$event->isMasterRequest()) {
            return $string;
        }
        if ($request->isXmlHttpRequest()) {
            return $string;
        }*/

        // recursive call for arrays
        // [removed as it's duplicated in datautil]

        // prepare htmlpurifier class
        static $safeCache;
        $purifier = SecurityCenterUtil::getpurifier();

        $md5 = md5($string);
        // check if the value is in the safecache
        if (isset($safeCache[$md5])) {
            $string = $safeCache[$md5];
        } else {
            // save renderer delimiters
            $string = str_replace('{', '%VIEW_LEFT_DELIMITER%', $string);
            $string = str_replace('}', '%VIEW_RIGHT_DELIMITER%', $string);
            $string = $purifier->purify($string);

            // restore renderer delimiters
            $string = str_replace('%VIEW_LEFT_DELIMITER%', '{', $string);
            $string = str_replace('%VIEW_RIGHT_DELIMITER%', '}', $string);

            // cache the value
            $safeCache[$md5] = $string;
        }

        return $string;
    }

    /**
     * Returns internal name of this extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'zikulasecuritymodule_twigextension';
    }
}
