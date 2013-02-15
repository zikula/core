<?php

namespace Zikula\Bundle\CoreBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * GettextExtension
 *
 * @todo add automatic domain detection (from the environment)
 */
class GettextExtension extends \Twig_Extension
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
            '__' => new \Twig_Function_Method($this, '__', array('needs_environment' => true)),
            '__f' => new \Twig_Function_Method($this, '__f', array('needs_environment' => true)),
            '_fn' => new \Twig_Function_Method($this, '_fn', array('needs_environment' => true)),
            '__p' => new \Twig_Function_Method($this, '__p', array('needs_environment' => true)),
            '__fp' => new \Twig_Function_Method($this, '__fp', array('needs_environment' => true)),
            '_fnp' => new \Twig_Function_Method($this, '_fnp', array('needs_environment' => true)),
            'no__' => new \Twig_Function_Method($this, 'no__'),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'zikula_gettext';
    }

    /**
     * @see __()
     */
    public function __(\Twig_Environment $env, $message, $domain = null)
    {
        return \__($message, $domain);
    }

    /**
     * @see __p()
     */
    public function __p(\Twig_Environment $env, $context, $message, $domain = null)
    {
        return \__p($context, $message, $domain);
    }

    /**
     * @see __f()
     */
    public function __f(\Twig_Environment $env, $message, $params, $domain = null)
    {
        return \__f($message, $params, $domain);
    }

    /**
     * @see __fp()
     */
    public function __fp(\Twig_Environment $env, $context, $message, $params, $domain = null)
    {
        return \__fp($context, $message, $params, $domain);
    }

    /**
     * @see _fn()
     */
    public function _fn(\Twig_Environment $env, $singular, $plural, $count, $params, $domain = null)
    {
        return \_fn($singular, $plural, $count, $params, $domain);
    }

    /**
     * @see _fpn()
     */
    public function _fnp(\Twig_Environment $env, $context, $singular, $plural, $count, $params, $domain = null)
    {
        return \_fnp($context, $singular, $plural, $count, $params, $domain);
    }

    /**
     * @see _n()
     */
    public function _n(\Twig_Environment $env, $singular, $plural, $count, $domain = null)
    {
        return \_n($singular, $plural, $count, $domain);
    }

    /**
     * @see _np()
     */
    public function _np(\Twig_Environment $env, $context, $singular, $plural, $count, $domain = null)
    {
        return \_np($context, $singular, $plural, $count, $domain);
    }


    /**
     * @see no__()
     */
    public function no__($msgid)
    {
        return $msgid;
    }
}
