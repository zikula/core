<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocaleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('locale', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => __('Select your default language'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
                'choices' => $options['choices'],
                'data' => $options['choice']
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'locale';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'choices' => \ZLanguage::getInstalledLanguageNames(),
            'choice' => \ZLanguage::getLanguageCode()
//                'csrf_field_name' => '_token',
//                // a unique key to help generate the secret token
//                'intention'       => '_zk_bdcreds',
        ]);
    }
}
