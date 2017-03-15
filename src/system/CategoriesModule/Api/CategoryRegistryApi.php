<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Api;

use Doctrine\ORM\EntityManager;
use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;
use Zikula\Common\Translator\TranslatorInterface;

/**
 * CategoryRegistryApi
 */
class CategoryRegistryApi
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
     * @var CategoryApi
     */
    private $categoryApi;

    /**
     * CategoryRegistryApi constructor.
     *
     * @param TranslatorInterface $translator    TranslatorInterface service instance
     * @param EntityManager       $entityManager EntityManager service instance
     * @param CategoryApi         $categoryApi   CategoryApi service instance
     */
    public function __construct(TranslatorInterface $translator, EntityManager $entityManager, CategoryApi $categoryApi)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->categoryApi = $categoryApi;
    }

    /**
     * Delete a category registry entry.
     *
     * @param string  $modName    The module to create a property for
     * @param integer $categoryId The category id to bind this property to
     *
     * @return boolean True on success, false otherwise
     *
     * @throws \InvalidArgumentException Thrown if input arguments are not valid
     */
    public function deleteRegistry($modName, $categoryId = null)
    {
        if (!$modName) {
            throw new \InvalidArgumentException($this->translator->__f("Error! Received invalid parameter '%s'", ['%s' => 'modName']));
        }

        $params = ['modname' => $modName];
        if ($categoryId) {
            $params['id'] = $categoryId;
        }

        $entity = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoryRegistryEntity')->findOneBy($params);
        if ($entity) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        }

        return true;
    }

    /**
     * Create a category registry entry.
     *
     * @param string  $modName    The module to create a property for
     * @param string  $entityName The module entity to create a property for
     * @param string  $property   The property name
     * @param integer $categoryId The category-id to bind this property to
     *
     * @return boolean True on success, false otherwise
     */
    public function insertRegistry($modName, $entityName, $property, $categoryId)
    {
        return $this->processEntry($modName, $entityName, $property, $categoryId);
    }

    /**
     * Update a category registry entry.
     *
     * @param integer $registryId The id of the existing entry we wish to update
     * @param string  $modName    The module to create a property for
     * @param string  $entityName The module entity to create a property for
     * @param string  $property   The property name
     * @param integer $categoryId The category-id to bind this property to
     *
     * @return boolean True on success, false otherwise
     *
     * @throws \InvalidArgumentException Thrown if input arguments are not valid
     */
    public function updateRegistry($registryId, $modName, $entityName, $property, $categoryId)
    {
        if (!$registryId) {
            throw new \InvalidArgumentException($this->translator->__f("Error! Received invalid parameter '%s'", ['%s' => 'registryId']));
        }

        return $this->processEntry($modName, $entityName, $property, $categoryId, $registryId);
    }

    /**
     * Create or update a category registry entry.
     *
     * @param string  $modName    The module to create a property for
     * @param string  $entityName The module entity to create a property for
     * @param string  $property   The property name
     * @param integer $categoryId The category-id to bind this property to
     * @param integer $registryId The id of the existing entry we wish to update (optional) (default=null)
     *
     * @return boolean True on success, false otherwise
     *
     * @throws \InvalidArgumentException Thrown if input arguments are not valid
     */
    private function processEntry($modName, $entityName, $property, $categoryId, $registryId = null)
    {
        if (!$modName) {
            throw new \InvalidArgumentException($this->translator->__f("Error! Received invalid parameter '%s'", ['%s' => 'modName']));
        }
        if (!$entityName) {
            throw new \InvalidArgumentException($this->translator->__f("Error! Received invalid parameter '%s'", ['%s' => 'entityName']));
        }
        if (!$property) {
            throw new \InvalidArgumentException($this->translator->__f("Error! Received invalid parameter '%s'", ['%s' => 'property']));
        }
        if (!$categoryId) {
            throw new \InvalidArgumentException($this->translator->__f("Error! Received invalid parameter '%s'", ['%s' => 'categoryId']));
        }

        $data = [
            'modname' => $modName,
            'entityname' => $entityName,
            'property' => $property,
            'category_id' => $categoryId,
            'id' => $registryId ? $registryId : null
        ];

        return $this->registerModuleCategory($data);
    }

    /**
     * Register a module category.
     *
     * @param array $registryData The array of category map data objects
     *
     * @return boolean True on success, false otherwise
     */
    public function registerModuleCategory($registryData)
    {
        if (!$registryData) {
            return false;
        }

        if (isset($registryData['id']) && $registryData['id']) {
            $entity = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoryRegistryEntity')->find($registryData['id']);
        } else {
            $entity = new CategoryRegistryEntity();
        }
        $categoryId = $registryData['category_id'];
        unset($registryData['category_id']);
        $entity->merge($registryData);
        $entity->setCategory($this->entityManager->getRepository('ZikulaCategoriesModule:CategoryEntity')->find($categoryId));
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Register multiple module categories.
     *
     * @param array $registryDataArray The array of category map data objects
     *
     * @return boolean
     */
    public function registerModuleCategories($registryDataArray)
    {
        if (!$registryDataArray) {
            return false;
        }

        $categoryRegistryRepository = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoryRegistryEntity');
        foreach ($registryDataArray as $registryData) {
            if (isset($registryData['id']) && $registryData['id']) {
                $entity = $categoryRegistryRepository->find($registryData['id']);
                if (null === $entity) {
                    $entity = new CategoryRegistryEntity();
                }
            } else {
                $entity = new CategoryRegistryEntity();
            }
            $categoryId = $registryData['category_id'];
            unset($registryData['category_id']);
            $entity->merge($registryData);
            $entity->setCategory($this->entityManager->getRepository('ZikulaCategoriesModule:CategoryEntity')->find($categoryId));
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();

        return true;
    }

    /**
     * Get the registries for a given module entity.
     *
     * @param string $modName    The module name
     * @param string $entityName The entity name for which we wish to get the property for
     *
     * @return array List of found registry objects
     *
     * @throws \InvalidArgumentException Thrown if input arguments are not valid
     */
    public function getModuleRegistries($modName, $entityName)
    {
        if (!$modName || !$entityName) {
            throw new \InvalidArgumentException($this->translator->__f('Error! Received invalid specifications %1$s, %2$s.', ['%1$s' => $modName, '%2$s' => $entityName]));
        }

        $registries = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoryRegistryEntity')->findBy([
            'modname' => $modName,
            'entityname' => $entityName
        ]);

        return $registries;
    }

    /**
     * Get the registries ids for a given module entity.
     *
     * @param string $modName    The module name
     * @param string $entityName The entity name for which we wish to get the property for
     *
     * @return array The associative field array of register ids for the specified module
     *
     * @throws \InvalidArgumentException Thrown if input arguments are not valid
     */
    public function getModuleRegistriesIds($modName, $entityName)
    {
        if (!$modName || !$entityName) {
            throw new \InvalidArgumentException($this->translator->__f('Error! Received invalid specifications %1$s, %2$s.', ['%1$s' => $modName, '%2$s' => $entityName]));
        }

        $registries = $this->getModuleRegistries($modName, $entityName);

        $result = [];

        foreach ($registries as $registry) {
            $result[$registry['property']] = $registry['id'];
        }

        return $result;
    }

    /**
     * Get registered categories for a module.
     *
     * @param string $modName    The module name
     * @param string $entityName The entity name for which we wish to get the property for
     * @param string $arrayKey   Property name used to index the result array
     *
     * @return array The associative field array of registered categories for the specified module
     *
     * @throws \InvalidArgumentException Thrown if input arguments are not valid
     */
    public function getModuleCategoryIds($modName, $entityName, $arrayKey = 'property')
    {
        if (!$modName || !$entityName) {
            throw new \InvalidArgumentException($this->translator->__f('Error! Received invalid specifications %1$s, %2$s.', ['%1$s' => $modName, '%2$s' => $entityName]));
        }

        static $cache = [];
        if (isset($cache[$modName][$entityName])) {
            return $cache[$modName][$entityName];
        }

        $registries = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoryRegistryEntity')->findBy([
            'modname' => $modName,
            'entityname' => $entityName
        ], ['id' => 'ASC']);

        $result = [];

        /** @var $registry CategoryRegistryEntity */
        foreach ($registries as $registry) {
            $registry = $registry->toArray();
            $result[$registry[$arrayKey]] = $registry['category']->getId();
        }

        $cache[$modName][$entityName] = $result;

        return $result;
    }

    /**
     * Get registered category for module property.
     * @deprecated
     *
     * @param string $modName    The module we wish to get the property for
     * @param string $entityName The entity name for which we wish to get the property for
     * @param string $property   The property name
     * @param string $default    The default value to return if the requested value is not set (optional) (default=null)
     *
     * @return string The associative field array of registered categories for the specified module
     */
    public function getModuleCategoryId($modName, $entityName, $property, $default = null)
    {
        if (!$modName || !$property) {
            return $default;
        }

        $categories = $this->getModuleCategoryIds($modName, $entityName);

        if ($categories && isset($categories[$property]) && $categories[$property]) {
            return $categories[$property];
        }

        // if we have a path default, we get the ID
        if ($default && !is_int($default)) {
            $cat = $this->categoryApi->getCategoryByPath($default);
            if ($cat) {
                $default = $cat['id'];
            }
        }

        return $default;
    }
}
