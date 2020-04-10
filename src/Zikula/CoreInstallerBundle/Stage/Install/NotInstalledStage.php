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

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Install;

use Zikula\Component\Wizard\StageInterface;

class NotInstalledStage implements StageInterface
{
    public function getName(): string
    {
        return 'notinstalled';
    }

    public function getTemplateName(): string
    {
        return '@ZikulaCoreInstaller/Install/notinstalled.html.twig';
    }

    public function isNecessary(): bool
    {
        return true;
    }

    public function getTemplateParams(): array
    {
        return [];
    }
}
