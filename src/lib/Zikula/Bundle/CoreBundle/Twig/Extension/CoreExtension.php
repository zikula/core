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
            'button' => new \Twig_Function_Method($this, 'button'),
            'img' => new \Twig_Function_Method($this, 'img'),
            'icon' => new \Twig_Function_Method($this, 'icon'),
            'lang' => new \Twig_Function_Method($this, 'lang'),
            'langdirection' => new \Twig_Function_Method($this, 'langDirection'),
            'showblockposition' => new \Twig_Function_Method($this, 'showBlockPosition'),
            'showblock' => new \Twig_Function_Method($this, 'showBlock'),
            'blockinfo' => new \Twig_Function_Method($this, 'getBlockInfo'),
            'zasset' => new \Twig_Function_Method($this, 'getAssetPath'),
            'showflashes' => new \Twig_Function_Method($this, 'showFlashes', array('is_safe' => array('html'))),
            'array_unset' => new \Twig_Function_Method($this, 'arrayUnset'),
            'pageSetVar' => new \Twig_Function_Method($this, 'pageSetVar'),
            'pageAddVar' => new \Twig_Function_Method($this, 'pageAddVar'),
            'pageGetVar' => new \Twig_Function_Method($this, 'pageGetVar'),
            'getModVar' => new \Twig_Function_Method($this, 'getModVar'),
            'setMetaTag' => new \Twig_Function_Method($this, 'setMetaTag'),
            'checkPermission' => new \Twig_Function_Method($this, 'checkPermission'),
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('languageName', array($this, 'languageName')),
            new \Twig_SimpleFilter('safeHtml', array($this, 'safeHtml')),
        );
    }

    public function getAssetPath($path)
    {
        return $this->container->get('theme.asset_helper')->resolve($path);
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
     * @param string $name
     * @param string $value
     */
    public function pageSetVar($name, $value)
    {
        if (empty($name) || empty($value)) {
            throw new \InvalidArgumentException(__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        \PageUtil::setVar($name, $value);
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function pageAddVar($name, $value)
    {
        if (empty($name) || empty($value)) {
            throw new \InvalidArgumentException(__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        if ($value == 'polyfill') {
            $features = isset($params['features']) ? $params['features'] : 'forms';
        } else {
            $features = null;
        }

        \PageUtil::addVar($name, $value, $features);
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

        return \PageUtil::getVar($name, $default);
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
    public function checkPermission($component, $instance, $level)
    {
        if (empty($component) || empty($instance) || empty($level)) {
            throw new \InvalidArgumentException(__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        $result = \SecurityUtil::checkPermission($component, $instance, constant($level));

        return (boolean) $result;
    }
}
