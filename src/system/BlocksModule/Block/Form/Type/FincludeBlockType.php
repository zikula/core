<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Block\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class FincludeBlockType
 */
class FincludeBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('filo', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new File([
                        'mimeTypes' => ['text/html', 'text/plain'],
                    ])
                ],
                'label' => __('File Path'),
                'attr' => ['placeholder' => '/full/path/to/file.txt']
            ])
            ->add('typo', ChoiceType::class, [
                'choices' => [
                    'HTML' => 0,
                    'Text' => 1,
                    'PHP' => 2
                ],
                'choices_as_values' => true, // defaults to true in Sy3.0
                'label' => __('File type')
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulablocksmodule_fincludeblock';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // add a constraint to the entire form
        $resolver->setDefaults([
            'constraints' => new Callback(['callback' => [$this, 'validateFileAgainstMimeType']]),
        ]);
    }

    /**
     * Validate file type against the mime type.
     *
     * @param $data
     * @param ExecutionContextInterface $context
     */
    public function validateFileAgainstMimeType($data, ExecutionContextInterface $context)
    {
        if (('text/html' == mime_content_type($data['filo'])) && (0 !== $data['typo'])) {
            $context->addViolation(__('For Html files please select the Html file type.'));
        }
    }
}
