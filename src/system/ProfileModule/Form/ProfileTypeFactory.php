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

namespace Zikula\ProfileModule\Form;

use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\FormExtensionBundle\Form\Type\InlineFormDefinitionType;
use Zikula\ProfileModule\ProfileConstant;
use Zikula\ProfileModule\Repository\PropertyRepositoryInterface;

class ProfileTypeFactory
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var PropertyRepositoryInterface
     */
    private $propertyRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $prefix;

    public function __construct(
        FormFactoryInterface $formFactory,
        PropertyRepositoryInterface $propertyRepository,
        TranslatorInterface $translator,
        string $prefix
    ) {
        $this->formFactory = $formFactory;
        $this->propertyRepository = $propertyRepository;
        $this->translator = $translator;
        $this->prefix = $prefix;
    }

    public function createForm(PersistentCollection $attributes, bool $includeButtons = true): FormInterface
    {
        $attributeValues = [];
        foreach ($attributes as $attribute) {
            if (0 === mb_strpos($attribute->getName(), $this->prefix)) {
                $attributeValues[$attribute->getName()] = $attribute->getValue();
            }
        }

        $formBuilder = $this->formFactory->createNamedBuilder(ProfileConstant::FORM_BLOCK_PREFIX, FormType::class, $attributeValues, [
            'auto_initialize' => false,
            'error_bubbling' => true,
            'mapped' => false
        ]);
        $formBuilder->add('dynamicFields', InlineFormDefinitionType::class, [
            'dynamicFieldsContainer' => $this->propertyRepository,
            'label' => false,
            'inherit_data' => true
        ]);

        if ($includeButtons) {
            $formBuilder->add('save', SubmitType::class, [
                'label' => $this->translator->trans('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn-success'
                ]
            ]);
            $formBuilder->add('cancel', SubmitType::class, [
                'label' => $this->translator->trans('Cancel'),
                'icon' => 'fa-times'
            ]);
        }

        return $formBuilder->getForm();
    }
}
