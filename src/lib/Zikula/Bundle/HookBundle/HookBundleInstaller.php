<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle;

use Doctrine\ORM\EntityManagerInterface;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookBindingEntity;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookRuntimeEntity;
use Zikula\Core\Doctrine\Helper\SchemaHelper;
use Zikula\Core\InstallerInterface;

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
        HookRuntimeEntity::class,
    ];

    /**
     * HookBundleInstaller constructor.
     * @param SchemaHelper $schemaTool
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        SchemaHelper $schemaTool,
        EntityManagerInterface $entityManager
    ) {
        $this->schemaTool = $schemaTool;
        $this->em = $entityManager;
    }

    public function install()
    {
        try {
            $this->schemaTool->create(self::$entities);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        return false;
    }

    public function upgrade($currentCoreVersion)
    {
        // special note, the $currentCoreVersion var will contain the version of the CORE (not this bundle)

        if (version_compare($currentCoreVersion, '2.0.0', '<')) {
            // remove undefined entities
            foreach (['hook_area', 'hook_provider', 'hook_subscriber'] as $table) {
                $sql = "DROP TABLE $table;";
                $connection = $this->em->getConnection();
                $stmt = $connection->prepare($sql);
                $stmt->execute();
                $stmt->closeCursor();
            }
        }
        switch ($currentCoreVersion) {
            case '2.0.0':
                $this->schemaTool->update([HookRuntimeEntity::class]);
            case '2.0.1': //current version
        }

        // Update successful
        return true;
    }
}
