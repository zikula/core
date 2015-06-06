<?php
namespace Zikula\Bundle\CoreBundle\Templating\Asset;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Asset\PathPackage as BasePathPackage;

/**
 * The path package adds a version and a base path to asset URLs.
 *
 * TODO This class can probably be removed.
 * The setting "templating.asset.path_package.class" is already disabled in app/config/config.yml.
 */
class PathPackage extends BasePathPackage
{
    /**
     * @param string                   $basePath        The base path to be prepended to relative paths
     * @param VersionStrategyInterface $versionStrategy The version strategy
     *
     * Note: Append the 'web/' folder, because the application kernel of Zikula is not located in
     * the 'web' folder.
     */
    public function __construct($basePath, VersionStrategyInterface $versionStrategy, ContextInterface $context = null)
    {
        parent::__construct($basePath . '/web/', $versionStrategy, $context);
    }
}
