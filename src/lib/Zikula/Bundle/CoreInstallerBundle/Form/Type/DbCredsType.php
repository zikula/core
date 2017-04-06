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
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Zikula\Bundle\CoreInstallerBundle\Form\AbstractType;
use Zikula\Bundle\CoreInstallerBundle\Validator\Constraints\ValidPdoConnection;
use Zikula\Common\Translator\IdentityTranslator;

class DbCredsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->setTranslator($options['translator']);
        $builder
            ->add('database_driver', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $this->__('Database type'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
                'choices' => $this->getDbTypes(),
                'choices_as_values' => true,
                'data' => 'mysql'
            ])
            ->add('dbtabletype', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $this->__('Storage Engine'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
                'choices' => [
                    'InnoDB' => 'innodb',
                    'MyISAM' => 'myisam'
                ],
                'choices_as_values' => true,
                'data' => 'innodb'
            ])
            ->add('database_host', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $this->__('Database Host'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
                'data' => 'localhost',
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('database_user', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $this->__('Database Username'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('database_password', 'Symfony\Component\Form\Extension\Core\Type\PasswordType', [
                'label' => $this->__('Database Password'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
                'required' => false
            ])
            ->add('database_name', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $this->__('Database Name'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 64]),
                    new Regex([
                        'pattern' => '/^[\w-]*$/',
                        'message' => $this->__('Error! Invalid database name. Please use only letters, numbers, "-" or "_".')
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
            $types['MySQL'] = 'mysql';
        }
        if (function_exists('mssql_connect')) {
            $types['MSSQL (alpha)'] = 'mssql';
        }
        if (function_exists('OCIPLogon')) {
            $types['Oracle (alpha) via OCI8 driver'] = 'oci8';
            $types['Oracle (alpha) via Oracle driver'] = 'oracle';
        }
        if (function_exists('pg_connect')) {
            $types['PostgreSQL'] = 'postgres';
        }

        return $types;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => new ValidPdoConnection(),
            'translator' => new IdentityTranslator()
        ]);
    }
}
