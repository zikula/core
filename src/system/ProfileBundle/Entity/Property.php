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

namespace Zikula\ProfileBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\FormExtensionBundle\DynamicFieldInterface;
use Zikula\ProfileBundle\Repository\PropertyRepository;

#[ORM\Entity(repositoryClass: PropertyRepository::class)]
#[ORM\Table(name: 'user_property')]
#[UniqueEntity('id')]
class Property implements DynamicFieldInterface
{
    /**
     * Note this value is NOT auto-generated and must be manually created!
     * @Assert\Regex("/^[a-zA-Z0-9\-\_]+$/")
     */
    #[ORM\Id]
    #[ORM\Column(length: 190, unique: true)]
    private string $id;

    #[ORM\Column]
    private array $labels = [];

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\Length(min: 1, max: 255)]
    private string $formType = '';

    #[ORM\Column]
    private array $formOptions = [];

    #[ORM\Column]
    #[Assert\GreaterThan(0)]
    private int $weight = 0;

    #[ORM\Column]
    private bool $active = true;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    public function getLabel(string $locale = '', string $default = 'en'): string
    {
        if (!empty($locale) && isset($this->labels[$locale])) {
            return $this->labels[$locale];
        }
        if (!empty($default) && isset($this->labels[$default])) {
            return $this->labels[$default];
        }
        $values = array_values($this->labels);

        return !empty($values[0]) ? array_shift($values) : $this->id;
    }

    /**
     * @param string[] $labels
     */
    public function setLabels(array $labels): self
    {
        $this->labels = $labels;

        return $this;
    }

    public function getFormType(): string
    {
        return $this->formType;
    }

    public function setFormType(string $formType): self
    {
        $this->formType = $formType;

        return $this;
    }

    public function getFormOptions(): array
    {
        if (!isset($this->formOptions['required'])) {
            $this->formOptions['required'] = false;
        }

        return $this->formOptions;
    }

    public function setFormOptions(array $formOptions): self
    {
        $this->formOptions = $formOptions;

        return $this;
    }

    public function getFieldInfo(): array
    {
        return [
            'formType' => $this->getFormType(),
            'formOptions' => $this->getFormOptions()
        ];
    }

    public function setFieldInfo(array $fieldInfo): self
    {
        return $this->setFormType($fieldInfo['formType'])
            ->setFormOptions($fieldInfo['formOptions']);
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function incrementWeight(): self
    {
        $this->weight++;

        return $this;
    }

    public function decrementWeight(): self
    {
        $this->weight--;

        return $this;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getId();
    }

    public function getName(): string
    {
        return $this->getId();
    }

    public function getPrefix(): string
    {
        return 'zpmpp';
    }

    public function getGroupNames(): array
    {
        return [];
    }
}
