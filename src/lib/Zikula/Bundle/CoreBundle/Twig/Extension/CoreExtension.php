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
            'zasset' => new \Twig_Function_Method($this, 'getAssetPath'),
            'showflashes' => new \Twig_Function_Method($this, 'showFlashes', array('is_safe' => array('html'))),
            'array_unset' => new \Twig_Function_Method($this, 'arrayUnset'),
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
            /**
             * Get messages.
             */
            $messages = $this->container->get('session')->getFlashBag()->get($messageType);

            if (count($messages) > 0) {
                /**
                 * Set class for the messages.
                 */
                $class = (!empty($params['class'])) ? $params['class'] : "alert alert-$bootstrapClass";

                $total_messages = $total_messages + $messages;

                /**
                 * Build output of the messages.
                 */
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
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'zikulacore';
    }
}
