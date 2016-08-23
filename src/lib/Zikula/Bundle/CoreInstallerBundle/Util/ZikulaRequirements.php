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

    public function runSymfonyChecks()
    {
        try {
            $path = realpath(__DIR__.'/../../../../../app/SymfonyRequirements.php');
            require $path;
            $symfonyRequirements = new \SymfonyRequirements();
            $symfonyRequirements->addRequirement(
                function_exists('mb_get_info'),
                'mbstring is not installed in PHP.  Zikula cannot install or upgrade without this extension.',
                'Install and enable the <strong>mbstring</strong> extension'
            );
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
}
