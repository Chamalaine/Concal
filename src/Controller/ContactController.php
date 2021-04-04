<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="app_contact")
     */
    public function index(): Response
    {
        $contacts = $this->getDoctrine()->getRepository(Contact::class)->findAll();


        return $this->render('contact/index.html.twig', [
            'contacts' => $contacts,
        ]);
    }

    /**
     * @Route("/contact/add", name="contact_add")
     * @param Request $request
     */
    public function ContactAdd(Request $request): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactFormType::class, $contact);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contact);
            $entityManager->flush();
        }

        return $this->render("contact/contact-form.html.twig", [
            "contactForm" => $form->createView(),
            'FormName' => "Ajouter le Contact",

        ]);
    }

    /**
     * @Route("/contact/{id}", name="contact_show")
     * @return Response
     */
    public function ContactShow(Contact $contact): Response
    {
        return $this->render("contact/show.html.twig", [
            'contact' =>$contact
        ]);
    }

    /**
     * @Route("/contact/edit/{id}", name="contact_edit", methods={"GET","POST"})
     */
    public function ContactEdit(Request $request, Contact $contact): Response
    {
        $form = $this->createForm(ContactFormType::class, $contact);
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success','Contact Edité avec succès');

            return $this->redirectToRoute('contact_show', [
                'id' => $contact->getId(),
            ]);
        }

        return $this->render('contact/contact-form.html.twig', [
            'contact' => $contact,
            'contactForm' => $form->createView(),
            'FormName' => "Editer le Contact",
        ]);
    }

    /**
     * @Route("/contact/delete/{id}", name="contact_delete", methods={"DELETE"})
     */
    public function ContactDelete(Request $request, Contact $contact): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contact->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($contact);
            $entityManager->flush();
        }

        $this->addFlash('success','Contact supprimé avec succes');

        return $this->redirectToRoute('app_contact');

    }

}
