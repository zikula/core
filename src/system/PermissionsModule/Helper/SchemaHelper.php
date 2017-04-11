<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsModule\Helper;

use Zikula\ExtensionsModule\Api\ExtensionApi;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;

class SchemaHelper
{
    /**
     * @var ExtensionRepositoryInterface
     */
    private $extensionRepository;

    /**
     * SchemaHelper constructor.
     * @param ExtensionRepositoryInterface $extensionRepository
     */
    public function __construct(ExtensionRepositoryInterface $extensionRepository)
    {
        $this->extensionRepository = $extensionRepository;
    }

    /**
     * Get the security schema for each registered extension
     * Optionally filter by active status
     * @param bool $activeOnly
     * @return array
     */
    public function getAllSchema($activeOnly = false)
    {
        $criteria = $activeOnly ? ['state' => ExtensionApi::STATE_ACTIVE] : [];
        /** @var ExtensionEntity[] $extensions */
        $extensions = $this->extensionRepository->findBy($criteria);
        $schema = [];
        foreach ($extensions as $extension) {
            if (null !== $extension->getSecurityschema()) {
                $schema = array_merge($schema, $extension->getSecurityschema());
            }
        }
        uksort($schema, 'strnatcasecmp');

        return $schema;
    }
}
