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

class TicketController extends Controller
{
public function ticketAction(Request $request, Ticket $ticket, $id)
	{ 
	$em = $this->getDoctrine()->getManager();

    $user = $this->container->get('security.context')->getToken()->getUser();
    $iduser = $user->getUsername();
    $currentuserid = $user->getId();
    $ticketuserid = $ticket->getIdsender();
    $iscommercial= $this->get('security.context')->isGranted('ROLE_COM');

    if ($currentuserid == $ticketuserid OR $iscommercial) 
        {
            $idcat= $ticket->getIdcategory();
            $idsscat= $ticket->getIdsouscategory();
            $idsender = $ticket->getIdsender();
            $senderuser = $em->getRepository('AppBundle:User')->findOneById($idsender);
            $senderusername = $senderuser->getUSername();
            
            if ($idcat != 0) {
            $categorie = $em->getRepository('GrcBundle:Grccategory')->findOneById($idcat);
            $mycategory = $categorie->getName();
            } else {
                $mycategory = "Non définie";  
            }
            if ($idsscat != 0) {
            $sscategorie = $em->getRepository('GrcBundle:Grcsouscategory')->findOneById($idsscat);
            $mysscategory = $sscategorie->getName();
            } else {
                $mysscategory = "Non définie";
            }

            if (!empty($ticket->getPath())){
                $mypath = $ticket->getPath();
                $myoriginalname = $ticket->getOriginalname();   
            } else {
                $mypath = "0";
                $myoriginalname = "0";
            }

            $liststatus = $em->getRepository('GrcBundle:Grcstatus')->findAll();
            $listpriorities = $em->getRepository('GrcBundle:Grcpriority')->findAll();
            $listcategories = $em->getRepository('GrcBundle:Grccategory')->findAll();
            $listsscategories = $em->getRepository('GrcBundle:Grcsouscategory')->findAll();

            $listcomments = $em->getRepository('GrcBundle:Comment')->findBy(
                array('idticket'=>$id),
                array('date'=>'desc')
            );


            return $this->render('GrcBundle:Default:ticket.html.twig', array(
                'ticket'=>$ticket,
                'iduser'=>$iduser,
                'senderusername'=>$senderusername,
                'mycategory'=>$mycategory,
                'mysscategory'=>$mysscategory,
                'mypath' =>$mypath,
                'myoriginalname' =>$myoriginalname,
                'listcomments'=>$listcomments,
                'liststatus'=>$liststatus,
                'listpriorities'=>$listpriorities,
                'listcategories'=>$listcategories,
                'listsscategories'=>$listsscategories,
                ));
        } else {
            $url = $this -> generateUrl('grc_fiche_client', array( 'id'=>$currentuserid ));
            $response = new RedirectResponse($url);
            return $response;
        }
    } 
}