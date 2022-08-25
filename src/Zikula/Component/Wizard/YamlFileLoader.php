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

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\Yaml\Parser as YamlParser;

/**
 * YamlFileLoader loads YAML files.
 */
class YamlFileLoader extends FileLoader
{
    private YamlParser $yamlParser;
    private array $content;

    /**
     * {@inheritdoc}
     */
    public function load($resource, string $type = null): mixed
    {
        $path = $this->locator->locate($resource);

        $this->content = $this->loadFile($path);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, string $type = null): bool
    {
        return is_string($resource) && 'yml' === pathinfo($resource, PATHINFO_EXTENSION);
    }

    public function getContent(): ?array
    {
        return $this->content;
    }

    /**
     * Loads a YAML file.
     *
     * @throws InvalidArgumentException when the given file is not a local file or when it does not exist
     */
    private function loadFile(string $file): array
    {
        if (!stream_is_local($file)) {
            throw new InvalidArgumentException(sprintf('This is not a local file "%s".', $file));
        }

        if (!file_exists($file)) {
            throw new InvalidArgumentException(sprintf('The service file "%s" is not valid.', $file));
        }

        if (null === $this->yamlParser) {
            $this->yamlParser = new YamlParser();
        }

        return $this->validate($this->yamlParser->parse(file_get_contents($file)), $file);
    }

    /**
     * Validates a YAML file.
     *
     * @param mixed $content
     *
     * @throws InvalidArgumentException When service file is not valid
     */
    private function validate($content, string $file): array
    {
        if (null === $content) {
            return $content;
        }

        if (!is_array($content)) {
            throw new InvalidArgumentException(sprintf('The yaml file "%s" is not valid. It should contain an array. Check your YAML syntax.', $file));
        }

        if (isset($content['stages']) && !is_array($content['stages'])) {
            throw new InvalidArgumentException(sprintf('The "stages" key should contain an array in %s. Check your YAML syntax.', $file));
        }

        return $content;
    }
}
