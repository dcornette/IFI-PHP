<?php

namespace AppBundle\Form;

use AppBundle\Entity\Contact;
use Doctrine\ORM\EntityManager;
use Swift_Mailer as Mailer;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ContactHandler
{
    protected $request;
    protected $form;
    protected $mailer;
    protected $templating;
    protected $em;

    public function __construct(FormInterface $form, Request $request, Mailer $mailer,EngineInterface $templating, EntityManager $em)
    {
        $this->form = $form;
        $this->request = $request;
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->em = $em;
    }

    /**
     * Test if form is submitted and validated
     *
     * @return bool
     */
    public function process()
    {
        if ($this->request->isMethod('post')) {

            $this->form->handleRequest($this->request);
            if ($this->form->isSubmitted() && $this->form->isValid()) {
                $this->onSuccess($this->form->getData());
                return true;
            }
        }
        return false;
    }


    /**
     * Send mail to administrator
     * @param Contact $contact
     */
    protected function onSuccess(Contact $contact)
    {
        $this->em->persist($contact);
        $this->em->flush();

        $message = \Swift_Message::newInstance()
            ->setSubject('New contact added')
            ->setFrom('no-reply@site.com')
            ->setTo('admin@site.com')
            ->setBody(
                $this->templating->render(
                    'email/new_contact.html.twig'
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }
}