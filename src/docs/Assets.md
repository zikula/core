Page Assets
===========

Assets are publicly visible resources that are used to construct a web page. These include javascripts, stylesheets,
fonts, images and so forth. Zikula manages javascript and css assets with two services:

    - zikula_core.common.theme.assets_js
    - zikula_core.common.theme.assets_css

There is also one twig function that is used to add items to those same services:

    - {{ pageAddAsset() }}

Assets can be **combined** in order to speed up page loading by taking advantage of browser caching. Please see the 
`AssetCombination.md` file for more information on this subject.

In a Controller or Listener, you can access either of the services and add an asset like so:

    $this->get('zikula_core.common.theme.assets_js')->add($request->getBasePath() . '/web/modules/acmefoo/mysript.js');

In a template, it would be done like this:

    {{ pageAddAsset('javascript', zasset('@AcmeFooModule:js/myscript.js')) }}

If the load order of the scripts or stylesheets is important (and it often is), you can set a *weight* for each asset.
The 'heavier' the weight the later it will load. The _default_ weight is 100 and is what would be used in the examples
above since no weight is set. To set the weight for an asset, add it like this:

    $this->get('zikula_core.common.theme.assets_js')->add([
        $request->getBasePath() . '/web/modules/acmefoo/mysript.js' => 200
    ]);

    {{ pageAddAsset('javascript', zasset('@AcmeFooModule:js/myscript.js'), 200) }}

Weights utilized by core assets are listed below.

    const WEIGHT_JQUERY = 20;
    const WEIGHT_BOOTSTRAP_JS = 21;
    const WEIGHT_BOOTSTRAP_ZIKULA = 22;
    const WEIGHT_HTML5SHIV = 23;
    const WEIGHT_ROUTER_JS = 24;
    const WEIGHT_ROUTES_JS = 25;
    const WEIGHT_JS_TRANSLATOR = 26;
    const WEIGHT_ZIKULA_JS_TRANSLATOR = 27;
    const WEIGHT_JS_TRANSLATIONS = 28;
    const WEIGHT_DEFAULT = 100;


Non-local Assets and Asset Combination
--------------------------------------

Sometimes you wish to use non-local assets like CDN for a common library or Google font assets, etc. This is fully
supported and can be done in the same manner:

    {{ pageAddAsset('javascript', 'https://ajax.googleapis.com/ajax/libs/angularjs/1.5.7/angular.min.js') }}

#### When local assets are combined, all non-local assets will be loaded after the local assets (in order by weight)
#### if the weight is positive (>=0).

Sometimes a non-local asset must be loaded before other assets even when asset combination is enabled. This is
accomplished with **negative weights**.

    {{ pageAddAsset('stylesheet', '//fonts.googleapis.com/css?family=Montserrat:400,700', -10) }}

Assigning a *negative weight* will have an important effect: The non-local asset will be loaded **before** the
combined assets. Positively weighted non-local assets will be loaded **after** the combined assets.

If asset combination is **disabled**, then all assets will be loaded in order as one would expect with negatively 
weighted assets loading before positively loaded assets.

*Local* assets with a negative weight will still be combined in order by weight.
