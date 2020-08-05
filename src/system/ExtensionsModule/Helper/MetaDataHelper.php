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

namespace Zikula\ExtensionsModule\Helper;

use Zikula\Bundle\CoreBundle\Composer\MetaData;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ExtensionsModule\Entity\Repository\ExtensionRepository;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;

class MetaDataHelper
{
    /**
     * @var ExtensionRepository
     */
    private $extensionRepository;

    /**
     * @var bool
     */
    private $installed;

    public function __construct(
        ExtensionRepositoryInterface $extensionRepository,
        string $installed
    ) {
        $this->extensionRepository = $extensionRepository;
        $this->installed = ZikulaKernel::VERSION === $installed;
    }

    /**
     * overwrite composer.json settings with dynamic values from extension repository
     */
    public function setDynamicMetaData(MetaData $metaData): MetaData
    {
        if ($this->installed) {
            $extensionEntity = $this->extensionRepository->get($metaData->getShortName());
            if (null !== $extensionEntity) {
                $metaData->setUrl($extensionEntity->getUrl());
                $metaData->setDisplayName($extensionEntity->getDisplayname());
                $metaData->setDescription($extensionEntity->getDescription());
                $metaData->setIcon($extensionEntity->getIcon());
            }
        }

        return $metaData;
    }
}
