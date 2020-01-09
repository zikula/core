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

namespace Zikula\ZAuthModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

class TogglePasswordConfirmationType extends AbstractType
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uid', HiddenType::class)
            ->add('toggle', SubmitType::class, [
                'label' => $options['mustChangePass']
                    ? $this->trans('Yes, cancel the change of password')
                    : $this->trans('Yes, force the change of password'),
                'icon' => $options['mustChangePass'] ? 'fa-times' : 'fa-sync',
                'attr' => ['class' => 'btn btn-success'],
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->trans('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-default']
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_togglepassconfirmation';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'mustChangePass' => true
        ]);
    }
}
