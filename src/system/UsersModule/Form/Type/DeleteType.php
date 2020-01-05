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

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\UsersModule\Entity\UserEntity;

class DeleteType extends AbstractType
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
            ->add('users', EntityType::class, [
                'choice_attr' => static function() {
                    return ['class' => 'user-checkboxes'];
                },
                'class' => UserEntity::class,
                'choices' => $options['choices'],
                'expanded' => true,
                'multiple' => true,
                'choice_label' => 'uname'
            ])
            ->add('delete', SubmitType::class, [
                'label' => $this->__('Delete selected users'),
                'icon' => 'fa-trash-alt',
                'attr' => [
                    'class' => 'btn btn-danger'
                ],
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_delete';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [],
            'attr' => ['id' => 'users_searchresults']
        ]);
    }
}
