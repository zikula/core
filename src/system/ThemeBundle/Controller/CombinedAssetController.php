<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeBundle\Controller;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;

#[Route('/theme')]
class CombinedAssetController extends AbstractController
{
    public function __construct(
        private readonly string $lifetime = '1 day',
        private readonly bool $compress = false
    ) {
    }

    #[Route('/combined_asset/{type}/{cacheKey}', name: 'zikulathemebundle_combinedasset_asset')]
    public function asset(ZikulaHttpKernelInterface $kernel, string $type, string $cacheKey): Response
    {
        $lifetimeInSeconds = abs(date_format(new DateTime($this->lifetime), 'U')) - (new DateTime())->getTimestamp();
        $cacheDirectory = $kernel->getCacheDir() . '/assets/' . $type;
        $cacheService = new FilesystemAdapter('combined_assets', $lifetimeInSeconds, $cacheDirectory);
        $cachedFile = $cacheService->get($cacheKey, function () {
            throw new \Exception('Combined Assets not found');
        });

        if ($this->compress && extension_loaded('zlib')) {
            ini_set('zlib.output_handler', '');
            ini_set('zlib.output_compression', 'On');
        }

        $response = new Response($cachedFile);
        $response->headers->set('Content-type', 'js' === $type ? 'text/javascript' : 'text/css');
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $cacheKey);
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->addCacheControlDirective('must-revalidate');
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + $lifetimeInSeconds) . ' GMT');

        return $response;
    }
}
