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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Zikula\BlocksModule\Block\FincludeBlock;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

class FincludeBlockType extends AbstractType
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
            ->add('filo', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new File([
                        'mimeTypes' => ['text/html', 'text/plain'],
                    ])
                ],
                'label' => $this->__('File Path'),
                'attr' => ['placeholder' => '/full/path/to/file.txt']
            ])
            ->add('typo', ChoiceType::class, [
                'choices' => [
                    'HTML' => FincludeBlock::FILETYPE_HTML,
                    'Text' => FincludeBlock::FILETYPE_TEXT,
                    'PHP' => FincludeBlock::FILETYPE_PHP
                ],
                'label' => $this->__('File type')
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
            'constraints' => new Callback(['callback' => [$this, 'validateFileAgainstMimeType']])
        ]);
    }

    /**
     * Validate file type against the mime type.
     */
    public function validateFileAgainstMimeType($data, ExecutionContextInterface $context): void
    {
        if (0 !== $data['typo'] && 'text/html' === mime_content_type($data['filo'])) {
            $context->addViolation($this->translator->trans('For Html files please select the Html file type.'));
        }
    }
}
