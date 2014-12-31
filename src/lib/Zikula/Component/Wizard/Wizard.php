<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula CoreInstaller bundle.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Component\Wizard;

use Gedmo\Exception\RuntimeException;
use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Debug\Exception\ClassNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Wizard
 * @package Zikula\Component\Wizard
 */
class Wizard
{
    private $container;
    private $stagesByName = array();
    private $stageOrder = array();
    private $defaultStage;
    private $currentStageName;
    private $YamlFileLoader;
    private $warning = '';

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     * @param string $path including filename, e.g. my/full/path/to/my/stages.yml
     * @throws FileLoaderLoadException
     */
    function __construct(ContainerInterface $container, $path)
    {
        $this->container = $container;
        if (!empty($path)) {
            $this->loadStagesFromYaml($path);
        } else {
            throw new FileLoaderLoadException('No stage definition file provided.');
        }
    }

    /**
     * Load the stage definitions from $path
     *
     * @param string $path including filename, e.g. my/full/path/to/my/stages.yml
     * @throws FileLoaderLoadException
     */
    public function loadStagesFromYaml($path)
    {
        if (!file_exists($path)) {
            throw new FileLoaderLoadException('Stage definition file cannot be found.');
        }
        $pathInfo = pathinfo($path);
        if ($pathInfo['extension'] !== 'yml') {
            throw new FileLoaderLoadException('Stage definition file must include .yml extension.');
        }
        $this->stages = array(); // empty the stages
        if (!isset($this->YamlFileLoader)) {
            $this->YamlFileLoader = new YamlFileLoader(new FileLocator($pathInfo['dirname']));
        }
        $this->YamlFileLoader->load($pathInfo['basename']);
        $stages = $this->YamlFileLoader->getContent()['stages'];
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
     *
     * @param $name
     * @return StageInterface
     */
    public function getCurrentStage($name)
    {
        // compute the stageClass from Request parameter
        $stageClass = $this->getStageClassName($name);

        // loop each stage until finds the first that is necessary
        do {
            $useCurrentStage = false;
            /** @var StageInterface $currentStage */
            if (!isset($currentStage)) {
                $currentStage = $this->getStageInstance($stageClass);
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
        } while ($useCurrentStage == false);

        return $currentStage;
    }

    /**
     * Get an instance of the previous stage
     *
     * @return StageInterface
     */
    public function getPreviousStage()
    {
        return $this->getSequentialStage('prev');
    }

    /**
     * Get an instance of the next stage
     *
     * @return StageInterface
     */
    public function getNextStage()
    {
        return $this->getSequentialStage('next');
    }

    /**
     * get either previous or next stage
     *
     * @param $direction (prev|next)
     * @return StageInterface|null
     */
    private function getSequentialStage($direction)
    {
        $dir = in_array($direction, array('prev', 'next')) ? $direction : 'next';
        ksort($this->stageOrder);
        // forward the array pointer to the current index
        while (current($this->stageOrder) !== $this->currentStageName && key($this->stageOrder) !== null) next($this->stageOrder);
        $key = $dir($this->stageOrder);
        if ((null !== $key) && (false !== $key)) {

            return $this->getStageInstance($this->stagesByName[$key]);
        }

        return null;
    }

    /**
     * Factory class to instantiate a StageClass
     *
     * @param $stageClass
     * @return StageInterface
     */
    private function getStageInstance($stageClass)
    {
        if (!class_exists($stageClass)) {
            throw new RuntimeException('Error: Could not find requested stage class.');
        }
        if(in_array("Zikula\\Component\\Wizard\\InjectContainerInterface", class_implements($stageClass))) {

            return new $stageClass($this->container);
        } else {

            return new $stageClass();
        }
    }

    /**
     * Has the wizard been halted?
     *
     * @return bool
     */
    public function isHalted()
    {
        return (!empty($this->warning));
    }

    /**
     * get any warning currently set
     *
     * @return string
     */
    public function getWarning()
    {
        return "WARNING: The Wizard was halted for the following reason. This must be corrected before you can continue. " . $this->warning;
    }

    /**
     * Match the stage and return the stage classname or default.
     *
     * @param $name
     * @return string
     * @throws \InvalidArgumentException
     */
    private function getStageClassName($name)
    {
        if (!empty($this->stagesByName[$name])) {

            return $this->stagesByName[$name];
        }
        if (!empty($this->defaultStage) && !empty($this->stagesByName[$this->defaultStage])) {

            return $this->stagesByName[$this->defaultStage];
        }
        throw new \InvalidArgumentException('The request stage could not be found and there is no default stage defined.');
    }
}