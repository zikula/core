<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Zikula\Core\Controller\AbstractController;

class CombinedAssetController extends AbstractController
{
    /**
     * @Route("/combined_asset/{type}/{key}", options={"i18n"=false})
     * @param $type
     * @param $key
     * @return Response
     */
    public function assetAction($type, $key)
    {
        $serviceName = in_array($type, ['js', 'css']) ? 'doctrine_cache.providers.zikula_' . $type . '_asset_cache' : null;
        $cachedFile = $this->get($serviceName)->fetch($key);
        $compress = $this->getParameter('zikula_asset_manager.compress');
        $lifetime = $this->getParameter('zikula_asset_manager.lifetime');
        $lifetime = abs((new \DateTime($lifetime))->getTimestamp() - (new \DateTime())->getTimestamp());
        if ($compress && extension_loaded('zlib')) {
            ini_set('zlib.output_handler', '');
            ini_set('zlib.output_compression', 1);
        }
        $response = new Response($cachedFile);
        $response->headers->set('Content-type', 'js' == $type ? 'text/javascript' : 'text/css');
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $key);
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->addCacheControlDirective('must-revalidate');
        $response->headers->set('Expires', gmdate("D, d M Y H:i:s", time() + $lifetime) . ' GMT');

        return $response;
    }
}
