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

namespace Zikula\CoreBundle\Composer;

use ArrayAccess;
use Exception;
use Symfony\Component\HttpKernel\Exception\PreconditionRequiredHttpException;
use Zikula\CoreBundle\Translation\TranslatorTrait;
use function Symfony\Component\String\s;

class MetaData implements ArrayAccess
{
    use TranslatorTrait;

    private string $name;

    private string $version;

    private string $description;

    private string $type;

    private string $class;

    private string $namespace;

    private array $autoload;

    private string $displayName;

    private string $url;

    private string $icon;

    private array $capabilities;

    public function __construct(array $json = [])
    {
        $this->name = $json['name'];
        $this->version = $json['version'] ?? '';
        $this->description = $json['description'] ?? '';
        $this->type = $json['type'];
        $this->class = $json['extra']['zikula']['class'];
        $this->namespace = s($this->class)->beforeLast('\\', true)->toString();
        $this->autoload = $json['autoload'];
        $this->displayName = $json['extra']['zikula']['displayname'] ?? '';
        $this->url = $json['extra']['zikula']['url'] ?? '';
        $this->icon = $json['extra']['zikula']['icon'] ?? '';
        $this->capabilities = $json['extra']['zikula']['capabilities'] ?? [];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): string
    {
        return $this->version;
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

        $description = $this->trans($this->description);

        return empty($description) ? $this->description : $description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDisplayName(): string
    {
        $this->confirmTranslator();

        $displayName = $this->trans($this->displayName);

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
            $url = $this->trans($this->url);

            return empty($url) ? $this->url : $url;
        }

        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
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

    private function confirmTranslator(): void
    {
        if (!isset($this->translator)) {
            throw new PreconditionRequiredHttpException(sprintf('The translator property is not set correctly in %s', __CLASS__));
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->{$offset});
    }

    public function offsetGet(mixed $offset): mixed
    {
        $method = 'get' . ucwords($offset);

        return $this->{$method}();
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new Exception('Setting values by array access is not allowed.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new Exception('Unsetting values by array access is not allowed.');
    }
}
