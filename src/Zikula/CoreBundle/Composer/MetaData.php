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

namespace Zikula\Bundle\CoreBundle\Composer;

use ArrayAccess;
use Exception;
use Symfony\Component\HttpKernel\Exception\PreconditionRequiredHttpException;
use function Symfony\Component\String\s;
use Translation\Extractor\Annotation\Ignore;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;

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

    private array $securitySchema;

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
        $this->securitySchema = $json['extra']['zikula']['securityschema'] ?? [];
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

        $description = $this->trans(/** @Ignore */ $this->description);

        return empty($description) ? $this->description : $description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDisplayName(): string
    {
        $this->confirmTranslator();

        $displayName = $this->trans(/** @Ignore */ $this->displayName);

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
            $url = $this->trans(/** @Ignore */ $this->url);

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

    public function getSecuritySchema(): array
    {
        return $this->securitySchema;
    }

    private function confirmTranslator(): void
    {
        if (!isset($this->translator)) {
            throw new PreconditionRequiredHttpException(sprintf('The translator property is not set correctly in %s', __CLASS__));
        }
    }

    public function offsetExists($offset): bool
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
