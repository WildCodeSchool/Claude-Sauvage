<?php

namespace CS\GrcBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use CS\GrcBundle\Entity\Ticket;
use CS\GrcBundle\Form\TicketType;
use DateTime;

class ListController extends Controller
{
    public function showDashboardAction (Request $request)
    {
    	$em=$this->getDoctrine()->getManager();
    	$user=$this->getUser();
    	$user = $this->container->get('security.context')->getToken()->getUser();
        $username = $user->getUsername();
        $userid = $user->getId();


        if ($this->get('security.context')->isGranted('ROLE_COM')){
            //comptes du haut de dashboard
        	//status
        	$toclasstickets=$em->getRepository('GrcBundle:Ticket')->findBy(
        		array('status'=>'Initial')
        		);
        	$nbtoclass = count($toclasstickets);
        	
           	$inprogresstickets=$em->getRepository('GrcBundle:Ticket')->findBy(
        		array('status'=>'En cours')
        		);
        	$nbinprogress = count($inprogresstickets);

    		$closedtickets=$em->getRepository('GrcBundle:Ticket')->findBy(
        		array('status'=>'Cloturés')
        		);
        	$nbclosed = count($closedtickets);

    		$archivedtickets=$em->getRepository('GrcBundle:Ticket')->findBy(
        		array('status'=>'Archivés')
        		);
        	$nbarchived = count($archivedtickets);

        	//priorités

    		$hutickets=$em->getRepository('GrcBundle:Ticket')->findBy(
        		array('priority'=>'Très urgent')
        		);
        	$nbhu = count($hutickets);

    		$urgenttickets=$em->getRepository('GrcBundle:Ticket')->findBy(
        		array('priority'=>'Urgent')
        		);
        	$nburgent = count($urgenttickets);

    		$blockingtickets=$em->getRepository('GrcBundle:Ticket')->findBy(
        		array('priority'=>'Bloquant')
        		);
        	$nbblocking = count($blockingtickets);

    		$simpletickets=$em->getRepository('GrcBundle:Ticket')->findBy(
        		array('priority'=>'Simple')
        		);
        	$nbsimple = count($simpletickets);

    		$lowtickets=$em->getRepository('GrcBundle:Ticket')->findBy(
        		array('priority'=>'Basse')
        		);
        	$nblow = count($lowtickets);

        	$statstab=array(
        		'nbtoclass'=>$nbtoclass,
        		'nbinprogress'=>$nbinprogress,
        		'nbclosed'=>$nbclosed,
        		'nbarchived'=>$nbarchived,
        		'nbhu'=>$nbhu,
        		'nburgent'=>$nburgent,
        		'nbblocking'=>$nbblocking,
        		'nbsimple'=>$nbsimple,
        		'nblow'=>$nblow,
        		);

        	$newtickets=$em->getRepository('GrcBundle:Ticket')->newtickets();
        	
            foreach ($newtickets as $ticket)
        	{
        		$ticketid = $ticket->getId();
        		$date=$ticket->getDate();
        		$priority=$ticket->getPriority();

        		$sender= $em->getRepository('AppBundle:User')->findOneById($ticket->getIdsender());
        		$ticketsender=$sender->getUsername();

        		$category=$em->getRepository('GrcBundle:Grccategory')->findOneById($ticket->getIdcategory());
        		
                    if (!empty($category)){
                    $categoryname=$category->getName();
                    } else {
                    $categoryname="Not defined";
                    }

        		$newticketstab[]=array(
        			'id'=>$ticketid,
        			'date'=>$date,
        			'priority'=>$priority,
        			'sender'=>$ticketsender,
        			'category'=>$categoryname,
        			);
        	}
        	if(empty($newticketstab))
        	{
        		$newticketstab=1;
        	}

        	$highlyurgenttickets=$em->getRepository('GrcBundle:Ticket')->highlyurgent();
        	

            foreach ($highlyurgenttickets as $huticket) {
        		
        		$ticketid = $huticket->getId();
        		$date= $huticket->getDate();
        		$priority= $huticket->getPriority();

        		$sender= $em->getRepository('AppBundle:User')->findOneById($huticket->getIdsender());
        		$ticketsender=$sender->getUsername();

        		$category=$em->getRepository('GrcBundle:Grccategory')->findOneById($huticket->getIdcategory());
                    
                    if (!empty($category)){
                    $categoryname=$category->getName();
                    } else {
                    $categoryname="Not defined";
                    }

        		$huticketstab[]=array(
        			'id'=>$ticketid,
        			'date'=>$date,
        			'priority'=>$priority,
        			'sender'=>$ticketsender,
        			'category'=>$categoryname,
        			);
        	}
    	   	if(empty($huticketstab))
        	{
        		$huticketstab=1;
        	}

        	$urgenttickets=$em->getRepository('GrcBundle:Ticket')->urgent();


        	foreach ($urgenttickets as $urgentticket) {
        		
        		$ticketid = $urgentticket->getId();
        		$date=$urgentticket->getDate();
        		$priority=$urgentticket->getPriority();

        		$sender= $em->getRepository('AppBundle:User')->findOneById($urgentticket->getIdsender());
        		$ticketsender=$sender->getUsername();

        		$category=$em->getRepository('GrcBundle:Grccategory')->findOneById($urgentticket->getIdcategory());
                    
                    if (!empty($category)){
                    $categoryname=$category->getName();
                    } else {
                    $categoryname="Not defined";
                    }

        		$urgentticketstab[]=array(
        			'id'=>$ticketid,
        			'date'=>$date,
        			'priority'=>$priority,
        			'sender'=>$ticketsender,
        			'category'=>$categoryname,
        			);
        	}
            
        	if(empty($urgentticketstab))
        	{
        		$urgentticketstab=1;
        	}

            // Derniers commentés
        	
            $comments=$em->getRepository('GrcBundle:Comment')->newcomments();



        	foreach ($comments as $comment ) {
        		
                    $ticket= $em->getRepository('GrcBundle:Ticket')->findOneById($comment->getIdticket());
        			$ticketid = $ticket->getId();
        			$date=$ticket->getDate();
        			$priority=$ticket->getPriority();

        			$sender= $em->getRepository('AppBundle:User')->findOneById($ticket->getIdsender());
        			$ticketsender=$sender->getUsername();

        			$category=$em->getRepository('GrcBundle:Grccategory')->findOneById($ticket->getIdcategory());
                        
                        if (!empty($category)){
                        $categoryname=$category->getName();
                        } else {
                        $categoryname="Not defined";
                        }

        			$commentedticketstab[]=array(
        				'id'=>$ticketid,
        				'date'=>$date,
        				'priority'=>$priority,
        				'sender'=>$ticketsender,
        				'category'=>$categoryname,
        			);
        	}
        	
            if(empty($commentedticketstab))
        	{
        		$commentedticketstab=1;
        	}
        	return $this->render('GrcBundle:Default:index.html.twig', array(
                'username'=>$username,
                'userid'=>$userid,
        		'newtickets'=>$newticketstab,
        		'hutickets'=>$huticketstab,
        		'urgenttickets'=>$urgentticketstab,
        		'commentedtickets'=>$commentedticketstab,
        		'statstab'=>$statstab,
        		));

        } else {

            $url = $this -> generateUrl('grc_fiche_client', array( 'id'=>$userid ));
            $response = new RedirectResponse($url);
            return $response;

        }
    }
}