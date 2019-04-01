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

namespace Zikula\Bundle\CoreBundle\DataCollector;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ThemeModule\Engine\Engine;

class ZikulaVersionDataCollector extends DataCollector
{
    /**
     * @var Engine
     */
    private $themeEngine;

    public function __construct(Engine $themeEngine)
    {
        $this->themeEngine = $themeEngine;
    }

    public function collect(Request $request, Response $response, Exception $exception = null): void
    {
        $this->data = [
            'version' => ZikulaKernel::VERSION,
            'ghZikulaCoreUrl' => 'https://github.com/zikula/core',
            'ghZikulaDocsUrl' => 'https://github.com/zikula/zikula-docs',
            'ghZikulaBootstrapDocsUrl' => 'https://zikula.github.io/bootstrap-docs'
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
        return $this->data['version'];
    }

    public function getGhZikulaCoreUrl(): string
    {
        return $this->data['ghZikulaCoreUrl'];
    }

    public function getGhZikulaDocsUrl(): string
    {
        return $this->data['ghZikulaDocsUrl'];
    }

    public function getGhZikulaBootstrapDocsUrl(): string
    {
        return $this->data['ghZikulaBootstrapDocsUrl'];
    }

    public function getThemeEngine(): array
    {
        return $this->data['themeEngine'];
    }

    public function getName(): string
    {
        return 'zikula_version';
    }
}
