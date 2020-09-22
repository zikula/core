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

namespace Zikula\ExtensionsModule\Api;

use InvalidArgumentException;
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
    public const CONFIG = 'ZConfig';

    /**
     * @var bool
     */
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

    public function __construct(
        string $installed,
        ExtensionVarRepositoryInterface $repository,
        ZikulaHttpKernelInterface $kernel,
        array $multisitesParameters
    ) {
        $this->installed = '0.0.0' !== $installed;
        $this->repository = $repository;
        $this->kernel = $kernel;
        $this->protectedSystemVars = $multisitesParameters['protected.systemvars'] ?? [];
    }

    /**
     * Loads extension vars for all extensions to reduce sql statements.
     */
    private function initialize(): bool
    {
        if (!$this->installed) {
            return false;
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

        return true;
    }

    public function localizeVariables(string $lang): void
    {
        $items = ['sitename', 'slogan', 'defaultpagetitle', 'defaultmetadescription', 'startController'];
        foreach ($items as $item) {
            $indexSource = $item . '_en';
            $indexTarget = $item . '_' . $lang;
            if (isset($this->variables[self::CONFIG][$indexSource])) {
                $this->variables[self::CONFIG][$item] = !empty($this->variables[self::CONFIG][$indexTarget]) ? $this->variables[self::CONFIG][$indexTarget] : $this->variables[self::CONFIG][$indexSource];
            }
        }
    }

    public function has(string $extensionName, string $variableName): bool
    {
        if (empty($extensionName) || empty($variableName)) {
            throw new InvalidArgumentException();
        }
        if (!$this->isInitialized && !$this->initialize()) {
            return false;
        }

        return isset($this->variables[$extensionName]) && array_key_exists($variableName, $this->variables[$extensionName]);
    }

    public function get(string $extensionName, string $variableName, $default = false)
    {
        if (empty($extensionName) || empty($variableName)) {
            throw new InvalidArgumentException();
        }
        if (!$this->isInitialized && !$this->initialize()) {
            return $default;
        }

        if (isset($this->variables[$extensionName]) && array_key_exists($variableName, $this->variables[$extensionName])) {
            return $this->variables[$extensionName][$variableName];
        }

        return $default;
    }

    public function getSystemVar(string $variableName, $default = false)
    {
        return $this->get(self::CONFIG, $variableName, $default);
    }

    public function getAll(string $extensionName): array
    {
        if (empty($extensionName)) {
            throw new InvalidArgumentException();
        }
        if (!$this->isInitialized && !$this->initialize()) {
            return [];
        }

        return $this->variables[$extensionName] ?? [];
    }

    public function set(string $extensionName, string $variableName, $value = ''): bool
    {
        if (empty($extensionName) || empty($variableName)) {
            throw new InvalidArgumentException();
        }
        if (!$this->isInitialized) {
            $this->initialize();
        }
        if (self::CONFIG === $extensionName && in_array($variableName, $this->protectedSystemVars, true)) {
            return false;
        }

        $entities = $this->repository->findBy(['modname' => $extensionName, 'name' => $variableName]);
        $amountOfEntities = count($entities);
        if (1 === $amountOfEntities) {
            $entity = $entities[0];
        } else {
            if (1 < $amountOfEntities) {
                /** @var ExtensionVarEntity $entity */
                foreach ($entities as $entity) {
                    // possible duplicates exist. remove all (refs #2385)
                    $this->repository->remove($entity);
                }
            }
            $entity = new ExtensionVarEntity();
            $entity->setModname($extensionName);
            $entity->setName($variableName);
        }
        $entity->setValue($value);
        $this->repository->persistAndFlush($entity);
        $this->variables[$extensionName][$variableName] = $value;

        return true;
    }

    public function setAll(string $extensionName, array $variables = []): bool
    {
        $ok = true;
        foreach ($variables as $var => $value) {
            $ok = $ok && $this->set($extensionName, $var, $value);
        }

        return $ok;
    }

    public function del(string $extensionName, string $variableName): bool
    {
        if (empty($extensionName) || empty($variableName)) {
            throw new InvalidArgumentException();
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

    public function delAll(string $extensionName): bool
    {
        if (empty($extensionName)) {
            throw new InvalidArgumentException();
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
