<?php

namespace Zikula\RoutesModule\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RedirectingController.
 */
class RedirectingController extends Controller
{
    /**
     * Remove trailing slash.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeTrailingSlashAction(Request $request)
    {
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();

        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

        return $this->redirect($url, 301);
    }
}
