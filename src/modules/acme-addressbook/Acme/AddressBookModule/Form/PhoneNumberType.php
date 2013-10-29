<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Acme\AddressBookModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Acme\AddressBookModule\Entity\PhoneNumber;

/**
 * A form type for editing {@link \Acme\AddressBookModule\Entity\PhoneNumber}
 * instances.
 */
class PhoneNumberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', 'choice', array(
                'choices' => PhoneNumber::getAvailableTypeNames(),
                // Display errors in the row
                'error_bubbling' => true,
            ))
            ->add('value', null, array(
                // Display errors in the row
                'error_bubbling' => true,
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Acme\AddressBookModule\Entity\PhoneNumber',
            // Don't let erors bubble up more
            'error_bubbling' => false,
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'acme_addressbook_phonenumber';
    }
}
