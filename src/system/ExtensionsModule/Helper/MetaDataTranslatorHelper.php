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

use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Composer\MetaData;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\ExtensionsModule\Entity\Repository\ExtensionRepository;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;

class MetaDataTranslatorHelper
{
    use TranslatorTrait;

    /**
     * @var ExtensionRepository
     */
    private $extensionRepository;

    /**
     * @var bool
     */
    private $installed;

    public function __construct(
        TranslatorInterface $translator,
        ExtensionRepositoryInterface $extensionRepository,
        string $installed
    ) {
        $this->setTranslator($translator);
        $this->extensionRepository = $extensionRepository;
        $this->installed = ZikulaKernel::VERSION === $installed;
    }

    /**
     * overwrite composer.json settings with dynamic values from extension repository
     */
    public function translateMetaData(MetaData $metaData): MetaData
    {
        if ($this->installed && isset($this->translator)) {
            $extensionEntity = $this->extensionRepository->get($metaData->getShortName());
            if (null !== $extensionEntity) {
                $metaData->setTranslator($this->translator);
                $metaData->setUrl($extensionEntity->getUrl());
                $metaData->setDisplayName($extensionEntity->getDisplayname());
                $metaData->setDescription($extensionEntity->getDescription());
                $metaData->setIcon($extensionEntity->getIcon());
            }
        }

        return $metaData;
    }
}
