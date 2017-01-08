<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Helper;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\SettingsModule\Api\LocaleApi;

/**
 * Category processing helper functions for the categories module.
 */
class CategoryProcessingHelper
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var LocaleApi
     */
    private $localeApi;

    /**
     * CategoryProcessingHelper constructor.
     *
     * @param TranslatorInterface $translator TranslatorInterface service instance
     * @param EntityManager $entityManager EntityManager service instance
     * @param KernelInterface $kernel KernelInterface service instance
     * @param LocaleApi $localeApi
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManager $entityManager,
        KernelInterface $kernel,
        LocaleApi $localeApi
    ) {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        $this->localeApi = $localeApi;
    }

    /**
     * Validate the data for a category
     *
     * @param array $data The data for the category
     *
     * @return boolean true/false Whether the provided data is valid
     *
     * @throws \InvalidArgumentException Thrown if no category name is provided or
     *                                          if no parent is defined for the category
     * @throws \RuntimeException Thrown if a category of the same anme already exists under the parent
     */
    public function validateCategoryData($data)
    {
        if (empty($data['name'])) {
            throw new \InvalidArgumentException($this->translator->__('Error! You did not enter a name for the category.'));
        }

        if (empty($data['parent_id'])) {
            throw new \InvalidArgumentException($this->translator->__('Error! You did not provide a parent for the category.'));
        }

        // process name
        $data['name'] = $this->processCategoryName($data['name']);

        if (!isset($data['id']) || !is_numeric($data['id'])) {
            $data['id'] = 0;
        }

        // check that we don't have another category with the same name
        // on the same level
        $existing = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoryEntity')->countForContext($data['name'], $data['parent_id'], $data['id']);
        if ($existing > 0) {
            throw new \RuntimeException($this->translator->__f('Category %s must be unique under parent', ['%s' => $data['name']]));
        }

        return true;
    }

    /**
     * Process the name of a category
     *
     * @param array $name The name of the category
     *
     * @return string the processed name
     */
    public function processCategoryName($name)
    {
        // encode slash in name
        return $name = str_replace('/', '&#47;', $name);
    }

    /**
     * Process the parent of a category
     *
     * @param integer $parentId The parent_id of the category
     *
     * @return CategoryEntity the parent entity
     */
    public function processCategoryParent($parentId)
    {
        return $this->entityManager->getReference('ZikulaCategoriesModule:CategoryEntity', $parentId);
    }

    /**
     * Process the display name of a category
     *
     * @param array $displayName The display name of the category
     * @param array $name        The name of the category
     *
     * @return array the processed display name
     */
    public function processCategoryDisplayName($displayName, $name)
    {
        $languages = $this->localeApi->getSupportedLocales();
        foreach ($languages as $lang) {
            if (!isset($displayName[$lang]) || !$displayName[$lang]) {
                $displayName[$lang] = $name;
            }
        }

        return $displayName;
    }

    /**
     * Process the path of a category
     *
     * @param string $parentPath   The path of the parent category
     * @param string $categoryName The name of the category
     *
     * @return string the category path
     */
    public function processCategoryPath($parentPath, $categoryName)
    {
        return $parentPath . '/' . $categoryName;
    }

    /**
     * Process the ipath of a category
     *
     * @param string $parentIpath  The ipath of the parent category
     * @param string $categoryId   The id of the category
     *
     * @return string the category path
     */
    public function processCategoryIPath($parentIpath, $categoryId)
    {
        return $parentIpath . '/' . $categoryId;
    }

    /**
     * Process the attributes of a category
     *
     * @param CategoryEntity $category     The category to set the attributes for
     * @param array          $attribNames  The attribute names
     * @param array          $attribValues The attribute values
     *
     * @return void
     */
    public function processCategoryAttributes($category, $attribNames, $attribValues)
    {
        // delete attributes
        if (isset($category['attributes'])) {
            foreach ($category['attributes'] as $attribute) {
                if (!in_array($attribute['name'], $attribNames)) {
                    $category->delAttribute($attribute['name']);
                }
            }
        }

        // add/update attributes
        foreach ($attribNames as $attribKey => $attribName) {
            if (!empty($attribName)) {
                $category->setAttribute($attribName, $attribValues[$attribKey]);
            }
        }
    }

    /**
     * Checks whether a category may be deleted or moved.
     * For this all registries are checked for if the given category is contained in the corresponding subtree.
     * If yes, the mapping table of the corresponding module is checked for if it contains the given category.
     *
     * @param CategoryEntity $category The category to process
     *
     * @return boolean true if category may be deleted or moved, false otherwise
     */
    public function mayCategoryBeDeletedOrMoved($category)
    {
        // check legacy table first (as this is quickly done)
        $legacyMappings = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoriesMapobj')
            ->findBy(['categoryId' => $category['id']]);
        if (count($legacyMappings) > 0) {
            return false;
        }

        // fetch registries
        $registries = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoryRegistryEntity')
            ->findAll();

        // iterate over all registries
        foreach ($registries as $registry) {
            // check if the registry subtree contains our category
            $rPath = $registry['category_id']['ipath'] . '/';
            $cPath = $category['ipath'];

            $isContained = strpos($cPath, $rPath) === 0;
            if (!$isContained) {
                continue;
            }

            // get information about responsible module
            if (!$this->kernel->isBundle($registry['modname'])) {
                continue;
            }

            $module = $this->kernel->getModule($registry['modname']);
            $moduleClass = get_class($module);
            $moduleClassLevels = explode('\\', get_class($module));
            unset($moduleClassLevels[count($moduleClassLevels) - 1]);
            $moduleNamespace = implode('\\', $moduleClassLevels);

            // collect module entities
            $entityPath = $module->getRelativePath() . '/Entity/';
            $finder = new Finder();
            $finder->files()->name('*.php')->in($entityPath);
            foreach ($finder as $file) {
                // check if this entity implements category assignments
                include_once $file;
                $entityName = basename($file->getRelativePathname(), '.php');
                $entityClass = $moduleNamespace . '\\Entity\\' . $entityName;
                if (!is_subclass_of($entityClass, 'Zikula\\CategoriesModule\\Entity\\AbstractCategoryAssignment')) {
                    continue;
                }

                // check if this mapping table contains a reference to the given category
                $mappings = $this->entityManager->getRepository($registry['modname'] . ':' . $entityName)
                    ->findBy(['category' => $category['id']]);
                if (count($mappings) > 0) {
                    // existing reference found
                    return false;
                }
            }
        }

        return true;
    }
}
