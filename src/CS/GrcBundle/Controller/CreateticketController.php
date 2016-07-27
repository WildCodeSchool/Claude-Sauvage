<?php

namespace CS\GrcBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use CS\GrcBundle\Entity\Ticket;
use CS\GrcBundle\Form\TicketType;
use CS\GrcBundle\Entity\Comment;
use CS\GrcBundle\Form\CommentType;
use DateTime;

class CreateticketController extends Controller
{
public function createticketAction(Request $request)
    {      
		$ticket = new Ticket();
		$form = $this->createForm(new TicketType(), $ticket);
		$form->handleRequest($request);

		$em = $this->getDoctrine()->getManager();
		$user = $this->container->get('security.context')->getToken()->getUser();
		$iduser = $user->getId();

		$categories = $em->getRepository('GrcBundle:Grccategory')->findAll();
		$sscategories = $em->getRepository('GrcBundle:Grcsouscategory')->findAll();

		if ($form->isSubmitted() && $form->isValid()) {
            $date = new DateTime;

            $cat = $request->request->get('cat');
            $sscat = $request->request->get('sscat');
            
            if (!empty($ticket->getPath())){
                $originalgetting=$form->getNormData()->getPath('originalName');
                $originalname=$originalgetting->getClientOriginalName();
                $file = $ticket->getPath();
                $type = $file->guessExtension();
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $pathDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads';
                $file->move($pathDir, $fileName);
            }

            $em = $this->getDoctrine()->getManager();
            $ticket->setDate($date);
            $ticket->setIdsender($iduser);
            $ticket->setIdreceiver(1);
            $ticket->setIdcategory($cat);
            $ticket->setIdsouscategory($sscat);

            $ticket->setStatus("Initial");
            $ticket->setPriority("Not defined");
            
            if (!empty($ticket->getPath())){
                $ticket->setOriginalname($originalname);
                $ticket->setType($type);
                $ticket->setPath($fileName);
            }

            $em->persist($ticket);
            $em->flush();

            return $this->redirectToRoute('grc_create_ticket');
        }

		return $this->render('GrcBundle:Default:createticket.html.twig', array(
		'form' => $form->createView(),
		'categories' => $categories,
		'sscategories' => $sscategories,
        ));
    }
}