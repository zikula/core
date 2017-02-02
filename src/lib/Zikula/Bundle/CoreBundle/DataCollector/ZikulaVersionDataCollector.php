<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\DataCollector;

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

    /**
     * ZikulaVersionDataCollector constructor.
     * @param $themeEngine
     */
    public function __construct(Engine $themeEngine)
    {
        $this->themeEngine = $themeEngine;
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = [
            'version' => ZikulaKernel::VERSION,
            'ghZikulaCoreUrl' => 'https://www.github.com/zikula/core',
            'ghZikulaDocsUrl' => 'https://www.github.com/zikula/zikula-docs',
            'ghZikulaBootstrapDocsUrl' => 'http://zikula.github.io/bootstrap-docs'
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

    public function getVersion()
    {
        return $this->data['version'];
    }

    public function getGhZikulaCoreUrl()
    {
        return $this->data['ghZikulaCoreUrl'];
    }

    public function getGhZikulaDocsUrl()
    {
        return $this->data['ghZikulaDocsUrl'];
    }

    public function getGhZikulaBootstrapDocsUrl()
    {
        return $this->data['ghZikulaBootstrapDocsUrl'];
    }

    public function getThemeEngine()
    {
        return $this->data['themeEngine'];
    }

    public function getName()
    {
        return 'zikula_version';
    }
}
