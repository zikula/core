<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Api;

use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Entity\ExtensionVarEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionVarRepositoryInterface;

/**
 * Class VariableApi
 *
 * This class manages the storage and retrieval of extension variables
 */
class VariableApi implements VariableApiInterface
{
    const CONFIG = 'ZConfig';

    private $isInitialized = false;

    /**
     * @var boolean Site is installed or not
     */
    private $installed;

    /**
     * @var ExtensionVarRepositoryInterface
     */
    private $repository;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var array
     */
    private $protectedSystemVars;

    /**
     * @var array
     */
    private $variables;

    /**
     * VariableApi constructor.
     *
     * @param $installed
     * @param ExtensionVarRepositoryInterface $repository
     * @param ZikulaHttpKernelInterface $kernel
     * @param array $multisitesParameters
     */
    public function __construct(
        $installed,
        ExtensionVarRepositoryInterface $repository,
        ZikulaHttpKernelInterface $kernel,
        array $multisitesParameters
    ) {
        $this->installed = $installed;
        $this->repository = $repository;
        $this->kernel = $kernel;
        $this->protectedSystemVars = $multisitesParameters['protected.systemvars'];
    }

    /**
     * Loads extension vars for all extensions to reduce sql statements.
     *
     * @return void
     */
    private function initialize()
    {
        if (!$this->installed) {
            return;
        }

        // The empty arrays for handlers and settings are required to prevent messages with E_ALL error reporting
        $this->variables = [
            'ZikulaSettingsModule' => [],
            self::CONFIG => [],
        ];

        // Load all variables into the variables property.
        /** @var ExtensionVarEntity[] $vars */
        $vars = $this->repository->findAll();
        foreach ($vars as $var) {
            if (!array_key_exists($var->getModname(), $this->variables)) {
                $this->variables[$var->getModname()] = [];
            }
            $this->variables[$var->getModname()][$var->getName()] = $var->getValue();
        }

        // Pre-load the variables array with empty arrays for known extensions that do not define any variables to
        // prevent unnecessary queries to the database.
        foreach ($this->kernel->getBundles() as $bundle) {
            if (!array_key_exists($bundle->getName(), $this->variables)) {
                $this->variables[$bundle->getName()] = [];
            }
        }
        // set default values for localized variables
        $this->localizeVariables('en');

        $this->isInitialized = true;
    }

    /**
     * {@inheritdoc}
     */
    public function localizeVariables($lang)
    {
        $items = ['sitename', 'slogan', 'defaultpagetitle', 'defaultmetadescription'];
        foreach ($items as $item) {
            if (isset($this->variables[self::CONFIG][$item . '_en'])) {
                $this->variables[self::CONFIG][$item] = !empty($this->variables[self::CONFIG][$item . '_' . $lang]) ? $this->variables[self::CONFIG][$item . '_' . $lang] : $this->variables[self::CONFIG][$item . '_en'];
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has($extensionName, $variableName)
    {
        if (empty($extensionName) || !is_string($extensionName) || empty($variableName) || !is_string($variableName)) {
            throw new \InvalidArgumentException();
        }
        if (!$this->isInitialized) {
            $this->initialize();
        }

        return isset($this->variables[$extensionName]) && array_key_exists($variableName, $this->variables[$extensionName]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($extensionName, $variableName, $default = false)
    {
        if (empty($extensionName) || !is_string($extensionName) || empty($variableName) || !is_string($variableName)) {
            throw new \InvalidArgumentException();
        }
        if (!$this->isInitialized) {
            $this->initialize();
        }

        if (isset($this->variables[$extensionName]) && array_key_exists($variableName, $this->variables[$extensionName])) {
            return $this->variables[$extensionName][$variableName];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getSystemVar($variableName, $default = false)
    {
        return $this->get(self::CONFIG, $variableName, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getAll($extensionName)
    {
        if (empty($extensionName) || !is_string($extensionName)) {
            throw new \InvalidArgumentException();
        }
        if (!$this->isInitialized) {
            $this->initialize();
        }

        return isset($this->variables[$extensionName]) ? $this->variables[$extensionName] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function set($extensionName, $variableName, $value = '')
    {
        if (empty($extensionName) || !is_string($extensionName) || empty($variableName) || !is_string($variableName)) {
            throw new \InvalidArgumentException();
        }
        if (!$this->isInitialized) {
            $this->initialize();
        }
        if (self::CONFIG == $extensionName && in_array($variableName, $this->protectedSystemVars)) {
            return false;
        }

        $entities = $this->repository->findBy(['modname' => $extensionName, 'name' => $variableName]);
        if (count($entities) > 1 || 0 == count($entities)) {
            foreach ($entities as $entity) {
                // possible duplicates exist. remove all (refs #2385)
                $this->repository->remove($entity);
            }
            $entity = new ExtensionVarEntity();
            $entity->setModname($extensionName);
            $entity->setName($variableName);
        } else {
            $entity = $entities[0];
        }
        $entity->setValue($value);
        $this->repository->persistAndFlush($entity);
        $this->variables[$extensionName][$variableName] = $value;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setAll($extensionName, array $vars)
    {
        $ok = true;
        foreach ($vars as $var => $value) {
            $ok = $ok && $this->set($extensionName, $var, $value);
        }

        return $ok;
    }

    /**
     * {@inheritdoc}
     */
    public function del($extensionName, $variableName)
    {
        if (empty($extensionName) || !is_string($extensionName) || empty($variableName) || !is_string($variableName)) {
            throw new \InvalidArgumentException();
        }
        if (!$this->isInitialized) {
            $this->initialize();
        }

        if (!isset($this->variables[$extensionName])) {
            return true;
        }
        if (array_key_exists($variableName, $this->variables[$extensionName])) {
            unset($this->variables[$extensionName][$variableName]);
        }

        return $this->repository->deleteByExtensionAndName($extensionName, $variableName);
    }

    /**
     * {@inheritdoc}
     */
    public function delAll($extensionName)
    {
        if (empty($extensionName) || !is_string($extensionName)) {
            throw new \InvalidArgumentException();
        }
        if (!$this->isInitialized) {
            $this->initialize();
        }

        if (array_key_exists($extensionName, $this->variables)) {
            unset($this->variables[$extensionName]);
        }

        return $this->repository->deleteByExtension($extensionName);
    }
}
