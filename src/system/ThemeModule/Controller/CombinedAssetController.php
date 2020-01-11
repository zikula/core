<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Controller;

use DateTime;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Core\Controller\AbstractController;

class CombinedAssetController extends AbstractController
{
    /**
     * @Route("/combined_asset/{type}/{key}", options={"i18n"=false})
     */
    public function assetAction(
        ZikulaHttpKernelInterface $kernel,
        string $type,
        string $key
    ): Response {
        $lifetimeInSeconds = abs(date_format(new DateTime($this->getParameter('zikula_asset_manager.lifetime')), 'U')) - (new DateTime())->getTimestamp();
        $cacheService = new FilesystemAdapter(
            'combined_assets',
            $lifetimeInSeconds,
            $kernel->getCacheDir() . '/assets/' . $type);
        $cachedFile = $cacheService->get($key, function() {
            throw new \Exception('Combined Assets not found');
        });

        $compress = $this->getParameter('zikula_asset_manager.compress');
        if ($compress && extension_loaded('zlib')) {
            ini_set('zlib.output_handler', '');
            ini_set('zlib.output_compression', '1');
        }

        $response = new Response($cachedFile);
        $response->headers->set('Content-type', 'js' === $type ? 'text/javascript' : 'text/css');
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $key);
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->addCacheControlDirective('must-revalidate');
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + $lifetimeInSeconds) . ' GMT');

        return $response;
    }
}
