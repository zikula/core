<?php

namespace Zikula\Bundle\ThemeBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class CoreExtension extends \Twig_Extension
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'pagegetvar' => new \Twig_Function_Method($this, 'pageGetVar'),
            'pagesetvar' => new \Twig_Function_Method($this, 'pageSetVar'),
            'pageaddvar' => new \Twig_Function_Method($this, 'pageAddVar'),
            'button' => new \Twig_Function_Method($this, 'button'),
            'img' => new \Twig_Function_Method($this, 'img'),
            'icon' => new \Twig_Function_Method($this, 'icon'),
            'lang' => new \Twig_Function_Method($this, 'lang'),
            'langdirection' => new \Twig_Function_Method($this, 'langDirection'),
            'showblockposition' => new \Twig_Function_Method($this, 'showBlockPostition'),
            'showblock' => new \Twig_Function_Method($this, 'showBlock'),
            'blockinfo' => new \Twig_Function_Method($this, 'getBlockInfo'),
        );
    }

    public function showBlockPostition($name, $implode = true)
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

    public function pageGetVar($name, $default = null)
    {
        return \PageUtil::getVar($name, $default);
    }

    public function pageSetVar($name, $value = null)
    {
        if (in_array($name, array('stylesheet', 'javascript'))) {
            $value = explode(',', $value);
        }

        \PageUtil::setVar($name, $value);
    }

    public function pageAddVar($name, $value = null)
    {
        if (in_array($name, array('stylesheet', 'javascript'))) {
            $value = explode(',', $value);
        }

        \PageUtil::addVar($name, $value);
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
