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

class StatusController extends Controller
{
public function statusAction(Request $request, $id)
    { 
    $em = $this->getDoctrine()->getManager();

    $user = $this->container->get('security.context')->getToken()->getUser();
    $username = $user->getUsername();

    $status = $em->getRepository('GrcBundle:Grcstatus')->findOneById($id);
    $mystatus = $status->getName();
    $mytickets= $em->getRepository('GrcBundle:Ticket')->findByStatus($mystatus);


    foreach ($mytickets as $myticket){
        $ticketid = $myticket->getId();
        $ticketidcat = $myticket->getIdcategory();
        $ticketidsscat= $myticket->getIdsouscategory();
        $ticketidsender = $myticket->getIdsender();
        $ticketdate = $myticket->getDate();
        $ticketpriority = $myticket->getPriority();

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

        $listestatus[]=array(
                'ticketid'=>$ticketid,
                'ticketcat'=>$ticketcat,
                'ticketsscat'=>$ticketsscat,
                'ticketidsender'=>$ticketidsender,
                'ticketdate'=>$ticketdate,
                'ticketpriority'=>$ticketpriority,
        );
    }

    if (empty($listestatus)){
        $listestatus = 0;
    }
           
    return $this->render('GrcBundle:Default:status.html.twig', array(
        'listestatus' => $listestatus,
        'username' => $username,
        'mystatus' => $mystatus,
        ));
        
    } 
}