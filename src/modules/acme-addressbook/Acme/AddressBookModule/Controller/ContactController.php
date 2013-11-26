<?php

namespace Acme\AddressBookModule\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Acme\AddressBookModule\Entity\Contact;

/**
 * A controller for editing contacts.
 *
 * @Route("/contacts")
 */
class ContactController extends Controller
{
    /**
     * Lists all contacts.
     *
     * @Route("/", name="contacts")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $contacts = $em->getRepository('AcmeAddressBookModule:Contact')
            ->createQueryBuilder('c')
            ->orderBy('c.lastName', 'ASC')
            ->getQuery()
            ->execute();

        $groups = $em->getRepository('AcmeAddressBookModule:ContactGroup')
            ->createQueryBuilder('g')
            ->orderBy('g.name', 'ASC')
            ->getQuery()
            ->execute();

        return array(
            'contacts' => $contacts,
            'groups' => $groups,
        );
    }

    /**
     * Creates a new contact.
     *
     * @Route("/", name="contacts_create")
     * @Method("POST")
     * @Template("AcmeAddressBookModule:Contact:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $form = $this->createCreateForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var Contact $contact */
            $contact = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($contact);
            $em->flush();

            $request->getSession()->getFlashBag()->set('contacts/success', array(
                'heading' => 'Contact created.',
                'body' => sprintf(
                    'The contact "%s" was created successfully.',
                    $contact->getFullName()
                ),
            ));

            return $this->redirect($this->generateUrl('contacts'));
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
    * Creates a form to create a contact.
    *
    * @return \Symfony\Component\Form\FormInterface The form
    */
    private function createCreateForm()
    {
        $form = $this->createForm('acme_addressbook_contact', null, array(
            'action' => $this->generateUrl('contacts_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create', 'attr' => array('class' => 'btn-primary')));

        return $form;
    }

    /**
     * Displays a form to create a new contact.
     *
     * @Route("/new", name="contacts_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $form = $this->createCreateForm();

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing contact.
     *
     * @Route("/{id}/edit", name="contacts_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $contact = $em->getRepository('AcmeAddressBookModule:Contact')->find($id);

        if (!$contact) {
            throw $this->createNotFoundException('Unable to find Contact contact.');
        }

        $editForm = $this->createEditForm($contact);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'contact' => $contact,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Creates a form to edit a contact.
     *
     * @param Contact $contact The contact
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createEditForm(Contact $contact)
    {
        $form = $this->createForm('acme_addressbook_contact', $contact, array(
            'action' => $this->generateUrl('contacts_update', array('id' => $contact->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update', 'attr' => array('class' => 'btn-primary')));

        return $form;
    }

    /**
     * Edits an existing contact.
     *
     * @Route("/{id}", name="contacts_update")
     * @Method("PUT")
     * @Template("AcmeAddressBookModule:Contact:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Contact $contact */
        $contact = $em->getRepository('AcmeAddressBookModule:Contact')->find($id);

        if (!$contact) {
            throw $this->createNotFoundException('Unable to find Contact contact.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($contact);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $request->getSession()->getFlashBag()->set('contacts/success', array(
                'heading' => 'Contact updated.',
                'body' => sprintf(
                    'The contact "%s" was updated successfully.',
                    $contact->getFullName()
                ),
            ));

            return $this->redirect($this->generateUrl('contacts_edit', array('id' => $id)));
        }

        return array(
            'contact' => $contact,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a contact.
     *
     * @Route("/{id}", name="contacts_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            /** @var Contact $contact */
            $contact = $em->getRepository('AcmeAddressBookModule:Contact')->find($id);

            if (!$contact) {
                throw $this->createNotFoundException('Unable to find Contact contact.');
            }

            $em->remove($contact);
            $em->flush();

            $request->getSession()->getFlashBag()->set('contacts/success', array(
                'heading' => 'Contact deleted.',
                'body' => sprintf(
                    'The contact "%s" was deleted successfully.',
                    $contact->getFullName()
                ),
            ));
        }

        return $this->redirect($this->generateUrl('contacts'));
    }

    /**
     * Creates a form to delete a contact.
     *
     * @param mixed $id The contact's ID
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('contacts_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete', 'attr' => array('class' => 'btn-danger')))
            ->getForm()
        ;
    }
}
