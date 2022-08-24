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

namespace Zikula\ProfileBundle\Initializer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\ExtensionsBundle\Initializer\BundleInitializerInterface;
use Zikula\ProfileBundle\ProfileConstant;

class ProfileInitializer implements BundleInitializerInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function init(): void
    {
        // create the default data for the module
        $request = null !== $this->requestStack ? $this->requestStack->getMainRequest() : null;
        // fall back to English for CLI installations
        $locale = null !== $request ? $request->getLocale() : 'en';

        $prop = (new PropertyEntity())
            ->setId(ProfileConstant::ATTRIBUTE_NAME_DISPLAY_NAME)
            ->setFormType(TextType::class)
            ->setLabels([$locale => $this->trans('Real Name')])
            ->setWeight(1);
        $this->entityManager->persist($prop);

        $prop = (new PropertyEntity())
            ->setId('publicemail')
            ->setFormType(EmailType::class)
            ->setLabels([$locale => $this->trans('Public Email')])
            ->setWeight(2);
        $this->entityManager->persist($prop);

        $prop = (new PropertyEntity())
            ->setId('url')
            ->setFormType(UrlType::class)
            ->setLabels([$locale => $this->trans('Homepage')])
            ->setWeight(3);
        $this->entityManager->persist($prop);

        $prop = (new PropertyEntity())
            ->setId('timezone')
            ->setFormType(TimezoneType::class)
            ->setLabels([$locale => $this->trans('Timezone')])
            ->setWeight(4);
        $this->entityManager->persist($prop);

        $prop = (new PropertyEntity())
            ->setId('avatar')
            ->setFormType(AvatarType::class)
            ->setLabels([$locale => $this->trans('Avatar')])
            ->setWeight(5);
        $this->entityManager->persist($prop);

        $prop = (new PropertyEntity())
            ->setId('city')
            ->setFormType(TextType::class)
            ->setLabels([$locale => $this->trans('Location')])
            ->setWeight(6);
        $this->entityManager->persist($prop);

        $prop = (new PropertyEntity())
            ->setId('occupation')
            ->setFormType(TextType::class)
            ->setLabels([$locale => $this->trans('Occupation')])
            ->setWeight(7);
        $this->entityManager->persist($prop);

        $prop = (new PropertyEntity())
            ->setId('signature')
            ->setFormType(TextType::class)
            ->setLabels([$locale => $this->trans('Signature')])
            ->setWeight(8);
        $this->entityManager->persist($prop);

        $prop = (new PropertyEntity())
            ->setId('extrainfo')
            ->setFormType(TextareaType::class)
            ->setLabels([$locale => $this->trans('Extra info')])
            ->setWeight(9);
        $this->entityManager->persist($prop);

        $this->entityManager->flush();
    }
}
