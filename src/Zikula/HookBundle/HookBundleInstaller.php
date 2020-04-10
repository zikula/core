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

namespace Zikula\Bundle\HookBundle;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Zikula\Bundle\CoreBundle\Doctrine\Helper\SchemaHelper;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookBindingEntity;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookRuntimeEntity;
use Zikula\ExtensionsModule\Installer\InstallerInterface;

/**
 * Class HookBundleInstaller
 */
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
        HookBindingEntity::class,
        HookRuntimeEntity::class
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
        try {
            $this->schemaTool->create(self::$entities);
        } catch (Exception $exception) {
            return false;
        }

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
            // remove undefined entities
            foreach (['hook_area', 'hook_provider', 'hook_subscriber'] as $table) {
                $sql = "DROP TABLE ${table};";
                $connection = $this->em->getConnection();
                $stmt = $connection->prepare($sql);
                $stmt->execute();
                $stmt->closeCursor();
            }
        }
        switch ($currentCoreVersion) {
            case '2.0.0':
                $this->schemaTool->update([
                    HookRuntimeEntity::class
                ]);
            case '2.0.1': //current version
        }

        // Update successful
        return true;
    }
}
