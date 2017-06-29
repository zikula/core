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

use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookBindingEntity;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookProviderEntity;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookRuntimeEntity;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookSubscriberEntity;
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

    private static $entities = [
        HookAreaEntity::class, // @deprecated
        HookBindingEntity::class,
        HookProviderEntity::class, // @deprecated
        HookRuntimeEntity::class,
        HookSubscriberEntity::class, // @deprecated
    ];

    /**
     * HookBundleInstaller constructor.
     * @param $schemaTool
     */
    public function __construct(SchemaHelper $schemaTool)
    {
        $this->schemaTool = $schemaTool;
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

        if (version_compare($currentCoreVersion, '1.5.0', '<')) {
            $this->schemaTool->update(self::$entities);
            // @todo update numeric id to areaname string
            // \Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookRuntimeEntity::$sareaid
            // \Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookRuntimeEntity::$pareaid
            // \Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookBindingEntity::$sareaid
            // \Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookBindingEntity::$pareaid
            // \Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookSubscriberEntity::$sareaid
            // \Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookProviderEntity::$pareaid
            // @todo should we remove subsowner and subpowner properties entirely?
        }
        // @todo at Core-2.0 remove deprecated entities

        // Update successful
        return true;
    }
}
