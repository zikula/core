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
use Zikula\BlocksModule\Block\FincludeBlock;
use Zikula\Common\Translator\IdentityTranslator;
use Zikula\Common\Translator\TranslatorInterface;

/**
 * Class FincludeBlockType
 */
class FincludeBlockType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];
        $builder
            ->add('filo', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new File([
                        'mimeTypes' => ['text/html', 'text/plain'],
                    ])
                ],
                'label' => $translator->__('File Path'),
                'attr' => ['placeholder' => '/full/path/to/file.txt']
            ])
            ->add('typo', ChoiceType::class, [
                'choices' => [
                    'HTML' => FincludeBlock::FILETYPE_HTML,
                    'Text' => FincludeBlock::FILETYPE_TEXT,
                    'PHP' => FincludeBlock::FILETYPE_PHP
                ],
                'label' => $translator->__('File type')
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
            'translator' => new IdentityTranslator()
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
            $context->addViolation($this->translator->__('For Html files please select the Html file type.'));
        }
    }
}
