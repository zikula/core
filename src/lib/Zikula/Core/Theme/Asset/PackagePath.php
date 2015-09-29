<?php

namespace Zikula\Core\Theme\Asset;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Asset\PathPackage as BasePathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\Context\RequestStackContext;

/**
 * Class PackagePath
 * @package Zikula\Core\Theme\Asset
 *
 * This class implements Symfony's Asset management system.
 */
class PackagePath extends BasePathPackage
{
    private $scriptPath;
    private $documentRoot;

    /**
     * Constructor.
     *
     * @param RequestStack $requestStack The request stack.
     */
    public function __construct(RequestStack $requestStack)
    {
        $request = $requestStack->getCurrentRequest();
        $this->scriptPath = ltrim(\dirname($request->getScriptName()), '/');
        $this->documentRoot = rtrim($request->server->get('DOCUMENT_ROOT'), '/');

        // @todo probably change EmptyVersionStrategy
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
}
