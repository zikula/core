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

namespace Zikula\CategoriesBundle\Initializer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Contracts\Translation\TranslatorInterface;
use Translation\Extractor\Annotation\Ignore;
use Zikula\Bundle\CoreBundle\Api\ApiInterface\LocaleApiInterface;
use Zikula\Bundle\CoreBundle\BundleInitializer\BundleInitializerInterface;
use Zikula\CategoriesBundle\Entity\CategoryEntity;
use Zikula\UsersBundle\Entity\UserEntity;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;
use Zikula\UsersBundle\UsersConstant;

class CategoriesInitializer implements BundleInitializerInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepositoryInterface $userRepository,
        private readonly LocaleApiInterface $localeApi
    ) {
    }

    public function init(): void
    {
        /**
         * explicitly set admin as user to be set as `updatedBy` and `createdBy` fields. Normally this would be taken care of
         * by the BlameListener but during installation from the CLI this listener is not available
         */
        /** @var UserEntity $adminUser */
        $adminUser = $this->userRepository->find(UsersConstant::USER_ID_ADMIN);

        $this->insertDefaultData($adminUser);

        // Set autonumber to 10000 (for DB's that support autonumber fields)
        $category = (new CategoryEntity())
            ->setId(9999)
            ->setUpdatedBy($adminUser)
            ->setCreatedBy($adminUser);
        $this->entityManager->persist($category);
        $this->entityManager->flush();
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    private function insertDefaultData(UserEntity $adminUser): void
    {
        $categoryData = $this->getDefaultCategoryData();
        $categoryObjectMap = [];
        /** @var ClassMetadata */
        $categoryMetaData = $this->entityManager->getClassMetaData(CategoryEntity::class);
        // disable auto-generation of keys to allow manual setting from this data set.
        $categoryMetaData->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_NONE);

        foreach ($categoryData as $data) {
            $data['parent'] = 0 < $data['parent_id'] && isset($categoryObjectMap[$data['parent_id']]) ? $categoryObjectMap[$data['parent_id']] : null;
            unset($data['parent_id']);
            $attributes = $data['attributes'] ?? [];
            unset($data['attributes']);

            $category = (new CategoryEntity())
                ->setId($data['id'])
                ->setParent($data['parent'])
                ->setLocked($data['locked'])
                ->setLeaf($data['leaf'])
                ->setName($data['name'])
                ->setValue($data['value'])
                ->setDisplayName($data['displayName'])
                ->setDisplayDesc($data['displayDesc'])
                ->setStatus($data['status'])
                ->setCreatedBy($adminUser)
                ->setUpdatedBy($adminUser);
            $this->entityManager->persist($category);

            $categoryObjectMap[$data['id']] = $category;

            if (isset($attributes)) {
                foreach ($attributes as $key => $value) {
                    $category->setAttribute($key, $value);
                }
            }
        }

        $this->entityManager->flush();
        $categoryMetaData->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_AUTO);
    }

    private function getDefaultCategoryData(): array
    {
        $categoryData = [];
        $categoryData[] = [
            'id' => 1,
            'parent_id' => 0,
            'locked' => true,
            'leaf' => false,
            'value' => '',
            'name' => '__SYSTEM__',
            'displayName' => $this->localize($this->translator->trans('Category root')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 2,
            'parent_id' => 1,
            'locked' => false,
            'leaf' => false,
            'value' => '',
            'name' => 'Modules',
            'displayName' => $this->localize($this->translator->trans('Modules')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 3,
            'parent_id' => 1,
            'locked' => false,
            'leaf' => false,
            'value' => '',
            'name' => 'General',
            'displayName' => $this->localize($this->translator->trans('General')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 10,
            'parent_id' => 3,
            'locked' => false,
            'leaf' => false,
            'value' => '',
            'name' => 'Publication Status (extended)',
            'displayName' => $this->localize($this->translator->trans('Publication status (extended)')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 11,
            'parent_id' => 10,
            'locked' => false,
            'leaf' => true,
            'value' => 'P',
            'name' => 'Pending',
            'displayName' => $this->localize($this->translator->trans('Pending')),
            'displayDesc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'P']
        ];
        $categoryData[] = [
            'id' => 12,
            'parent_id' => 10,
            'locked' => false,
            'leaf' => true,
            'value' => 'C',
            'name' => 'Checked',
            'displayName' => $this->localize($this->translator->trans('Checked')),
            'displayDesc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'C']
        ];
        $categoryData[] = [
            'id' => 13,
            'parent_id' => 10,
            'locked' => false,
            'leaf' => true,
            'value' => 'A',
            'name' => 'Approved',
            'displayName' => $this->localize($this->translator->trans('Approved')),
            'displayDesc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'A']
        ];
        $categoryData[] = [
            'id' => 14,
            'parent_id' => 10,
            'locked' => false,
            'leaf' => true,
            'value' => 'O',
            'name' => 'On-line',
            'displayName' => $this->localize($this->translator->trans('On-line')),
            'displayDesc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'O']
        ];
        $categoryData[] = [
            'id' => 15,
            'parent_id' => 10,
            'locked' => false,
            'leaf' => true,
            'value' => 'R',
            'name' => 'Rejected',
            'displayName' => $this->localize($this->translator->trans('Rejected')),
            'displayDesc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'R']
        ];
        $categoryData[] = [
            'id' => 25,
            'parent_id' => 3,
            'locked' => false,
            'leaf' => false,
            'value' => '',
            'name' => 'ActiveStatus',
            'displayName' => $this->localize($this->translator->trans('Activity status')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 26,
            'parent_id' => 25,
            'locked' => false,
            'leaf' => true,
            'value' => 'A',
            'name' => 'Active',
            'displayName' => $this->localize($this->translator->trans('Active')),
            'displayDesc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'A']
        ];
        $categoryData[] = [
            'id' => 27,
            'parent_id' => 25,
            'locked' => false,
            'leaf' => true,
            'value' => 'I',
            'name' => 'Inactive',
            'displayName' => $this->localize($this->translator->trans('Inactive')),
            'displayDesc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'I']
        ];
        $categoryData[] = [
            'id' => 28,
            'parent_id' => 3,
            'locked' => false,
            'leaf' => false,
            'value' => '',
            'name' => 'Publication status (basic)',
            'displayName' => $this->localize($this->translator->trans('Publication status (basic)')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 29,
            'parent_id' => 28,
            'locked' => false,
            'leaf' => true,
            'value' => 'P',
            'name' => 'Pending',
            'displayName' => $this->localize($this->translator->trans('Pending')),
            'displayDesc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'P']
        ];
        $categoryData[] = [
            'id' => 30,
            'parent_id' => 28,
            'locked' => false,
            'leaf' => true,
            'value' => 'A',
            'name' => 'Approved',
            'displayName' => $this->localize($this->translator->trans('Approved')),
            'displayDesc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'A']
        ];
        $categoryData[] = [
            'id' => 32,
            'parent_id' => 2,
            'locked' => false,
            'leaf' => false,
            'value' => '',
            'name' => 'Global',
            'displayName' => $this->localize($this->translator->trans('Global')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 33,
            'parent_id' => 32,
            'locked' => false,
            'leaf' => true,
            'value' => '',
            'name' => 'Blogging',
            'displayName' => $this->localize($this->translator->trans('Blogging')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 34,
            'parent_id' => 32,
            'locked' => false,
            'leaf' => true,
            'value' => '',
            'name' => 'Music and audio',
            'displayName' => $this->localize($this->translator->trans('Music and audio')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 35,
            'parent_id' => 32,
            'locked' => false,
            'leaf' => true,
            'value' => '',
            'name' => 'Art and photography',
            'displayName' => $this->localize($this->translator->trans('Art and photography')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 36,
            'parent_id' => 32,
            'locked' => false,
            'leaf' => true,
            'value' => '',
            'name' => 'Writing and thinking',
            'displayName' => $this->localize($this->translator->trans('Writing and thinking')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 37,
            'parent_id' => 32,
            'locked' => false,
            'leaf' => true,
            'value' => '',
            'name' => 'Communications and media',
            'displayName' => $this->localize($this->translator->trans('Communications and media')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 38,
            'parent_id' => 32,
            'locked' => false,
            'leaf' => true,
            'value' => '',
            'name' => 'Travel and culture',
            'displayName' => $this->localize($this->translator->trans('Travel and culture')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 39,
            'parent_id' => 32,
            'locked' => false,
            'leaf' => true,
            'value' => '',
            'name' => 'Science and technology',
            'displayName' => $this->localize($this->translator->trans('Science and technology')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 40,
            'parent_id' => 32,
            'locked' => false,
            'leaf' => true,
            'value' => '',
            'name' => 'Sport and activities',
            'displayName' => $this->localize($this->translator->trans('Sport and activities')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 41,
            'parent_id' => 32,
            'locked' => false,
            'leaf' => true,
            'value' => '',
            'name' => 'Business and work',
            'displayName' => $this->localize($this->translator->trans('Business and work')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];

        return $categoryData;
    }

    private function localize(string $value = ''): array
    {
        $values = [];
        foreach ($this->localeApi->getSupportedLocales() as $code) {
            $values[$code] = $this->translator->trans(/** @Ignore */ $value, [], 'zikula', $code);
        }

        return $values;
    }
}
