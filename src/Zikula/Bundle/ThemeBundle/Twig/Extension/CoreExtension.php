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
            'pagesetvar' => new \Twig_Function_Method($this, 'pageSetVar'),
            'pageaddvar' => new \Twig_Function_Method($this, 'pageAddVar'),
            'button' => new \Twig_Function_Method($this, 'button'),
            'img' => new \Twig_Function_Method($this, 'img'),
            'icon' => new \Twig_Function_Method($this, 'icon'),
        );
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
