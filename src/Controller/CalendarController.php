<?php

namespace App\Controller;

use App\Entity\Event;
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
        $events = $this->getDoctrine()->getRepository(Event::class)->findAll();

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
        $contact = new Event();
        $form = $this->createForm(EventFormType::class, $contact);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contact);
            $entityManager->flush();
        }

        return $this->render("calendar/event-form.html.twig", [
            "eventForm" => $form->createView(),
            'FormName' => "Ajouter un Evenement",

        ]);
    }
}
