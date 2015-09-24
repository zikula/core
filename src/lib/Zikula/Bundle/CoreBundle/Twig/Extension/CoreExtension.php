<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Bundle\CoreBundle\Twig;
use Zikula\Bundle\CoreBundle\Twig\Extension\SimpleFunction\AdminMenuPanelSimpleFunction;

class CoreExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'zikulacore';
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    public function getTokenParsers()
    {
        return array(
            new Twig\TokenParser\SwitchTokenParser(),
        );
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {

        return array(
            new \Twig_SimpleFunction('button', [$this, 'button']),
            new \Twig_SimpleFunction('img', [$this, 'img']),
            new \Twig_SimpleFunction('icon', [$this, 'icon']),
            new \Twig_SimpleFunction('lang', [$this, 'lang']),
            new \Twig_SimpleFunction('langdirection', [$this, 'langDirection']),
            new \Twig_SimpleFunction('showblockposition', [$this, 'showBlockPosition'], array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('showblock', [$this, 'showBlock']),
            new \Twig_SimpleFunction('blockinfo', [$this, 'getBlockInfo']),
            new \Twig_SimpleFunction('zasset', [$this, 'getAssetPath']),
            new \Twig_SimpleFunction('showflashes', [$this, 'showFlashes'], array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('array_unset', [$this, 'arrayUnset']),
            new \Twig_SimpleFunction('pageSetVar', [$this, 'pageSetVar']),
            new \Twig_SimpleFunction('pageAddVar', [$this, 'pageAddVar']),
            new \Twig_SimpleFunction('pageAddAsset', [$this, 'pageAddAsset']),
            new \Twig_SimpleFunction('pageGetVar', [$this, 'pageGetVar']),
            new \Twig_SimpleFunction('getModVar', [$this, 'getModVar']),
            new \Twig_SimpleFunction('setMetaTag', [$this, 'setMetaTag']),
            new \Twig_SimpleFunction('hasPermission', [$this, 'hasPermission']),
            new \Twig_SimpleFunction('adminPanelMenu', [new AdminMenuPanelSimpleFunction($this), 'display'], ['is_safe' => array('html')]),
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('languageName', [$this, 'languageName']),
            new \Twig_SimpleFilter('safeHtml', [$this, 'safeHtml'], array('is_safe' => array('html'))),
        );
    }

    public function getAssetPath($path)
    {
        $theme = $this->container->get('zikula_core.common.theme_engine')->getTheme();
        $themeName = isset($theme) ? $theme->getName() : '';

        return $this->container->get('zikula_core.common.theme.asset_helper')->resolve($path, $themeName);
    }

    public function showBlockPosition($name, $implode = true)
    {
        return \BlockUtil::displayPosition($name, false, $implode);
    }

    public function getBlockInfo($bid = 0, $name = null)
    {
        // get the block info array
        $blockinfo = \BlockUtil::getBlockInfo($bid);

        if ($name) {
            return $blockinfo[$name];
        }
    }

    public function showBlock($block, $blockname, $module)
    {
        if (!is_array($block)) {
            $block = \BlockUtil::getBlockInfo($block);
        }

        return \BlockUtil::show($module, $blockname, $block);
    }

    /**
     * Function to get the site's language.
     * 
     * Available parameters:
     *     - fs:  safe for filesystem.
     * @return string The language
     */
    public function lang($fs = false)
    {
        $result = ($fs ? \ZLanguage::transformFS(\ZLanguage::getLanguageCode()) : \ZLanguage::getLanguageCode());

        return $result;
    }

    /**
     * Function to get the language direction
     * 
     * @return string   the language direction
     */
    public function langDirection()
    {
        return \ZLanguage::getDirection();
    }

    public function button()
    {

    }

    public function img()
    {

    }

    public function icon()
    {

    }

    /**
     * Display flash messages in twig template. Defaults to bootstrap alert classes.
     *
     * <pre>
     *  {{ showflashes() }}
     *  {{ showflashes({'class': 'custom-class', 'tag': 'span'}) }}
     * </pre>
     *
     * @param array $params
     * @return string
     */
    public function showFlashes(array $params = array())
    {
        $result = '';
        $total_messages = array();
        $messageTypeMap = array(
            \Zikula_Session::MESSAGE_ERROR => 'danger',
            \Zikula_Session::MESSAGE_WARNING => 'warning',
            \Zikula_Session::MESSAGE_STATUS => 'success',
            'danger' => 'danger',
            'success' => 'success',
        );

        foreach ($messageTypeMap as $messageType => $bootstrapClass) {
            $messages = $this->container->get('session')->getFlashBag()->get($messageType);
            if (count($messages) > 0) {
                // Set class for the messages.
                $class = (!empty($params['class'])) ? $params['class'] : "alert alert-$bootstrapClass";
                $total_messages = $total_messages + $messages;
                // Build output of the messages.
                if (empty($params['tag']) || ($params['tag'] != 'span')) {
                    $params['tag'] = 'div';
                }
                $result .= '<' . $params['tag'] . ' class="' . $class . '"';
                if (!empty($params['style'])) {
                    $result .= ' style="' . $params['style'] . '"';
                }
                $result .= '>';
                $result .= implode('<hr />', $messages);
                $result .= '</' . $params['tag'] . '>';
            }
        }

        if (empty($total_messages)) {
            return "";
        }

        return $result;
    }

    /**
     * Delete a key of an array
     *
     * @param array  $array Source array
     * @param string $key   The key to remove
     *
     * @return array
     */
    public function arrayUnset($array, $key)
    {
        unset($array[$key]);

        return $array;
    }

    /**
     * @param string $code
     * @return string
     */
    public function languageName($code)
    {
        return \ZLanguage::getLanguageName($code);
    }

    /**
     * @param $string
     * @return string
     */
    public function safeHtml($string)
    {
        return \DataUtil::formatForDisplayHTML($string);
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
            throw new \InvalidArgumentException(__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        $this->container->get('zikula_core.common.theme.pagevars')->set($name, $value);
    }

    /**
     * @deprecated at Core 1.4.1, remove at Core-2.0
     * @see use pageSetVar() or pageAddAsset()
     * @param string $name
     * @param string $value
     */
    public function pageAddVar($name, $value)
    {
        $this->container->get('logger')->log(\Monolog\Logger::DEBUG, '\Zikula\Bundle\CoreBundle\Twig\Extension\CoreExtension::pageAddVar is deprecated use pageAddAsset() or pageSetVar().');
        if (in_array($name, ['stylesheet', 'javascript', 'header', 'footer'])) {
            $this->pageAddAsset($name, $value);
        } else {
            $this->pageSetVar($name, $value);
        }
    }

    /**
     * Zikula allows only the following asset types
     * <ul>
     *  <li>stylesheet</li>
     *  <li>javascript</li>
     *  <li>header</li>
     *  <li>footer</li>
     * </ul>
     *
     * @param string $type
     * @param string $value
     */
    public function pageAddAsset($type, $value)
    {
        if (empty($type) || empty($value)) {
            throw new \InvalidArgumentException(__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }
        if (!in_array($type, ['stylesheet', 'javascript', 'header', 'footer'])) {
            throw new \InvalidArgumentException(__('Invalid argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        // @todo handle this polyfill feature issue
        if ($value == 'polyfill') {
            $features = isset($params['features']) ? $params['features'] : 'forms';
        } else {
            $features = null;
        }

        // remove this code block at Core-2.0 because all themes are twig based
        $themeBundle = $this->container->get('zikula_core.common.theme_engine')->getTheme();
        if (!$themeBundle->isTwigBased()) {
            \PageUtil::addVar($type, $value);
            return;
        }

        if ('stylesheet' == $type) {
            $this->container->get('zikula_core.common.theme.assets_css')->add($value);
        } elseif ('javascript' == $type) {
            $this->container->get('zikula_core.common.theme.assets_js')->add($value);
        } elseif ('header' == $type) {
            $this->container->get('zikula_core.common.theme.assets_header')->add($value);
        } elseif ('footer' == $type) {
            $this->container->get('zikula_core.common.theme.assets_footer')->add($value);
        }
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function pageGetVar($name, $default = null)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException(__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        return $this->container->get('zikula_core.common.theme.pagevars')->get($name, $default);
    }

    /**
     * @param $module
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function getModVar($module, $name, $default = null)
    {
        if (empty($module) || empty($name)) {
            throw new \InvalidArgumentException(__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        return \ModUtil::getVar($module, $name, $default);
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setMetaTag($name, $value)
    {
        if (empty($name) || empty($value)) {
            throw new \InvalidArgumentException(__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        $metaTags = $this->container->hasParameter('zikula_view.metatags') ? $this->container->getParameter('zikula_view.metatags') : array();
        $metaTags[$name] = \DataUtil::formatForDisplay($value);
        $this->container->setParameter('zikula_view.metatags', $metaTags);
    }

    /**
     * @param string $component
     * @param string $instance
     * @param string $level
     * @return bool
     */
    public function hasPermission($component, $instance, $level)
    {
        if (empty($component) || empty($instance) || empty($level)) {
            throw new \InvalidArgumentException(__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        $result = \SecurityUtil::checkPermission($component, $instance, constant($level));

        return (boolean) $result;
    }
}
