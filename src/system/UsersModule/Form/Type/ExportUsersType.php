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

namespace Zikula\UsersModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

class ExportUsersType extends AbstractType
{
    use TranslatorTrait;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', CheckboxType::class, [
                'label' => $this->__('Export title row'),
                'required' => false,
                'data' => true
            ])
            ->add('email', CheckboxType::class, [
                'label' => $this->__('Export email address'),
                'required' => false,
                'data' => true
            ])
            ->add('user_regdate', CheckboxType::class, [
                'label' => $this->__('Export registration date'),
                'required' => false,
                'data' => true
            ])
            ->add('lastlogin', CheckboxType::class, [
                'label' => $this->__('Export last login date'),
                'required' => false,
                'data' => true
            ])
            ->add('groups', CheckboxType::class, [
                'required' => false,
                'label' => $this->__('Export group memberships'),
            ])
            ->add('filename', TextType::class, [
                'label' => $this->__('CSV filename'),
                'help' => $this->__('A simple name with three letter suffix, e.g. `myfile.csv`'),
                'data' => 'user.csv',
                'constraints' => [
                    new Regex(['pattern' => '/^[\w,\s-]+\.[A-Za-z]{3}$/'])
                ]
            ])
            ->add('delimiter', ChoiceType::class, [
                'label' => $this->__('CSV delimiter'),
                'choices' => [
                    ',' => ',',
                    ';' => ';',
                    ':' => ':',
                    'tab' => '\t'
                ]
            ])
            ->add('download', SubmitType::class, [
                'label' => $this->__('Download'),
                'icon' => 'fa-download',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-default']
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulausersmodule_exportusers';
    }
}
