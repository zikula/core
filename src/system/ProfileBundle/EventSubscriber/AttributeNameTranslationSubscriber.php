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

namespace Zikula\ProfileBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Zikula\ProfileBundle\Entity\Property;
use Zikula\UsersBundle\Entity\UserAttribute;

class AttributeNameTranslationSubscriber implements EventSubscriber
{
    private array $translations = [];

    private string $prefix;

    public function __construct(
        #[Autowire('%kernel.default_locale%')]
        private readonly string $locale,
        #[Autowire('%zikula_profile_module.property_prefix%')]
        string $prefix
    ) {
        $this->prefix = $prefix . ':';
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postLoad,
        ];
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();

        if ($entity instanceof UserAttribute) {
            $name = $entity->getName();
            if (!isset($this->translations[$this->locale][$name])) {
                $this->translations[$this->locale][$name] = $name;
                if (0 === mb_strpos($name, $this->prefix)) {
                    try {
                        $property = $entityManager->find(Property::class, mb_substr($name, mb_strlen($this->prefix)));
                        $this->translations[$this->locale][$name] = isset($property) ? $property->getLabel($this->locale) : $name;
                    } catch (\Exception $exception) {
                        // subscriber fails during upgrade. silently fail
                    }
                }
            }
            $entity->setExtra($this->translations[$this->locale][$name]);
        }
    }
}
