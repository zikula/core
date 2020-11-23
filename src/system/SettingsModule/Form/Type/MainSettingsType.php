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

namespace Zikula\SettingsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Translation\Extractor\Annotation\Ignore;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\Bundle\FormExtensionBundle\Form\Type\ControllerType;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;

/**
 * Main settings form type.
 */
class MainSettingsType extends AbstractType
{
    use TranslatorTrait;

    /**
     * @var ExtensionRepositoryInterface
     */
    private $extensionRepository;

    public function __construct(
        TranslatorInterface $translator,
        ExtensionRepositoryInterface $extensionRepository
    ) {
        $this->setTranslator($translator);
        $this->extensionRepository = $extensionRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $spaceReplaceCallbackTransformer = new CallbackTransformer(
            static function ($originalDescription) {
                return $originalDescription;
            },
            static function ($submittedDescription) {
                return mb_ereg_replace(' ', '', $submittedDescription);
            }
        );
        $pageTitleLocalizationTransformer = new CallbackTransformer(
            function ($originalPageTitle) {
                $originalPageTitle = empty($originalPageTitle) ? '%pagetitle%' : $originalPageTitle;
                $originalPageTitle = str_replace(
                    ['%pagetitle%', '%sitename%', '%modulename%'],
                    [$this->trans('%pagetitle%'), $this->trans('%sitename%'), $this->trans('%modulename%')],
                    $originalPageTitle
                );

                return $originalPageTitle;
            },
            function ($submittedPageTitle) {
                $submittedPageTitle = str_replace(
                    [$this->trans('%pagetitle%'), $this->trans('%sitename%'), $this->trans('%modulename%')],
                    ['%pagetitle%', '%sitename%', '%modulename%'],
                    $submittedPageTitle
                );

                return $submittedPageTitle;
            }
        );

        $builder
            ->add(
                $builder->create('pagetitle', TextType::class, [
                    'label' => 'Page title structure',
                    'required' => false,
                    'help' => 'Possible tags: %pagetitle%, %sitename%, %modulename%'
                ])
                ->addModelTransformer($pageTitleLocalizationTransformer)
            )
            ->add('adminmail', EmailType::class, [
                'label' => 'Admin\'s e-mail address',
                'constraints' => new Email()
            ])
            ->add('siteoff', ChoiceType::class, [
                'label' => 'Disable site',
                'label_attr' => ['class' => 'radio-custom'],
                'expanded' => true,
                'choices' => [
                    'Yes' => 1,
                    'No' => 0
                ],
            ])
            ->add('siteoffreason', TextareaType::class, [
                'label' => 'Reason for disabling site',
                'required' => false
            ])
            ->add('UseCompression', CheckboxType::class, [
                'label' => 'Activate compression',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('profilemodule', ChoiceType::class, [
                'label' => 'Module used for managing user profiles',
                'choices' => /** @Ignore */$this->formatModuleArrayForSelect($options['profileModules']),
                'placeholder' => 'No profile module',
                'required' => false
            ])
            ->add('messagemodule', ChoiceType::class, [
                'label' => 'Module used for private messaging',
                'choices' => /** @Ignore */$this->formatModuleArrayForSelect($options['messageModules']),
                'placeholder' => 'No message module',
                'required' => false
            ])
            ->add('ajaxtimeout', IntegerType::class, [
                'label' => 'Time-out for ajax connections',
                'input_group' => ['right' => 'milliseconds']
            ])
            ->add(
                $builder->create('permasearch', TextType::class, [
                    'label' => 'List to search for',
                    'constraints' => new Callback([
                        'callback' => function ($data, ExecutionContextInterface $context) {
                            if (mb_ereg(',$', $data)) {
                                $context->addViolation($this->trans('Error! In your permalink settings, strings cannot be terminated with a comma.'));
                            }
                        }
                    ])
                ])
                ->addModelTransformer($spaceReplaceCallbackTransformer)
            )
            ->add(
                $builder->create('permareplace', TextType::class, [
                    'label' => 'List to replace with'
                ])
                ->addModelTransformer($spaceReplaceCallbackTransformer)
            )
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'icon' => 'fa-times'
            ])
        ;
        foreach ($options['languages'] as $language => $languageCode) {
            $builder
                ->add('sitename_' . $languageCode, TextType::class, [
                    'label' => 'Site name'
                ])
                ->add('slogan_' . $languageCode, TextType::class, [
                    'label' => 'Description line'
                ])
                ->add('defaultpagetitle_' . $languageCode, TextType::class, [
                    'label' => 'Default page title'
                ])
                ->add('defaultmetadescription_' . $languageCode, TextType::class, [
                    'label' => 'Default meta description'
                ])
                ->add('startController_' . $languageCode, ControllerType::class, [
                    'label' => false,
                    'required' => false,
                    'parameterTypes' => ['query', 'attributes']
                ])
            ;
        }
    }

    public function getBlockPrefix()
    {
        return 'zikulasettingsmodule_mainsettings';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'profileModules' => [],
            'messageModules' => [],
            'languages' => [],
            'constraints' => [
                new Callback(['callback' => [$this, 'validatePermalinkSettings']])
            ]
        ]);
    }

    public function validatePermalinkSettings(array $data, ExecutionContextInterface $context): void
    {
        if ('' === $data['permasearch']) {
            $permasearchCount = 0;
        } else {
            $permasearchCount = (!mb_ereg(',', $data['permasearch']) && '' !== $data['permasearch']) ? 1 : mb_substr_count($data['permasearch'], ',') + 1;
        }

        if ('' === $data['permareplace']) {
            $permareplaceCount = 0;
        } else {
            $permareplaceCount = (!mb_ereg(',', $data['permareplace']) && '' !== $data['permareplace']) ? 1 : mb_substr_count($data['permareplace'], ',') + 1;
        }

        if ($permareplaceCount !== $permasearchCount) {
            $context->addViolation($this->trans('Error! In your permalink settings, the search list and the replacement list for permalink cleansing have a different number of comma-separated elements. If you have 3 elements in the search list then there must be 3 elements in the replacement list.'));
        }
    }

    /**
     * Prepare an array of module names and displaynames for dropdown usage.
     */
    private function formatModuleArrayForSelect(array $modules = []): array
    {
        $return = [];
        foreach ($modules as $module) {
            if (!($module instanceof ExtensionEntity)) {
                $module = $this->extensionRepository->get($module);
            }
            $return[$module->getDisplayname()] = $module->getName();
        }
        ksort($return);

        return $return;
    }
}
