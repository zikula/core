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

namespace Zikula\Bundle\CoreInstallerBundle\Bridge\HookBundle;

use Doctrine\ORM\EntityManagerInterface;
use Zikula\Bundle\CoreBundle\Doctrine\Helper\SchemaHelper;
use Zikula\Bundle\HookBundle\Entity\Connection;
use Zikula\Bundle\HookBundle\Entity\HookBindingEntity;
use Zikula\Bundle\HookBundle\Entity\HookRuntimeEntity;
use Zikula\ExtensionsModule\Installer\InstallerInterface;

class HookBundleInstaller implements InstallerInterface
{
    /**
     * @var SchemaHelper
     */
    private $schemaTool;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    private static $entities = [
        HookBindingEntity::class, // @deprecated remove this line at Core 4.0.0
        HookRuntimeEntity::class, // @deprecated remove this line at Core 4.0.0
        Connection::class
    ];

    public function __construct(
        SchemaHelper $schemaTool,
        EntityManagerInterface $entityManager
    ) {
        $this->schemaTool = $schemaTool;
        $this->em = $entityManager;
    }

    public function install(): bool
    {
        $this->schemaTool->create(self::$entities);

        return true;
    }

    public function uninstall(): bool
    {
        return false;
    }

    public function upgrade(string $currentCoreVersion): bool
    {
        // special note, the $currentCoreVersion var will contain the version of the CORE (not this bundle)

        if (version_compare($currentCoreVersion, '2.0.0', '<')) {
            // remove all old hook-related tables
            $oldTables = [
                'hook_area',
                'hook_provider',
                'hook_subscriber',
                'hook_binding',
                'hook_runtime'
            ];
            foreach ($oldTables as $table) {
                $sql = "DROP TABLE ${table};";
                $connection = $this->em->getConnection();
                $stmt = $connection->prepare($sql);
                $stmt->execute();
                $stmt->closeCursor();
            }
            $this->schemaTool->create(self::$entities); // create new versions of the tables for Core-2.0.0
        }
        switch ($currentCoreVersion) {
            case '2.0.0':
                $this->schemaTool->update([
                    HookRuntimeEntity::class
                ]);
                // no break
            case '2.0.1':
                // nothing
            case '3.1.0'://current version
                // allow old tables to remain on upgrade, just add the new one
                $this->schemaTool->create(self::$entities);
        }

        // Update successful
        return true;
    }
}
