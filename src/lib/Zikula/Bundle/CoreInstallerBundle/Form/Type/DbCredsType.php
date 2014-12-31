<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula CoreInstaller bundle.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DbCredsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('database_driver', 'choice', array(
                'label' => __('Database type'),
                'label_attr' => array('class' => 'col-lg-5'),
                'choices' => $this->getDbTypes(),
                'data' => 'mysql'))
            ->add('dbtabletype', 'choice', array(
                'label' => __('Database table type (MySQL only)'),
                'label_attr' => array('class' => 'col-lg-5'),
                'choices' => array(
                    'innodb' => __('InnoDB'),
                    'myisam' => __('MyISAM')),
                'data' => 'myisam'))
            ->add('database_host', 'text', array(
                'label' => __('Host'),
                'label_attr' => array('class' => 'col-lg-5'),
                'data' => __('localhost'),
                'constraints' => array(
                    new NotBlank(),
                )))
            ->add('database_user', 'text', array(
                'label' => __('DB Username'),
                'label_attr' => array('class' => 'col-lg-5'),
                'constraints' => array(
                    new NotBlank(),
                )))
            ->add('database_password', 'password', array(
                'label' => __('DB Password'),
                'label_attr' => array('class' => 'col-lg-5'),
                'constraints' => array(
                    new NotBlank(),
                )))
            ->add('database_name', 'text', array(
                'label' => __('DB Name'),
                'label_attr' => array('class' => 'col-lg-5'),
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('max' => 64)),
                    new Regex(array(
                        'pattern' => '/^[\w-]*$/',
                        'message' => __('Error! Invalid database name. Please use only letters, numbers, "-" or "_".')
                    )),
                )))
            ->add('save', 'submit', array('label' => __('Next')));
    }

    public function getName()
    {
        return 'dbcreds';
    }

    private function getDbTypes()
    {
        $types = array();
        if (function_exists('mysql_connect')) {
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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // add a constraint to the entire form
        // thanks to @Matt Daum : http://shout.setfive.com/2013/06/27/symfony2-forms-without-an-entity-and-with-a-conditional-validator/
        $resolver->setDefaults(array(
                'constraints' => new Callback(array('callback' => array('Zikula\Bundle\CoreInstallerBundle\Validator\PdoConnectionValidator', 'validate'))),
            )
        );
    }
}