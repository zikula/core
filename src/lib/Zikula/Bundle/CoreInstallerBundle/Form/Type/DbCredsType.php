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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class DbCredsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('database_driver', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => __('Database type'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
                'choices' => $this->getDbTypes(),
                'data' => 'mysql'
            ])
            ->add('dbtabletype', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => __('Storage Engine'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
                'choices' => [
                    'innodb' => __('InnoDB'),
                    'myisam' => __('MyISAM')
                ],
                'data' => 'innodb'
            ])
            ->add('database_host', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => __('Database Host'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
                'data' => __('localhost'),
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('database_user', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => __('Database Username'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('database_password', 'Symfony\Component\Form\Extension\Core\Type\PasswordType', [
                'label' => __('Database Password'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
                'required' => false
            ])
            ->add('database_name', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => __('Database Name'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 64]),
                    new Regex([
                        'pattern' => '/^[\w-]*$/',
                        'message' => __('Error! Invalid database name. Please use only letters, numbers, "-" or "_".')
                    ])
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'dbcreds';
    }

    private function getDbTypes()
    {
        $types = [];
        if (function_exists('mysql_connect') || function_exists('mysqli_connect')) {
            $types['mysql'] = __('MySQL');
        }
        if (function_exists('mssql_connect')) {
            $types['mssql'] = __('MSSQL (alpha)');
        }
        if (function_exists('OCIPLogon')) {
            $types['oci8'] = __('Oracle (alpha) via OCI8 driver');
            $types['oracle'] = __('Oracle (alpha) via Oracle driver');
        }
        if (function_exists('pg_connect')) {
            $types['postgres'] = __('PostgreSQL');
        }

        return $types;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // add a constraint to the entire form
        // thanks to @Matt Daum : http://shout.setfive.com/2013/06/27/symfony2-forms-without-an-entity-and-with-a-conditional-validator/
        $resolver->setDefaults([
            'constraints' => new Callback(['callback' => ['Zikula\Bundle\CoreInstallerBundle\Validator\CoreInstallerValidator', 'validatePdoConnection']]),
            'csrf_protection' => false,
//                'csrf_field_name' => '_token',
//                // a unique key to help generate the secret token
//                'intention'       => '_zk_bdcreds',
        ]);
    }
}
