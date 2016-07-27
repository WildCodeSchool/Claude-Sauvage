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

class AllticketsController extends Controller
{
    public function allticketsAction(Request $request)
    {
    
    $em = $this->getDoctrine()->getManager();
    $user = $this->container->get('security.context')->getToken()->getUser();
    $iduser=$user->getId();
    
    $alltickets=$em->getRepository('GrcBundle:Ticket')->findAll();

    foreach ($alltickets as $ticket) {
            //recuperer l'ID du ticket
            $idticket=$ticket->getId();    
            //recuperer l'ID du ticket
            $iddemandeur=$ticket->getIdsender(); 
            //recuperer la date de creation du ticket
            $dateticket=$ticket->getDate();
            //recuperer la priorité
            $prioticket=$ticket->getPriority();
            //recuperer le status
            $statusticket=$ticket->getStatus();
            //recuperer l'ID catégorie
            $idcategory=$ticket->getIdcategory();
            //recuperer l'ID sous-catégorie
            $idsscategory=$ticket->getIdsouscategory();       
            //allez chercher la catégorie 
            if ($idcategory != 0) {
            $cat = $em->getRepository('GrcBundle:Grccategory')->findOneById($idcategory);
            $catname=$cat->getName();
            } else {
                $catname="Non définie";
            }
            //allez chercher la sous-catégorie
            if ($idsscategory != 0) {
            $sscat = $em->getRepository('GrcBundle:Grcsouscategory')->findOneById($idsscategory);
            $sscatname=$sscat->getName();
            } else {
                $sscatname="Non définie";
            }
            $ticketslist[]=array(
                "idticket"=>$idticket,
                "catname"=>$catname,
                "sscatname"=>$sscatname,
                "iddemandeur"=>$iddemandeur,
                "dateticket"=>$dateticket,
                "prioticket"=>$prioticket,
                "statusticket"=>$statusticket
                );
        }
        if (empty($ticketslist))
        {
          $ticketslist=1;
        }

    return $this->render('GrcBundle:Default:liste.html.twig', array(
        'ticketslist'=>$ticketslist,
        ));

    }
}