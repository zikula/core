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
            'zasset' => new \Twig_Function_Method($this, 'getAssetPath')
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
     * @todo
     * @return string
     */
    public function lang()
    {
        return 'en';
    }

    /**
     * @todo
     * @return string
     */
    public function langDirection()
    {
        return 'ltr';
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
