<?php

namespace Acme\AddressBookModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * A form type for editing {@link \Acme\AddressBookModule\Entity\ContactGroup}
 * instances.
 */
class ContactGroupType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Acme\AddressBookModule\Entity\ContactGroup'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'acme_addressbook_contactgroup';
    }
}
