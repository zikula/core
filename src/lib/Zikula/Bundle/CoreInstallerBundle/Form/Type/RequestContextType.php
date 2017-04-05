<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Zikula\Bundle\CoreInstallerBundle\Form\AbstractType;
use Zikula\Common\Translator\IdentityTranslator;

class RequestContextType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->setTranslator($options['translator']);
        $builder
            ->add('router:request_context:host', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $this->__('The root domain where you install Zikula, e.g. "example.com". Do not include subdirectories.'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
                'data' => $this->__('localhost'),
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('router:request_context:scheme', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $this->__('Please enter the scheme of where you install Zikula, can be either "http" or "https"'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
                'choices' => [
                    'http' => 'http',
                    'https' => 'https'
                ],
                'data' => 'http',
            ])
            ->add('router:request_context:base_url', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $this->__('Please enter the url path of the directory where you install Zikula, leave empty if you install it at the top level. Example: /my/sub-dir'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'router_request_context';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => php_sapi_name() != "cli",
            'translator' => new IdentityTranslator()
        ]);
    }
}
