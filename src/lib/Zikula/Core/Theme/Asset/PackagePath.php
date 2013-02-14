<?php
namespace Zikula\Core\Theme\Asset;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\Asset\PathPackage as BasePathPackage;

class PackagePath extends BasePathPackage
{
    private $scriptPath;
    private $documentRoot;
    private $themeName;

    /**
     * Constructor.
     *
     * @param Request $request The current request.
     * @param string  $version The version.
     * @param string  $format  The version format.
     */
    public function __construct(Request $request, $version = null, $format = null)
    {
        $this->scriptPath = ltrim(\dirname($request->getScriptName()), '/');
        $this->documentRoot = $request->server->get('DOCUMENT_ROOT');
        $this->themeName = $request->attributes->get('_theme');

        parent::__construct($request->getBasePath(), $version, $format);
    }

    public function getScriptPath()
    {
        return $this->scriptPath;
    }

    public function getDocumentRoot()
    {
        return $this->documentRoot;
    }

    public function getThemeName()
    {
        return $this->themeName;
    }
}
