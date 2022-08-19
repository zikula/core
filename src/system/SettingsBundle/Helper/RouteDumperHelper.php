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

namespace Zikula\SettingsBundle\Helper;

use FOS\JsRoutingBundle\Command\DumpCommand;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;

class RouteDumperHelper
{
    public function __construct(
        private readonly ZikulaHttpKernelInterface $kernel,
        private readonly Filesystem $fileSystem,
        private readonly TranslatorInterface $translator,
        private readonly DumpCommand $dumpCommand
    ) {
    }

    /**
     * Dumps exposed JS routes to '/public/js/fos_js_routes.js'.
     */
    public function dumpJsRoutes(): string
    {
        $errors = '';
        $format = 'js';
        $domain = '';

        // force deletion of existing file
        $targetPath = sprintf(
            '%s/public/js/fos_js_routes%s.%s',
            $this->kernel->getProjectDir(),
            empty($domain) ? '' : ('_' . implode('_', $domain)),
            $format
        );
        if ($this->fileSystem->exists($targetPath)) {
            try {
                $this->fileSystem->remove($targetPath);
            } catch (IOExceptionInterface $exception) {
                $errors .= $this->translator->trans('Error: Could not delete "%path%" because %message%.', [
                    '%path%' => $targetPath,
                    '%message%' => $exception->getMessage()
                ]);
            }
        }

        // call dump command
        $input = new ArrayInput([
            '--format' => $format,
            '--target' => $targetPath
        ]);
        $output = new NullOutput();
        try {
            $this->dumpCommand->run($input, $output);
        } catch (RuntimeException $exception) {
            $errors .= $exception->getMessage() . '. ';
        }

        return $errors;
    }
}
