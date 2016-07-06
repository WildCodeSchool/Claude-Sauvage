<?php

namespace CS\GrcBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use CS\GrcBundle\Entity\Ticket;
use CS\GrcBundle\Form\TicketType;
use DateTime;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('GrcBundle:Default:index.html.twig');
    }
    
    public function createAction(Request $request)
    {      
		$ticket = new Ticket();
		$form = $this->createForm(new TicketType(), $ticket);
		$form->handleRequest($request);

		$em = $this->getDoctrine()->getManager();
		$user = $this->container->get('security.context')->getToken()->getUser();
		$iduser = $user->getId();

		if ($form->isSubmitted() && $form->isValid()) {
            $date = new DateTime;
            $em = $this->getDoctrine()->getManager();
            $ticket->setDate($date);
            $ticket->setIdsender($iduser);

            $em->persist($ticket);
            $em->flush();

            return $this->redirectToRoute('grc_create_ticket');
        }

		return $this->render('GrcBundle:Default:create.html.twig', array(
		'form' => $form->createView(),
        ));
    }
	
	public function ajaxcatAction(Request $request)
	{
	
	$em = $this->getDoctrine()->getManager();
	$listcat=$em->getRepository('GrcBundle::Grccategory')->findAll();

	var_dump($listcat);
	$json = array();

	foreach ($listcat as $cat) {
		$id=$cat->getId();
		$name=$cat->getName();
		$json[$id][] = $name;
	}

	// envoi du résultat au success
	echo json_encode($json);

	}

	public function ticketAction(Request $request)
	{
	
	$em = $this->getDoctrine()->getManager();
	return $this->render('GrcBundle:Default:ticket.html.twig');

	}
}
