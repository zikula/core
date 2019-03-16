PageAssetApi
============

classname: \Zikula\ThemeModule\Api\PageAssetApi

The PageAssetApi allows page assets to be added to a page outside of the template.
Also available is a twig template function that calls this same function.

The class makes the following methods available:

    /**
     * Zikula allows only the following asset types
     * <ul>
     *  <li>stylesheet</li>
     *  <li>javascript</li>
     *  <li>header</li>
     *  <li>footer</li>
     * </ul>
     *
     * @param string $type
     * @param string $value
     * @param int $weight
     */
    public function add($type, $value, $weight = AssetBag::WEIGHT_DEFAULT);

The class is fully tested.
