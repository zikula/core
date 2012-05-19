<?php

namespace Zikula\Bundle\ThemeBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

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
        return 'zgettext';
    }

    /**
     * Alias for gettext.
     *
     * @param \Twig_Environment $env
     * @param string $msgid  The message.
     * @param string $domain Gettext domain.
     *
     * @throws \Exception If $domain is an array.
     */
    function __(\Twig_Environment $env, $msgid, $domain = null)
    {
        return (isset($domain) ? $this->_dgettext($domain, $msgid) : $this->_gettext($msgid));
    }

    /**
     * Format _dgettext string.
     *
     * Uses sprintf() formatting %s etc, and positional %1$s, %2$s etc.
     * {@link http://us.php.net/manual/en/function.sprintf.php
     * %1$s specifies the first occurance in the array of params, %2$s the second
     *
     * Note params must passed either as
     * __('beer') or as $beer where $beer = __('beer') somewhere before the call
     * __f('I want some %s with my meal', __('beer'));
     * __f('Give me %s with my %s', array(__('some sausages'), __('beer'));
     * __f('%1$s buy me %2$s', array('Drak', __('a beer'));
     *
     * @param \Twig_Environment $env
     * @param string $msgid  The message.
     * @param mixed  $params Format parameters or attay of parameters.
     * @param string $domain Gettext domain.
     *
     * @throws \Exception If $domain is an array.
     * @return string
     */
    function __f(\Twig_Environment $env, $msgid, $params, $domain = null)
    {
        $msgstr = (isset($domain) ? $this->_dgettext($domain, $msgid) : $this->_gettext($msgid));
        $params = (is_array($params) ? $params : array($params));

        return vsprintf($msgstr, $params);
    }

    /**
     * Format _dngettext string.
     *
     * Uses sprintf() formatting %s etc, and positional %1$s, %2$s etc.
     * {@link: http://us.php.net/manual/en/function.sprintf.php}
     * %1$s specifies the first occurance in the array of params, %2$s the second
     *
     * Note params must passed either as
     * __('now') or as $value where $value = __('now') somewhere before the call
     * _fn('apple %s', 'apples %s', __('now'), 4);
     * _fn('apple %s', 'apples %s', $value, 4);
     *
     * @param \Twig_Environment $env
     * @param string  $sin    Singular form.
     * @param string  $plu    Plural form.
     * @param integer $n      Count.
     * @param mixed   $params Format parameters or attay of parameters.
     * @param string  $domain Gettext domain.
     *
     * @throws \Exception If $domain is an array.
     * @return string
     */
    function _fn(\Twig_Environment $env, $sin, $plu, $n, $params, $domain = null)
    {
        $msgstr = (isset($domain) ? $this->_dngettext($domain, $sin, $plu, (int)$n) : $this->_ngettext($sin, $plu, (int)$n));
        $params = (is_array($params) ? $params : array($params));

        return vsprintf($msgstr, $params);
    }

    /**
     * Plural translation.
     *
     * @param \Twig_Environment $env
     * @param string  $singular Singular.
     * @param string  $plural   Plural.
     * @param integer $count    Count.
     * @param string  $domain   Gettext domain.
     *
     * @throws \Exception If $domain is an array.
     * @return string
     */
    function _n(Twig_Environment $env, $singular, $plural, $count, $domain = null)
    {
        return (isset($domain) ? $this->_dngettext($domain, $singular, $plural, (int)$count) : _ngettext($singular, $plural, (int)$count));
    }

    /**
     * No operation gettext.
     *
     * @param string $msgid The Message.
     *
     * @return string
     */
    function no__($msgid)
    {
        return $msgid;
    }

    /**
     * Lookup a message in the current domain.
     *
     * @param string $msgid The Message.
     *
     * @return string
     */
    function _gettext($msgid)
    {
        return \ZGettext::getReader()->translate($msgid);
    }

    /**
     * Plural version of gettext.
     *
     * @param string  $single Singular.
     * @param string  $plural Plural.
     * @param integer $number Count.
     *
     * @return string
     */
    function _ngettext($single, $plural, $number)
    {
        return \ZGettext::getReader()->ngettext($single, $plural, $number);
    }

    /**
     * Override the current domain.
     *
     * @param string $domain Gettext domain.
     * @param string $msgid  The message.
     *
     * @return string
     */
    function _dgettext($domain, $msgid)
    {
        return \ZGettext::getReader($domain)->translate($msgid);
    }

    /**
     * Plural version of dgettext.
     *
     * @param string  $domain Gettext domain.
     * @param string  $single Singular.
     * @param string  $plural Plural.
     * @param integer $number Count.
     *
     * @return string
     */
    function _dngettext($domain, $single, $plural, $number)
    {
        return \ZGettext::getReader($domain)->ngettext($single, $plural, $number);
    }

    /**
     * Overrides the domain and category for a single lookup.
     *
     * @param string   $domain   Gettext domain.
     * @param string   $msgid    The message.
     * @param constant $category LC_CONSTANT.
     *
     * @return string
     */
    function _dcgettext($domain, $msgid, $category)
    {
        return \ZGettext::getReader($domain, $category)->translate($msgid);
    }

    /**
     * Plural version of dcgettext.
     *
     * @param string   $domain   Gettext domain.
     * @param string   $single   Singular.
     * @param string   $plural   Plural.
     * @param integer  $number   Count.
     * @param constant $category LC_CONSTANT.
     *
     * @return string
     */
    function _dcngettext($domain, $single, $plural, $number, $category)
    {
        return \ZGettext::getReader($domain, $category)->ngettext($single, $plural, $number);
    }


}


