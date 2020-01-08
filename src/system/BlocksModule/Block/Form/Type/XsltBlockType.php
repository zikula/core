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

namespace Zikula\BlocksModule\Block\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

class XsltBlockType extends AbstractType
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('docurl', TextType::class, [
                'constraints' => [
                    new Url()
                ],
                'required' => false,
                'label' => $this->trans('Document URL')
            ])
            ->add('doccontents', TextareaType::class, [
                'label' => $this->trans('Document contents'),
                'required' => false,
                'attr' => [
                    'rows' => 15
                ]
            ])
            ->add('styleurl', TextType::class, [
                'constraints' => [
                    new Url()
                ],
                'required' => false,
                'label' => $this->trans('Style sheet URL')
            ])
            ->add('stylecontents', TextareaType::class, [
                'label' => $this->trans('Style sheet contents'),
                'required' => false,
                'attr' => [
                    'rows' => 15
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // add a constraint to the entire form
        $resolver->setDefaults([
            'constraints' => new Callback(['callback' => [$this, 'validateOrFields']])
        ]);
    }

    public function getBlockPrefix()
    {
        return 'zikulablocksmodule_xsltblock';
    }

    /**
     * Validation method for entire form.
     */
    public function validateOrFields($data, ExecutionContextInterface $context): void
    {
        if (empty($data['docurl']) && empty($data['doccontents'])) {
            $context->addViolation($this->trans('Either the Document URL or the Document contents must contain a value.'));
        }
        if (!empty($data['docurl']) && !empty($data['doccontents'])) {
            $context->addViolation($this->trans('Either the Document URL of the Document contents can contain a value, not both.'));
        }
        if (empty($data['styleurl']) && empty($data['stylecontents'])) {
            $context->addViolation($this->trans('Either the Style sheet URL or the Style sheet contents must contain a value.'));
        }
        if (!empty($data['styleurl']) && !empty($data['stylecontents'])) {
            $context->addViolation($this->trans('Either the Style sheet URL or the Style sheet contents can contain a value, not both.'));
        }
    }
}
