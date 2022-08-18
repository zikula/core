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

namespace Zikula\Bundle\CoreBundle\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ThemeBundle\Engine\Engine;

class ZikulaVersionDataCollector extends DataCollector
{
    public function __construct(protected readonly Engine $themeEngine)
    {
    }

    public function collect(Request $request, Response $response, \Throwable $exception = null): void
    {
        $this->data = [
            'version' => ZikulaKernel::VERSION,
            'ghZikulaCoreUrl' => 'https://github.com/zikula/core',
            'ghZikulaDocsUrl' => 'https://docs.ziku.la/'
        ];
        if (null !== $this->themeEngine->getTheme()) {
            $this->data['themeEngine'] = [
                'theme' => $this->themeEngine->getTheme()->getName(),
                'realm' => $this->themeEngine->getRealm(),
                'annotation' => $this->themeEngine->getAnnotationValue(),
            ];
        } else {
            $this->data['themeEngine'] = [
                'theme' => '',
                'realm' => '',
                'annotation' => '',
            ];
        }
    }

    public function reset(): void
    {
        $this->data = [];
    }

    public function getVersion(): string
    {
        return $this->data['version'] ?? '';
    }

    public function getGhZikulaCoreUrl(): string
    {
        return $this->data['ghZikulaCoreUrl'] ?? '';
    }

    public function getGhZikulaDocsUrl(): string
    {
        return $this->data['ghZikulaDocsUrl'] ?? '';
    }

    public function getThemeEngine(): array
    {
        return $this->data['themeEngine'] ?? [];
    }

    public function getName(): string
    {
        return 'zikula_version';
    }
}
