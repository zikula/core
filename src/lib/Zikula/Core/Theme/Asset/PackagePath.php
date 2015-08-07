<?php
namespace Zikula\Core\Theme\Asset;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Asset\PathPackage as BasePathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\Context\RequestStackContext;
use Zikula\Core\AbstractTheme;
use Zikula\Core\Theme\Engine;

class PackagePath extends BasePathPackage
{
    private $scriptPath;
    private $documentRoot;
    /**
     * @var AbstractTheme
     */
    private $themeBundle;

    /**
     * Constructor.
     *
     * @param RequestStack $requestStack The request stack.
     * @param $themeEngine
     */
    public function __construct(RequestStack $requestStack, Engine $themeEngine)
    {
        $request = $requestStack->getCurrentRequest();
        $this->scriptPath = ltrim(\dirname($request->getScriptName()), '/');
        $this->documentRoot = $request->server->get('DOCUMENT_ROOT');
        $this->themeBundle = $themeEngine->getTheme($request);

        parent::__construct('', new EmptyVersionStrategy(), new RequestStackContext($requestStack));
    }

    public function getScriptPath()
    {
        return $this->scriptPath;
    }

    public function getDocumentRoot()
    {
        return $this->documentRoot;
    }

    /**
     * @deprecated remove in Core-2.0
     * use getThemeBundle instead
     * @return string
     */
    public function getThemeName()
    {
        return $this->themeBundle->getName();
    }

    public function getThemeBundle()
    {
        return $this->themeBundle;
    }
}
