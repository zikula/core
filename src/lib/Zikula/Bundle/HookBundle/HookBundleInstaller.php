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
     * HookBundleInstaller constructor.
     * @param $schemaTool
     */
    public function __construct(SchemaHelper $schemaTool)
    {
        $this->schemaTool = $schemaTool;
    }

    public function install()
    {
        $entities = [
            'Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity',
            'Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookBindingEntity',
            'Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookProviderEntity',
            'Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookRuntimeEntity',
            'Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookSubscriberEntity',
        ];

        try {
            $this->schemaTool->create($entities);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        return false;
    }

    public function upgrade($previousVersion)
    {
        return true;
    }
}
