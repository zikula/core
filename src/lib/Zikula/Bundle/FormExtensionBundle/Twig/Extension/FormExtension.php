<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\FormExtensionBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\ThemeModule\Engine\AssetBag;

class FormExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct($container = null)
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
        return [
            new \Twig_SimpleFunction('polyfill', [$this, 'polyfill']),
        ];
    }

//    public function getFilters()
//    {
//        return [];
//    }

    public function polyfill(array $features = ['forms'])
    {
        $basePath = $this->container->get('request')->getBasePath();
        $jsAssetBag = $this->container->get('zikula_core.common.theme.assets_js');
        $jsAssetBag->add([$basePath . '/javascript/js-webshim/dev/polyfiller.js' => AssetBag::WEIGHT_JQUERY + 1]);
        $jsAssetBag->add([$basePath . '/javascript/js-webshim/dev/polyfiller.init.js' => AssetBag::WEIGHT_JQUERY + 2]);
        $themePageVars = $this->container->get('zikula_core.common.theme.pagevars');
        $existingFeatures = $themePageVars->get('polyfill_features', []);
        $features = array_unique(array_merge($existingFeatures, $features));
        $themePageVars->set('polyfill_features', $features);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'zikula_form_extension_bundle.form_extension';
    }
}
