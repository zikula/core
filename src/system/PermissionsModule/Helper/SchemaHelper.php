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

namespace Zikula\PermissionsModule\Helper;

use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\AbstractExtension;

class SchemaHelper
{
    public function __construct(private readonly ZikulaHttpKernelInterface $kernel)
    {
    }

    /**
     * Get the security schema for each registered extension.
     */
    public function getAllSchema(): array
    {
        $criteria = $activeOnly ? ['state' => Constant::STATE_ACTIVE] : [];
        $bundles = $this->kernel->getBundles();
        $schema = [];
        foreach ($bundles as $bundle) {
            if (!($bundle instanceof AbstractExtension)) {
                continue;
            }
            if (null === ($bundleSchema = $bundle->getMetaData()->getSecurityschema())) {
                continue;
            }
            $schema = array_merge($schema, $bundleSchema);
        }
        uksort($schema, 'strnatcasecmp');

        return $schema;
    }
}
