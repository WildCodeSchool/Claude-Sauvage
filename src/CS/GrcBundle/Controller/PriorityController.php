<?php

namespace CS\GrcBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use CS\GrcBundle\Entity\Ticket;
use CS\GrcBundle\Form\TicketType;

class PriorityController extends Controller
{
public function priorityAction(Request $request, $id)
	{ 
	$em = $this->getDoctrine()->getManager();

    $user = $this->container->get('security.context')->getToken()->getUser();
    $username = $user->getUsername();

    $priority = $em->getRepository('GrcBundle:Grcpriority')->findOneById($id);
    $mypriority = $priority->getName();
    $mytickets= $em->getRepository('GrcBundle:Ticket')->findByPriority($mypriority);


    foreach ($mytickets as $myticket){
        $ticketid = $myticket->getId();
        $ticketidcat = $myticket->getIdcategory();
        $ticketidsscat= $myticket->getIdsouscategory();
        $ticketidsender = $myticket->getIdsender();
        $ticketdate = $myticket->getDate();
        $ticketstatus = $myticket->getStatus();

        if ($ticketidcat != 0) {
            $categorie = $em->getRepository('GrcBundle:Grccategory')->findOneById($ticketidcat);
            $ticketcat = $categorie->getName();
        } else {
            $ticketcat = "Non définie";  
        }

        if ($ticketidsscat != 0) {
            $sscategorie = $em->getRepository('GrcBundle:Grcsouscategory')->findOneById($ticketidsscat);
            $ticketsscat = $sscategorie->getName();
        } else {
            $ticketsscat = "Non définie";  
        }

        $sender = $em->getRepository('AppBundle:User')->findOneById($ticketidsender);
        $ticketidsender = $sender->getUsername();

        $listepriority[]=array(
                'ticketid'=>$ticketid,
                'ticketcat'=>$ticketcat,
                'ticketsscat'=>$ticketsscat,
                'ticketidsender'=>$ticketidsender,
                'ticketdate'=>$ticketdate,
                'ticketstatus'=>$ticketstatus,
        );
    }

    if (empty($listepriority)){
        $listepriority = 0;
    }
           
    return $this->render('GrcBundle:Default:priority.html.twig', array(
        'listepriority' => $listepriority,
        'username' => $username,
        'mypriority' => $mypriority,
        ));
        
    } 
}