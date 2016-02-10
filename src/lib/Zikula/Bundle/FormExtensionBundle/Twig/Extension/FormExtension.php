<?php

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
        $this->container->get('zikula_core.common.theme.assets_js')->add([$basePath . '/javascript/js-webshim/dev/polyfiller.js' => AssetBag::WEIGHT_JQUERY + 1]);
        $this->container->get('zikula_core.common.theme.assets_js')->add([$basePath . '/javascript/js-webshim/dev/polyfiller.init.js' => AssetBag::WEIGHT_JQUERY + 2]);
        $existingFeatures = $this->container->get('zikula_core.common.theme.pagevars')->get('polyfill_features', []);
        $features = array_unique(array_merge($existingFeatures, $features));
        $this->container->get('zikula_core.common.theme.pagevars')->set('polyfill_features', $features);
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
