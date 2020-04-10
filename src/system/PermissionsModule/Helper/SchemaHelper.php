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

use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;

class SchemaHelper
{
    /**
     * @var ExtensionRepositoryInterface
     */
    private $extensionRepository;

    public function __construct(ExtensionRepositoryInterface $extensionRepository)
    {
        $this->extensionRepository = $extensionRepository;
    }

    /**
     * Get the security schema for each registered extension.
     * Optionally filter by active status.
     */
    public function getAllSchema(bool $activeOnly = false): array
    {
        $criteria = $activeOnly ? ['state' => Constant::STATE_ACTIVE] : [];
        /** @var ExtensionEntity[] $extensions */
        $extensions = $this->extensionRepository->findBy($criteria);
        $schema = [];
        foreach ($extensions as $extension) {
            if (null === $extension->getSecurityschema()) {
                continue;
            }
            $schema = array_merge($schema, $extension->getSecurityschema());
        }
        uksort($schema, 'strnatcasecmp');

        return $schema;
    }
}
