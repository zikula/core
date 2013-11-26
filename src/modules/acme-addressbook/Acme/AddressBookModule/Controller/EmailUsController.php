<?php

namespace Acme\AddressBookModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * A controller for emailing the website administration.
 */
class EmailUsController extends Controller
{
    const SUBJECT_PREFIX = 'New web message: ';

    const RECIPIENT_ADDRESS = 'admin@acme.org';

    /**
     * Displays a form for sending a message to the administrator.
     *
     * @Route("/email-us", name="email_us")
     * @Method("GET")
     * @Template("AcmeAddressBookModule:EmailUs:compose.html.twig")
     */
    public function composeAction()
    {
        $form = $this->createEmailUsForm();

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Receives the form submission with the sent message.
     *
     * @Route("/email-us", name="email_us_send")
     * @Method("POST")
     * @Template("AcmeAddressBookModule:EmailUs:compose.html.twig")
     */
    public function sendAction(Request $request)
    {
        $form = $this->createEmailUsForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $body = $this->renderView(
                'AcmeAddressBookModule:EmailUs:email.txt.twig',
                array(
                    'senderName' => $form->get('senderName')->getData(),
                    'message' => $form->get('message')->getData(),
                )
            );

            $message = \Swift_Message::newInstance()
                ->setSubject(self::SUBJECT_PREFIX.$form->get('subject')->getData())
                ->setFrom($form->get('senderAddress')->getData())
                ->setTo(self::RECIPIENT_ADDRESS)
                ->setBody($body)
            ;

            $this->get('mailer')->send($message);

            $request->getSession()->getFlashBag()->set('emailUs/message', $form->getData());

            return $this->redirect($this->generateUrl('email_us_success'));
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Informs the sender that the mail was sent successfully.
     *
     * @Route("/email-us/success", name="email_us_success")
     * @Method("GET")
     * @Template("AcmeAddressBookModule:EmailUs:success.html.twig")
     */
    public function successAction(Request $request)
    {
        if (!$request->getSession()->getFlashBag()->has('emailUs/message')) {
            return $this->redirect($this->generateUrl('email_us'));
        }

        $message = $request->getSession()->getFlashBag()->get('emailUs/message');

        return array(
            'message' => $message,
        );
    }

    /**
     * Creates a form for sending a mail.
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createEmailUsForm()
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('email_us_send'))
            ->setMethod('POST')
            ->add('senderName', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 3, 'minMessage' => 'Please enter a name with at least three characters.')),
                )
            ))
            ->add('senderAddress', 'email', array(
                'constraints' => array(
                    new NotBlank(),
                    new Email(),
                )
            ))
            ->add('subject', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 5, 'minMessage' => 'Please enter a subject with at least five characters.')),
                )
            ))
            ->add('message', 'textarea', array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 20, 'minMessage' => 'Please enter a text with at least twenty characters.')),
                )
            ))
            ->add('send', 'submit', array(
                'attr' => array('class' => 'btn-primary'),
            ))
            ->getForm();
    }
}
