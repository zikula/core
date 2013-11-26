<?php

namespace Zikula\Bundle\CoreBundle\Twig\Extension;

use Zikula\Bundle\CoreBundle\Twig;

class CoreExtension extends \Twig_Extension
{
    private $container;

    public function __construct($container = null)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(
            'evaluate' => new \Twig_Filter_Method($this, 'evaluateFilter', array(
                                                                          'needs_environment' => true,
                                                                          'needs_context' => true,
                                                                          'is_safe' => array(
                                                                              'evaluate' => true
                                                                          )
                                                                     )),
        );
    }

    public function evaluateFilter(\Twig_Environment $environment, $context, $value)
    {
        $loader = $environment->getLoader();
        $environment->setLoader(new \Twig_Loader_String());
        $return = $environment->render($value, $context);
        $environment->setLoader($loader);

        return $return;
    }

    public function getTokenParsers()
    {
        return array(
            new Twig\TokenParser\SwitchTokenParser(),
        );
    }

    public function getGlobals()
    {
        return array(
            'metatags' => \ServiceUtil::getManager()->getParameter('zikula_view.metatags'),
            'modvars' => \ModUtil::getModvars(),
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
            new \Twig_SimpleFunction('jsconfig', array('JCSSUtil', 'getJSConfig')), // @todo dev-legacy
            new \Twig_SimpleFunction('pagegetvar', array($this, 'pageGetVar')), // @todo dev-legacy
            new \Twig_SimpleFunction('pageaddvar', array($this, 'pageAddVar')), // @todo dev-legacy
            new \Twig_SimpleFunction('pagesetvar', array($this, 'pageSetVar')), // @todo dev-legacy
            new \Twig_SimpleFunction('langdirection', array('ZLanguage', 'getDirection')), // @todo dev-legacy
            new \Twig_SimpleFunction('lang', array('ZLanguage', 'getLanguageCode')), // @todo dev-legacy
            new \Twig_SimpleFunction('charset', array('ZLanguage', 'getHomepageUrl')), // @todo dev-legacy
            new \Twig_SimpleFunction('homepage', array('System', 'getHomepageUrl')), // @todo dev-legacy
            new \Twig_SimpleFunction('modurl', array($this, 'modurl')), // @todo dev-legacy
            new \Twig_SimpleFunction('button', array($this, 'button')),
            new \Twig_SimpleFunction('img', array($this, 'img')),
            new \Twig_SimpleFunction('icon', array($this, 'icon')),
            new \Twig_SimpleFunction('blockposition', array($this, 'showBlockPosition')),
            new \Twig_SimpleFunction('showblock', array($this, 'showBlock')),
            new \Twig_SimpleFunction('blockinfo', array($this, 'getBlockInfo')),
            new \Twig_SimpleFunction('zasset', array($this, 'getAssetPath')),
        );
    }

    public function pageAddVar($key, $value)
    {
        \PageUtil::addVar($key, $value);
    }

    public function pageSetVar($key, $value)
    {
        \PageUtil::setVar($key, $value);
    }

    public function pageGetVar($key)
    {
        return \PageUtil::getVar($key);
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

    public function modurl($a)
    {
        return call_user_func_array('ModUtil::url', $a);
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
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'zikulacore';
    }
}
