<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Dumper;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;

/**
 * YamlDumper dumps a service container as a YAML string.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class YamlDumper extends Dumper
{
    /**
     * Dumps the service container as an YAML string.
     *
     * @param  array  $options An array of options
     *
     * @return string A YAML string representing of the service container
     *
     * @api
     */
    public function dump(array $options = array())
    {
        return $this->addParameters()."\n".$this->addServices();
    }

    /**
     * Adds a service
     *
     * @param string $id
     * @param Definition $definition
     * @return string
     */
    private function addService($id, $definition)
    {
        $code = "    $id:\n";
        if ($definition->getClass()) {
            $code .= sprintf("        class: %s\n", $definition->getClass());
        }

        $tagsCode = '';
        foreach ($definition->getTags() as $name => $tags) {
            foreach ($tags as $attributes) {
                $att = array();
                foreach ($attributes as $key => $value) {
                    $att[] = sprintf('%s: %s', Yaml::dump($key), Yaml::dump($value));
                }
                $att = $att ? ', '.implode(' ', $att) : '';

                $tagsCode .= sprintf("            - { name: %s%s }\n", Yaml::dump($name), $att);
            }
        }
        if ($tagsCode) {
            $code .= "        tags:\n".$tagsCode;
        }

        if ($definition->getFile()) {
            $code .= sprintf("        file: %s\n", $definition->getFile());
        }

        if ($definition->getFactoryMethod()) {
            $code .= sprintf("        factory_method: %s\n", $definition->getFactoryMethod());
        }

        if ($definition->getFactoryService()) {
            $code .= sprintf("        factory_service: %s\n", $definition->getFactoryService());
        }

        if ($definition->getArguments()) {
            $code .= sprintf("        arguments: %s\n", Yaml::dump($this->dumpValue($definition->getArguments()), 0));
        }

        if ($definition->getProperties()) {
            $code .= sprintf("        properties: %s\n", Yaml::dump($this->dumpValue($definition->getProperties()), 0));
        }

        if ($definition->getMethodCalls()) {
            $code .= sprintf("        calls:\n            %s\n", str_replace("\n", "\n            ", Yaml::dump($this->dumpValue($definition->getMethodCalls()), 1)));
        }

        if (ContainerInterface::SCOPE_CONTAINER !== $scope = $definition->getScope()) {
            $code .= sprintf("        scope: %s\n", $scope);
        }

        if ($callable = $definition->getConfigurator()) {
            if (is_array($callable)) {
                if (is_object($callable[0]) && $callable[0] instanceof Reference) {
                    $callable = array($this->getServiceCall((string) $callable[0], $callable[0]), $callable[1]);
                } else {
                    $callable = array($callable[0], $callable[1]);
                }
            }

            $code .= sprintf("        configurator: %s\n", Yaml::dump($callable, 0));
        }

        return $code;
    }

    /**
     * Adds a service alias
     *
     * @param string $alias
     * @param string $id
     * @return string
     */
    private function addServiceAlias($alias, $id)
    {
        if ($id->isPublic()) {
            return sprintf("    %s: @%s\n", $alias, $id);
        } else {
            return sprintf("    %s:\n        alias: %s\n        public: false", $alias, $id);
        }
    }

    /**
     * Adds services
     *
     * @return string
     */
    private function addServices()
    {
        if (!$this->container->getDefinitions()) {
            return '';
        }

        $code = "services:\n";
        foreach ($this->container->getDefinitions() as $id => $definition) {
            $code .= $this->addService($id, $definition);
        }

        foreach ($this->container->getAliases() as $alias => $id) {
            $code .= $this->addServiceAlias($alias, $id);
        }

        return $code;
    }

    /**
     * Adds parameters
     *
     * @return string
     */
    private function addParameters()
    {
        if (!$this->container->getParameterBag()->all()) {
            return '';
        }

        if ($this->container->isFrozen()) {
            $parameters = $this->prepareParameters($this->container->getParameterBag()->all());
        } else {
            $parameters = $this->container->getParameterBag()->all();
        }

        return Yaml::dump(array('parameters' => $parameters), 2);
    }

    /**
     * Dumps the value to YAML format
     *
     * @param mixed $value
     * @throws \RuntimeException When trying to dump object or resource
     */
    private function dumpValue($value)
    {
        if (is_array($value)) {
            $code = array();
            foreach ($value as $k => $v) {
                $code[$k] = $this->dumpValue($v);
            }

            return $code;
        } elseif (is_object($value) && $value instanceof Reference) {
            return $this->getServiceCall((string) $value, $value);
        } elseif (is_object($value) && $value instanceof Parameter) {
            return $this->getParameterCall((string) $value);
        } elseif (is_object($value) || is_resource($value)) {
            throw new \RuntimeException('Unable to dump a service container if a parameter is an object or a resource.');
        }

        return $value;
    }

    /**
     * Gets the service call.
     *
     * @param string    $id
     * @param Reference $reference
     * @return string
     */
    private function getServiceCall($id, Reference $reference = null)
    {
        if (null !== $reference && ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE !== $reference->getInvalidBehavior()) {
            return sprintf('@?%s', $id);
        }

        return sprintf('@%s', $id);
    }

    /**
     * Gets parameter call.
     *
     * @param string $id
     * @return string
     */
    private function getParameterCall($id)
    {
        return sprintf('%%%s%%', $id);
    }

    /**
     * Prepares parameters
     *
     * @param array $parameters
     * @return array
     */
    private function prepareParameters($parameters)
    {
        $filtered = array();
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $value = $this->prepareParameters($value);
            } elseif ($value instanceof Reference) {
                $value = '@'.$value;
            }

            $filtered[$key] = $value;
        }

        return $this->escape($filtered);
    }

    /**
     * Escapes arguments
     *
     * @param array $arguments
     * @return array
     */
    private function escape($arguments)
    {
        $args = array();
        foreach ($arguments as $k => $v) {
            if (is_array($v)) {
                $args[$k] = $this->escape($v);
            } elseif (is_string($v)) {
                $args[$k] = str_replace('%', '%%', $v);
            } else {
                $args[$k] = $v;
            }
        }

        return $args;
    }
}
