<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\EventFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{
    /**
     * @Route("/calendar", name="app_calendar")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $events = $this->getDoctrine()->getRepository(User::class)->find($this->getUser())->getEvents();

        $calendar = [];

        foreach($events as $event){
            $calendar[]= [
                'id' => $event->getId(),
                'start' => $event->getStart()->format('Y-m-d H:i:s'),
                'end' => $event->getEnd()->format('Y-m-d H:i:s'),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
            ];
        }

        $calendar = json_encode($calendar);
        return $this->render('calendar/index.html.twig', [
            'calendar' => $calendar,
        ]);
    }

    /**
     * @Route("/calendar/add", name="event_add")
     * @param Request $request
     * @return Response
     */
    public function EventAdd(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $event = new Event();
        $form = $this->createForm(EventFormType::class, $event);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $this->getDoctrine()->getManager();
            $event->addUser($this->getUser());
            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success','Evenement ajouté avec succes');

            return $this->redirectToRoute("app_calendar");
        }

        return $this->render("calendar/event-form.html.twig", [
            "eventForm" => $form->createView(),
            'FormName' => "Ajouter un Evenement",

        ]);
    }

    /**
     * @Route("/calendar/{id}", name="event_show")
     * @param Event $event
     * @return Response
     */
    public function EventShow(Event $event): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        return $this->render("calendar/show.html.twig", [
            'event' =>$event
        ]);
    }

    /**
     * @Route("/calendar/delete/{id}", name="event_delete", methods={"DELETE"})
     * @param Request $request
     * @param Event $event
     * @return Response
     */
    public function EventDelete(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($event);
            $entityManager->flush();
        }

        $this->addFlash('success','Evenement supprimé avec succes');

        return $this->redirectToRoute('app_calendar');

    }

    /**
     * @Route("/event/edit/{id}", name="event_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Event $event
     * @return Response
     */
    public function ContactEdit(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $form = $this->createForm(EventFormType::class, $event);
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success','Evenement Edité avec succès');

            return $this->redirectToRoute('event_show', [
                'id' => $event->getId(),
            ]);
        }

        return $this->render('calendar/event-form.html.twig', [
            'event' => $event,
            'eventForm' => $form->createView(),
            'FormName' => "Editer l'évenement'",
        ]);
    }


}
