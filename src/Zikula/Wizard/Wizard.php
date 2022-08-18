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

namespace Zikula\Component\Wizard;

use InvalidArgumentException;
use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class Wizard
{
    private StageContainerInterface $stageContainer;

    private array $stagesByName = [];

    private array $stageOrder = [];

    private string $defaultStage;

    private string $currentStageName;

    private YamlFileLoader $yamlFileLoader;

    private string $warning = '';

    /**
     * @throws LoaderLoadException
     */
    public function __construct(StageContainerInterface $stageContainer, string $path)
    {
        $this->stageContainer = $stageContainer;
        if (!empty($path)) {
            $this->loadStagesFromYaml($path);
        } else {
            throw new LoaderLoadException('No stage definition file provided.');
        }
    }

    /**
     * Load the stage definitions from $path
     *
     * @throws LoaderLoadException
     */
    public function loadStagesFromYaml(string $path): void
    {
        if (!file_exists($path)) {
            throw new LoaderLoadException('Stage definition file cannot be found.');
        }
        $pathInfo = pathinfo($path);
        if (!in_array($pathInfo['extension'], ['yml', 'yaml'])) {
            throw new LoaderLoadException('Stage definition file must include .yml extension.');
        }

        // empty the stages
        $this->stagesByName = [];
        if (!isset($this->yamlFileLoader)) {
            $this->yamlFileLoader = new YamlFileLoader(new FileLocator($pathInfo['dirname']));
        }
        $this->yamlFileLoader->load($pathInfo['basename']);
        $stages = $this->yamlFileLoader->getContent();
        $stages = $stages['stages'];
        foreach ($stages as $key => $stageArray) {
            $this->stagesByName[$key] = $stageArray['class'];
            $this->stageOrder[$stageArray['order']] = $key;
            if (isset($stageArray['default'])) {
                $this->defaultStage = $key;
            }
        }
    }

    /**
     * Get the stage that is the first necessary stage
     */
    public function getCurrentStage(string $name): StageInterface
    {
        // compute the stageClass from Request parameter
        $stageClass = $this->getStageClassName($name);

        // loop each stage until finds the first that is necessary

        do {
            $useCurrentStage = false;
            /** @var StageInterface $currentStage */
            if (!isset($currentStage)) {
                $currentStage = $this->getStage($stageClass);
            }
            $this->currentStageName = $currentStage->getName();
            try {
                $isNecessary = $currentStage->isNecessary();
            } catch (AbortStageException $e) {
                $this->warning = $e->getMessage();
                $isNecessary = true;
            }
            if ($isNecessary) {
                $useCurrentStage = true;
            } else {
                $currentStage = $this->getNextStage();
            }
        } while (false === $useCurrentStage);

        return $currentStage;
    }

    /**
     * Get an instance of the previous stage
     */
    public function getPreviousStage(): StageInterface
    {
        return $this->getSequentialStage('prev');
    }

    /**
     * Get an instance of the next stage
     */
    public function getNextStage(): StageInterface
    {
        return $this->getSequentialStage('next');
    }

    /**
     * Get either previous or next stage
     */
    private function getSequentialStage(string $direction): ?StageInterface
    {
        $dir = in_array($direction, ['prev', 'next']) ? $direction : 'next';
        ksort($this->stageOrder);
        // forward the array pointer to the current index
        while (current($this->stageOrder) !== $this->currentStageName && null !== key($this->stageOrder)) {
            next($this->stageOrder);
        }
        $key = $dir($this->stageOrder);
        if (null !== $key && false !== $key) {
            return $this->getStage($this->stagesByName[$key]);
        }

        return null;
    }

    /**
     * Get stage from stageContainer
     */
    private function getStage(string $stageClass): StageInterface
    {
        if ($this->stageContainer->has($stageClass)) {
            return $this->stageContainer->get($stageClass);
        }
        throw new FileNotFoundException('Error: Could not find requested stage class.');
    }

    /**
     * Has the wizard been halted?
     */
    public function isHalted(): bool
    {
        return !empty($this->warning);
    }

    /**
     * Get any warning currently set
     */
    public function getWarning(): string
    {
        return 'WARNING: The Wizard was halted for the following reason. This must be corrected before you can continue. ' . $this->warning;
    }

    /**
     * Match the stage and return the stage classname or default.
     *
     * @throws InvalidArgumentException
     */
    private function getStageClassName(string $name): string
    {
        if (!empty($this->stagesByName[$name])) {
            return $this->stagesByName[$name];
        }
        if (!empty($this->defaultStage) && !empty($this->stagesByName[$this->defaultStage])) {
            return $this->stagesByName[$this->defaultStage];
        }
        throw new InvalidArgumentException('The request stage could not be found and there is no default stage defined.');
    }
}
