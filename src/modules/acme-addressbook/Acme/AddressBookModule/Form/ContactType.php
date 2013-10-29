<?php

namespace Acme\AddressBookModule\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * A form type for editing {@link \Acme\AddressBookModule\Entity\Contact}
 * instances.
 */
class ContactType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('address')
            ->add('phoneNumbers', 'collection', array(
                'type' => 'acme_addressbook_phonenumber',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            ->add('emails', 'collection', array(
                'type' => 'acme_addressbook_email',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            ->add('groups', null, array(
                'expanded' => true,
                'property' => 'name',
                'query_builder' => function (EntityRepository $repo) {
                    return $repo->createQueryBuilder('g')
                        ->orderBy('g.name', 'ASC');
                },
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Acme\AddressBookModule\Entity\Contact'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'acme_addressbook_contact';
    }
}
