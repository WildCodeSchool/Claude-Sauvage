<?php

namespace CS\GrcBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use CS\GrcBundle\Entity\Ticket;
use CS\GrcBundle\Form\TicketType;
use CS\GrcBundle\Entity\Grccategory;

use DateTime;

class AllticketsController extends Controller
{
    
  /**
   * @Security("has_role('ROLE_COM')")
   */
    public function allticketsAction(Request $request)
    {
    
    $em = $this->getDoctrine()->getManager();
    $user = $this->container->get('security.context')->getToken()->getUser();
    $userid = $user->getId();
    $username = $user->getUsername();
    
    $listcategories = $em->getRepository('GrcBundle:Grccategory')->findAll();
    $alltickets=$em->getRepository('GrcBundle:Ticket')->findAll();

    foreach ($alltickets as $ticket) {
            //recuperer l'ID du ticket
            $idticket=$ticket->getId();    
            //recuperer l'ID du ticket
            $iddemandeur = $ticket->getIdsender(); 
            $sender = $em->getRepository('AppBundle:User')->findOneById($iddemandeur);
            $usernameticket = $sender->getUsername();
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
                "usernameticket"=>$usernameticket,
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
        'listcategories'=>$listcategories,
        'ticketslist'=>$ticketslist,
        'username'=>$username,
        "userid"=>$userid,
        ));

    }
}