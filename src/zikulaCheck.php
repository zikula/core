<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Intl\Exception\MethodArgumentValueNotImplementedException;

class ZikulaRequirementsCheck
{
    public $requirementsErrors = [];

    public function runSymfonyChecks()
    {
        try {
            require 'app/SymfonyRequirements.php';
            $symfonyRequirements = new \SymfonyRequirements();
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

echo "BEGINNING CHECKS</br>";
$checker = new ZikulaRequirementsCheck();
$checker->runSymfonyChecks();
foreach ($checker->requirementsErrors as $error) {
    echo $error . '<br/>';
}

echo "END CHECKS</br>";
