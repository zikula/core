<?php

namespace Acme\AddressBookModule\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Acme\AddressBookModule\Entity\ContactGroup;

/**
 * A controller for editing contact groups.
 *
 * @Route("/groups")
 */
class ContactGroupController extends Controller
{
    /**
     * Creates a new group.
     *
     * @Route("/", name="groups_create")
     * @Method("POST")
     * @Template("AcmeAddressBookModule:ContactGroup:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $form = $this->createCreateForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var ContactGroup $group */
            $group = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($group);
            $em->flush();

            $request->getSession()->getFlashBag()->set('groups/success', array(
                'heading' => 'Group created.',
                'body' => sprintf(
                    'The group "%s" was created successfully.',
                    $group->getName()
                ),
            ));

            return $this->redirect($this->generateUrl('contacts'));
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
    * Creates a form to create a group.
    *
    * @return \Symfony\Component\Form\FormInterface The form
    */
    private function createCreateForm()
    {
        $form = $this->createForm('acme_addressbook_contactgroup', null, array(
            'action' => $this->generateUrl('groups_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create', 'attr' => array('class' => 'btn-primary')));

        return $form;
    }

    /**
     * Displays a form to create a new group.
     *
     * @Route("/new", name="groups_new")
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
     * Displays a form to edit an existing group.
     *
     * @Route("/{id}/edit", name="groups_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $group = $em->getRepository('AcmeAddressBookModule:ContactGroup')->find($id);

        if (!$group) {
            throw $this->createNotFoundException('Unable to find ContactGroup group.');
        }

        $editForm = $this->createEditForm($group);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'group' => $group,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a group.
    *
    * @param ContactGroup $group The group
    *
    * @return \Symfony\Component\Form\FormInterface The form
    */
    private function createEditForm(ContactGroup $group)
    {
        $form = $this->createForm('acme_addressbook_contactgroup', $group, array(
            'action' => $this->generateUrl('groups_update', array('id' => $group->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update', 'attr' => array('class' => 'btn-primary')));

        return $form;
    }
    /**
     * Edits an existing group.
     *
     * @Route("/{id}", name="groups_update")
     * @Method("PUT")
     * @Template("AcmeAddressBookModule:ContactGroup:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var ContactGroup $group */
        $group = $em->getRepository('AcmeAddressBookModule:ContactGroup')->find($id);

        if (!$group) {
            throw $this->createNotFoundException('Unable to find ContactGroup group.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($group);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $request->getSession()->getFlashBag()->set('groups/success', array(
                'heading' => 'Group updated.',
                'body' => sprintf(
                    'The group "%s" was updated successfully.',
                    $group->getName()
                ),
            ));

            return $this->redirect($this->generateUrl('groups_edit', array('id' => $id)));
        }

        return array(
            'group' => $group,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a group.
     *
     * @Route("/{id}", name="groups_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            /** @var ContactGroup $group */
            $group = $em->getRepository('AcmeAddressBookModule:ContactGroup')->find($id);

            if (!$group) {
                throw $this->createNotFoundException('Unable to find ContactGroup group.');
            }

            $em->remove($group);
            $em->flush();

            $request->getSession()->getFlashBag()->set('groups/success', array(
                'heading' => 'Group deleted.',
                'body' => sprintf(
                    'The group "%s" was deleted successfully.',
                    $group->getName()
                ),
            ));
        }

        return $this->redirect($this->generateUrl('contacts'));
    }

    /**
     * Creates a form to delete a group.
     *
     * @param mixed $id The group id
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('groups_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete', 'attr' => array('class' => 'btn-danger')))
            ->getForm()
        ;
    }
}
