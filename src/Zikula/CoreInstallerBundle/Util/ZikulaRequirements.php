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

namespace Zikula\Bundle\CoreInstallerBundle\Util;

use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Intl\Exception\MethodArgumentValueNotImplementedException;
use Symfony\Requirements\Requirement;
use Symfony\Requirements\SymfonyRequirements;

/**
 * Portions of this class copied from or inspired by the Symfony Installer (@see https://github.com/symfony/symfony-installer)
 * Class ZikulaRequirements
 */
class ZikulaRequirements
{
    /**
     * @var array
     */
    public $requirementsErrors = [];

    public function runSymfonyChecks(array $parameters = []): void
    {
        try {
            $symfonyRequirements = new SymfonyRequirements($parameters['kernel.project_dir'], '5.0.0');
            $this->addZikulaPathRequirements($symfonyRequirements, $parameters);

            foreach ($symfonyRequirements->getRequirements() as $req) {
                if ($helpText = $this->getErrorMessage($req)) {
                    $this->requirementsErrors[] = $helpText;
                }
            }
        } catch (MethodArgumentValueNotImplementedException $e) {
            // workaround https://github.com/symfony/symfony-installer/issues/163
        }
    }

    protected function getErrorMessage(Requirement $requirement, $lineSize = 70): string
    {
        if ($requirement->isFulfilled()) {
            return '';
        }
        $errorMessage = wordwrap($requirement->getTestMessage(), $lineSize - 3, PHP_EOL . '   ') . PHP_EOL;
        $errorMessage .= '   > ' . wordwrap($requirement->getHelpText(), $lineSize - 5, PHP_EOL . '   > ') . PHP_EOL;

        return $errorMessage;
    }

    private function addZikulaPathRequirements(SymfonyRequirements $symfonyRequirements, array $parameters = []): void
    {
        $fileSystem = new Filesystem();
        $projectDir = $parameters['kernel.project_dir'];
        $symfonyRequirements->addRequirement(
            is_writable($projectDir . '/config'),
            'config/ directory must be writable',
            'Change the permissions of "<strong>config/</strong>" directory so that the web server can write into it.'
        );
        $symfonyRequirements->addRequirement(
            is_writable($projectDir . '/config/dynamic'),
            'config/dynamic/ directory must be writable',
            'Change the permissions of "<strong>config/dynamic/</strong>" directory so that the web server can write into it.'
        );
        $dataDir = $projectDir . '/' . $parameters['datadir'];
        if (!is_dir($dataDir)) {
            $fileSystem->mkdir($dataDir);
        }
        $symfonyRequirements->addRequirement(
            is_writable($dataDir),
            $parameters['datadir'] . '/ directory must be writable',
            'Change the permissions of "<strong>' . $parameters['datadir'] . '</strong>" directory so that the web server can write into it.'
        );
        $customParametersPath = $projectDir . '/config/services_custom.yaml';
        if (file_exists($customParametersPath)) {
            $symfonyRequirements->addRequirement(
                is_writable($customParametersPath),
                'config/services_custom.yaml file must be writable',
                'Change the permissions of "<strong>config/services_custom.yaml</strong>" so that the web server can write into it.'
            );
        }
        $customEnvVarsPath = $projectDir . '/.env.local';
        if (!file_exists($customEnvVarsPath)) {
            // try to create the file
            try {
                $fileSystem->touch($customEnvVarsPath);
            } catch (IOExceptionInterface $exception) {
                $symfonyRequirements->addRequirement(
                    false,
                    '.env.local file must exists',
                    'Create an empty file "<strong>.env.local</strong>" in the root folder.'
                );
            }
        }
        if (file_exists($customEnvVarsPath)) {
            $content = file_get_contents($customEnvVarsPath);
            if (false === mb_strpos($content, 'DATABASE_URL')) {
                // no database credentials are set yet
                try {
                    $fileSystem->dumpFile($customEnvVarsPath, 'Test');
                    $fileSystem->dumpFile($customEnvVarsPath, '');
                } catch (IOExceptionInterface $exception) {
                    $symfonyRequirements->addRequirement(
                        false,
                        '.env.local file must be writable',
                        'Change the permissions of "<strong>.env.local</strong>" so that the web server can write into it.'
                    );
                }
            }
        }
    }
}
