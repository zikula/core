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
use Zikula\Core\Controller\AbstractController;

class CombinedAssetController extends AbstractController
{
    /**
     * @Route("/combined_asset/{key}", options={"i18n"=false})
     */
    public function assetAction($key)
    {
        $path = pathinfo($key);
        $serviceName = in_array($path['extension'], ['js', 'css']) ? 'doctrine_cache.providers.zikula_' . $path['extension'] . '_asset_cache' : null;
        $data = $this->get($serviceName)->fetch($key);
        $compress = $this->getParameter('zikula_asset_manager.compress');
        $lifetime = $this->getParameter('zikula_asset_manager.lifetime');
        $lifetime = abs((new \DateTime($lifetime))->getTimestamp() - (new \DateTime())->getTimestamp());
        if ($compress) {
            ini_set('zlib.output_handler', '');
            ini_set('zlib.output_compression', 1);
        }
        $response = new Response();
        $response->setContent($data);
        $response->headers->set('Content-type', $path['extension'] == 'js' ? 'text/javascript' : 'text/css');
        $response->headers->addCacheControlDirective('must-revalidate');
        $response->headers->set('Expires', gmdate("D, d M Y H:i:s", time() + $lifetime) . ' GMT');

        return $response;
    }
}
