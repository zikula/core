<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\BlocksModule\Block\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class FincludeBlockType
 * @package Zikula\BlocksModule\Block\Form\Type
 */
class FincludeBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('filo', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'constraints' => [
                    new NotBlank(),
                    new File([
                        'mimeTypes' => ["text/html", "text/plain"],
                    ])
                ],
                'label' => __('File Path'),
                'attr' => ['placeholder' => '/full/path/to/file.txt']
            ])
            ->add('typo', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
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

    public function getName()
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
        if (("text/html" == mime_content_type($data['filo'])) && (0 !== $data['typo'])) {
            $context->addViolation(__('For Html files please select the Html file type.'));
        }
    }
}
