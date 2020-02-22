<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Composer;

use ArrayAccess;
use Exception;
use Symfony\Component\HttpKernel\Exception\PreconditionRequiredHttpException;
use Translation\Extractor\Annotation\Ignore;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;

class MetaData implements ArrayAccess
{
    use TranslatorTrait;

    public const TYPE_MODULE = 2;

    public const TYPE_SYSTEM_MODULE = 3;

    public const TYPE_THEME = 4;

    public const TYPE_SYSTEM_THEME = 5;

    public const EXTENSION_TYPE_MODULE = 'zikula-module';

    public const EXTENSION_TYPE_THEME = 'zikula-theme';

    public const DEPENDENCY_REQUIRED = 1;

    public const DEPENDENCY_RECOMMENDED = 2;

    public const DEPENDENCY_CONFLICTS = 3;

    private $name;

    private $version;

    private $description;

    private $type;

    private $dependencies;

    private $shortName;

    private $class;

    private $namespace;

    private $autoload;

    private $displayName;

    private $url;

    private $oldNames;

    private $icon;

    private $capabilities;

    private $securitySchema;

    private $extensionType;

    private $coreCompatibility;

    public function __construct(array $json = [])
    {
        $this->name = $json['name'];
        $this->version = $json['version'] ?? '';
        $this->description = $json['description'] ?? '';
        $this->type = $json['type'];
        $this->dependencies = $this->formatDependencies($json);
        $this->shortName = $json['extra']['zikula']['short-name'];
        $this->class = $json['extra']['zikula']['class'];
        $this->namespace = mb_substr($this->class, 0, mb_strrpos($this->class, '\\') + 1);
        $this->autoload = $json['autoload'];
        $this->displayName = $json['extra']['zikula']['displayname'] ?? '';
        $this->url = $json['extra']['zikula']['url'] ?? '';
        $this->oldNames = $json['extra']['zikula']['oldnames'] ?? [];
        $this->icon = $json['extra']['zikula']['icon'] ?? '';
        $this->capabilities = $json['extra']['zikula']['capabilities'] ?? [];
        $this->securitySchema = $json['extra']['zikula']['securityschema'] ?? [];
        $this->extensionType = $json['extensionType'] ?? self::TYPE_MODULE;
        $this->coreCompatibility = $json['extra']['zikula']['core-compatibility'] ?? null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getShortName(): string
    {
        return $this->shortName;
    }

    public function getPsr0(): array
    {
        return $this->autoload['psr-0'] ?? [];
    }

    public function getPsr4(): array
    {
        return $this->autoload['psr-4'] ?? [];
    }

    public function getAutoload(): array
    {
        return $this->autoload;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDescription(): string
    {
        $this->confirmTranslator();

        $description = $this->trans(/** @Ignore */$this->description);

        return empty($description) ? $this->description : $description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function getDisplayName(): string
    {
        $this->confirmTranslator();

        $displayName = $this->trans(/** @Ignore */$this->displayName);

        return empty($displayName) ? $this->displayName : $displayName;
    }

    public function setDisplayName(string $name): void
    {
        $this->displayName = $name;
    }

    public function getUrl(bool $translated = true): string
    {
        if ($translated) {
            $this->confirmTranslator();
            $url = $this->trans(/** @Ignore */$this->url);

            return empty($url) ? $this->url : $url;
        }

        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getOldNames(): array
    {
        return $this->oldNames;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }

    public function getCapabilities(): array
    {
        return $this->capabilities;
    }

    public function getSecuritySchema(): array
    {
        return $this->securitySchema;
    }

    public function getExtensionType(): int
    {
        return $this->extensionType;
    }

    public function getCoreCompatibility(): ?string
    {
        return $this->coreCompatibility;
    }

    private function formatDependencies(array $json = []): array
    {
        $dependencies = [];
        if (!empty($json['require'])) {
            foreach ($json['require'] as $package => $version) {
                $dependencies[] = [
                    'modname' => $package,
                    'minversion' => $version,
                    'maxversion' => $version,
                    'status' => self::DEPENDENCY_REQUIRED
                ];
            }
        } else {
            $dependencies[] = [
                'modname' => 'zikula/core',
                'minversion' => '>=1.4.1 <3.0.0',
                'maxversion' => '>=1.4.1 <3.0.0',
                'status' => self::DEPENDENCY_REQUIRED
            ];
        }
        if (!empty($json['suggest'])) {
            foreach ($json['suggest'] as $package => $reason) {
                if (mb_strpos($package, ':')) {
                    list($name, $version) = explode(':', $package, 2);
                } else {
                    $name = $package;
                    $version = '-1';
                }
                $dependencies[] = [
                    'modname' => $name,
                    'minversion' => $version,
                    'maxversion' => $version,
                    'reason' => $reason,
                    'status' => self::DEPENDENCY_RECOMMENDED
                ];
            }
        }

        return $dependencies;
    }

    private function confirmTranslator(): void
    {
        if (!isset($this->translator)) {
            throw new PreconditionRequiredHttpException(sprintf('The translator property is not set correctly in %s', __CLASS__));
        }
    }

    /**
     * Module MetaData as array
     */
    public function getFilteredVersionInfoArray(): array
    {
        return [
            'name' => $this->getShortName(),
            'type' => $this->getExtensionType(),
            'displayname' => $this->getDisplayName(),
            'oldnames' => $this->getOldNames(),
            'description' => $this->getDescription(),
            'version' => $this->getVersion(),
            'url' => $this->getUrl(),
            'capabilities' => $this->getCapabilities(),
            'securityschema' => $this->getSecuritySchema(),
            'dependencies' => $this->getDependencies(),
            'coreCompatibility' => $this->getCoreCompatibility()
        ];
    }

    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    public function offsetGet($offset)
    {
        $method = 'get' . ucwords($offset);

        return $this->{$method}();
    }

    public function offsetSet($offset, $value): void
    {
        throw new Exception('Setting values by array access is not allowed.');
    }

    public function offsetUnset($offset): void
    {
        throw new Exception('Unsetting values by array access is not allowed.');
    }
}
