<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Util;

use Symfony\Component\Intl\Exception\MethodArgumentValueNotImplementedException;

/**
 * Portions of this class copied from or inspired by the Symfony Installer (@see https://github.com/symfony/symfony-installer)
 * Class ZikulaRequirements
 */
class ZikulaRequirements
{
    public $requirementsErrors = [];

    public function runSymfonyChecks($parameters = [])
    {
        try {
            $path = realpath(__DIR__.'/../../../../../var/SymfonyRequirements.php');
            require $path;
            $symfonyRequirements = new \SymfonyRequirements();
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

    protected function getErrorMessage(\Requirement $requirement, $lineSize = 70)
    {
        if ($requirement->isFulfilled()) {
            return;
        }
        $errorMessage = wordwrap($requirement->getTestMessage(), $lineSize - 3, PHP_EOL.'   ').PHP_EOL;
        $errorMessage .= '   > '.wordwrap($requirement->getHelpText(), $lineSize - 5, PHP_EOL.'   > ').PHP_EOL;

        return $errorMessage;
    }

    private function addZikulaPathRequirements(\SymfonyRequirements $symfonyRequirements, $parameters)
    {
        $src = realpath(__DIR__ . '/../../../../../');
        $symfonyRequirements->addRequirement(
            is_writable($src . '/app/config'),
            'app/config/ directory must be writable',
            'Change the permissions of "<strong>app/config/</strong>" directory so that the web server can write into it.'
        );
        $symfonyRequirements->addRequirement(
            is_writable($src . '/app/config/dynamic'),
            'app/config/dynamic/ directory must be writable',
            'Change the permissions of "<strong>app/config/dynamic/</strong>" directory so that the web server can write into it.'
        );
        $symfonyRequirements->addRequirement(
            is_writable($src . '/' . $parameters['datadir']),
            $parameters['datadir'] . '/ directory must be writable',
            'Change the permissions of "<strong>' . $parameters['datadir']. '</strong>" directory so that the web server can write into it.'
        );
    }
}
