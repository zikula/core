<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * GettextExtension class.
 */
class GettextExtension extends \Twig_Extension
{
    private $container;

    /**
     * @var \Zikula\Common\Translator\Translator
     */
    private $translator;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->translator = $this->container->get('translator');
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('__', [$this, '__'], array('needs_environment' => true)),
            new \Twig_SimpleFunction('_n', [$this, '_n'], array('needs_environment' => true)),
            new \Twig_SimpleFunction('__f', [$this, '__f'], array('needs_environment' => true)),
            new \Twig_SimpleFunction('_fn', [$this, '_fn'], array('needs_environment' => true)),
            new \Twig_SimpleFunction('__p', [$this, '__p'], array('needs_environment' => true)),
            new \Twig_SimpleFunction('__fp', [$this, '__fp'], array('needs_environment' => true)),
            new \Twig_SimpleFunction('_fnp', [$this, '_fnp'], array('needs_environment' => true)),
            new \Twig_SimpleFunction('no__', [$this, 'no__']),
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
    public function __(\Twig_Environment $env, $message, $domain = null, $locale = null)
    {
        return $this->translator->__(/** @Ignore */$message, $domain, $locale);
    }

    /**
     * @see __f()
     */
    public function __f(\Twig_Environment $env, $message, $params, $domain = null, $locale = null)
    {
        return $this->translator->__f(/** @Ignore */$message, $params, $domain, $locale);
    }

    /**
     * @see _n()
     */
    public function _n(\Twig_Environment $env, $singular, $plural, $count, $domain = null, $locale = null)
    {
        return $this->translator->_n(/** @Ignore */$singular, $plural, $count, $domain, $locale);
    }

    /**
     * @see _fn()
     */
    public function _fn(\Twig_Environment $env, $singular, $plural, $count, $params, $domain = null, $locale = null)
    {
        return $this->translator->_fn(/** @Ignore */$singular, $plural, $count, $params, $domain, $locale);
    }

    /**
     * @see no__()
     */
    public function no__($msgid)
    {
        return $msgid;
    }

    /**
     * Translator context functions
     *
     * @todo Define how this should work
     * @link https://www.gnu.org/software/gettext/manual/html_node/Contexts.html
     */

    /**
     * @see __p()
     */
    public function __p(\Twig_Environment $env, $context, $message, $domain = null)
    {
        return \__p($context, $message, $domain);
    }

    /**
     * @see __fp()
     */
    public function __fp(\Twig_Environment $env, $context, $message, $params, $domain = null)
    {
        return \__fp($context, $message, $params, $domain);
    }

    /**
     * @see _fpn()
     */
    public function _fnp(\Twig_Environment $env, $context, $singular, $plural, $count, $params, $domain = null)
    {
        return \_fnp($context, $singular, $plural, $count, $params, $domain);
    }

    /**
     * @see _np()
     */
    public function _np(\Twig_Environment $env, $context, $singular, $plural, $count, $domain = null)
    {
        return \_np($context, $singular, $plural, $count, $domain);
    }
}
