<?php
namespace Zikula\Bundle\CoreBundle\Templating\Asset;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\Asset\PathPackage as BasePathPackage;

/**
 * The path package adds a version and a base path to asset URLs.
 */
class PathPackage extends BasePathPackage
{
    /**
     * Constructor.
     *
     * @param Request $request The current request
     * @param string  $version The version
     * @param string  $format  The version format
     *
     * @note Append the 'web/' folder, because the application kernel of Zikula is not located in
     * the 'web' folder.
     */
    public function __construct(Request $request, $version = null, $format = null)
    {
        parent::__construct($request->getBasePath() . '/web/', $version, $format);
    }
}
