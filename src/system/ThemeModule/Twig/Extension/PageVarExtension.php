<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Twig\Extension;

use Psr\Log\LoggerInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ThemeModule\Engine\ParameterBag;

class PageVarExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ParameterBag
     */
    private $pageVars;

    /**
     * @var LoggerInterface
     * @deprecated - remove at Core-2.0
     */
    private $logger;

    /**
     * @var AssetExtension
     * @deprecated - remove at Core-2.0
     */
    private $assetExtension;

    /**
     * PageVarExtension constructor.
     * @param TranslatorInterface $translator
     * @param ParameterBag $pageVars
     * @param LoggerInterface $logger
     * @param AssetExtension $assetExtension
     */
    public function __construct(
        TranslatorInterface $translator,
        ParameterBag $pageVars,
        LoggerInterface $logger,
        AssetExtension $assetExtension
    ) {
        $this->translator = $translator;
        $this->pageVars = $pageVars;
        $this->logger = $logger;
        $this->assetExtension = $assetExtension;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('pageAddVar', [$this, 'pageAddVar']), // @deprecated
            new \Twig_SimpleFunction('pageSetVar', [$this, 'pageSetVar']),
            new \Twig_SimpleFunction('pageGetVar', [$this, 'pageGetVar']),
        ];
    }

    /**
     * @deprecated at Core 1.4.1, remove at Core-2.0
     * @see use pageSetVar() or pageAddAsset()
     * @param string $name
     * @param string $value
     */
    public function pageAddVar($name, $value)
    {
        $this->logger->log(\Monolog\Logger::DEBUG, '\Zikula\Bundle\CoreBundle\Twig\Extension\CoreExtension::pageAddVar is deprecated use pageAddAsset() or pageSetVar().');
        if (in_array($name, ['stylesheet', 'javascript', 'header', 'footer'])) {
            $this->assetExtension->pageAddAsset($name, $value);
        } else {
            $this->pageSetVar($name, $value);
        }
    }

    /**
     * Zikula imposes no restriction on page variable names.
     * Typical usage is to set `title` `meta.charset` `lang` etc.
     * array values are set using `.` in the `$name` string (e.g. `meta.charset`)
     * @param string $name
     * @param string $value
     */
    public function pageSetVar($name, $value)
    {
        if (empty($name) || empty($value)) {
            throw new \InvalidArgumentException($this->translator->__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        $this->pageVars->set($name, $value);
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function pageGetVar($name, $default = null)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException($this->translator->__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        return $this->pageVars->get($name, $default);
    }
}
