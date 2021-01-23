<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Form\Type;

use PDO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Translation\Extractor\Annotation\Ignore;
use Zikula\Bundle\CoreInstallerBundle\Validator\Constraints\ValidPdoConnection;

class DbCredsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('database_driver', ChoiceType::class, [
                'label' => 'Database type',
                'label_attr' => [
                    'class' => 'col-md-3'
                ],
                'choices' => /** @Ignore */ $this->getDbTypes(),
                'data' => 'mysql'
            ])
            ->add('database_host', TextType::class, [
                'label' => 'Database host',
                'label_attr' => [
                    'class' => 'col-md-3'
                ],
                'data' => 'localhost',
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('database_port', IntegerType::class, [
                'label' => 'Database port',
                'label_attr' => [
                    'class' => 'col-md-3'
                ],
                'required' => false,
                'help' => 'Enter custom port number or leave empty for default.'
            ])
            ->add('database_user', TextType::class, [
                'label' => 'Database user name',
                'label_attr' => [
                    'class' => 'col-md-3'
                ],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('database_password', PasswordType::class, [
                'label' => 'Database password',
                'label_attr' => [
                    'class' => 'col-md-3'
                ],
                'required' => false
            ])
            ->add('database_name', TextType::class, [
                'label' => 'Database name',
                'label_attr' => [
                    'class' => 'col-md-3'
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 64]),
                    new Regex([
                        'pattern' => '/^[\w-]*$/',
                        'message' => 'Error! Invalid database name. Please use only letters, numbers, "-" or "_".'
                    ])
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'dbcreds';
    }

    private function getDbTypes(): array
    {
        $availableDrivers = PDO::getAvailableDrivers();

        $types = [];
        if (in_array('mysql', $availableDrivers, true)) {
            $types['MySQL'] = 'mysql';
        }
        if (in_array('sqlsrv', $availableDrivers, true)) {
            $types['MSSQL (alpha)'] = 'mssql';
        }
        if (in_array('oci8', $availableDrivers, true)) {
            $types['Oracle (alpha) via OCI8 driver'] = 'oci8';
        } elseif (in_array('oci', $availableDrivers, true)) {
            $types['Oracle (alpha) via Oracle driver'] = 'oracle';
        }
        if (in_array('pgsql', $availableDrivers, true)) {
            $types['PostgreSQL'] = 'pgsql';
        }

        return $types;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => new ValidPdoConnection()
        ]);
    }
}
