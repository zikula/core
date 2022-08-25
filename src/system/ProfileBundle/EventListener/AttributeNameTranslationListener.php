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

namespace Zikula\ProfileBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Exception;
use Zikula\ProfileBundle\Entity\PropertyEntity;
use Zikula\UsersBundle\Entity\UserAttributeEntity;

class AttributeNameTranslationListener implements EventSubscriber
{
    private array $translations = [];

    private string $prefix;

    public function __construct(private readonly string $locale, string $prefix)
    {
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

        if ($entity instanceof UserAttributeEntity) {
            $name = $entity->getName();
            if (!isset($this->translations[$this->locale][$name])) {
                $this->translations[$this->locale][$name] = $name;
                if (0 === mb_strpos($name, $this->prefix)) {
                    try {
                        $property = $entityManager->find(PropertyEntity::class, mb_substr($name, mb_strlen($this->prefix)));
                        $this->translations[$this->locale][$name] = isset($property) ? $property->getLabel($this->locale) : $name;
                    } catch (Exception $exception) {
                        // listener fails during upgrade. silently fail
                    }
                }
            }
            $entity->setExtra($this->translations[$this->locale][$name]);
        }
    }
}
