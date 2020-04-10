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

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Upgrade;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zikula\Bundle\CoreInstallerBundle\Helper\MigrationHelper;
use Zikula\Component\Wizard\StageInterface;

class InitStage implements StageInterface
{
    /**
     * @var MigrationHelper
     */
    private $migrationHelper;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var string
     */
    private $installed;

    /**
     * @var int
     */
    private $count;

    public function __construct(
        MigrationHelper $migrationHelper,
        SessionInterface $session,
        string $installed
    ) {
        $this->migrationHelper = $migrationHelper;
        $this->session = $session;
        $this->installed = $installed;
    }

    public function getName(): string
    {
        return 'init';
    }

    public function getTemplateName(): string
    {
        return '@ZikulaCoreInstaller/Migration/migrate.html.twig';
    }

    public function isNecessary(): bool
    {
        if (version_compare($this->installed, '2.0.0', '>=')) {
            return false;
        }

        $this->count = $this->migrationHelper->countUnMigratedUsers();
        if ($this->count > 0) {
            $this->session->set('user_migration_count', $this->count);
            $this->session->set('user_migration_complete', 0);
            $this->session->set('user_migration_lastuid', 0);
            $this->session->set('user_migration_maxuid', $this->migrationHelper->getMaxUnMigratedUid());

            return true;
        }

        return false;
    }

    public function getTemplateParams(): array
    {
        return [
            'count' => $this->count
        ];
    }
}
