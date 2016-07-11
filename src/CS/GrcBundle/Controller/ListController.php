<?php

namespace CS\GrcBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use CS\GrcBundle\Entity\Ticket;
use CS\GrcBundle\Form\TicketType;
use DateTime;

class ListController extends Controller
{
	public function listeAction(Request $request)
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
            $cat = $em->getRepository('GrcBundle:Grccategory')->findOneById($idcategory);
            $catname=$cat->getName();
            
            //allez chercher la sous-catégorie 
            $sscat = $em->getRepository('GrcBundle:Grcsouscategory')->findOneById($idsscategory);
            $sscatname=$sscat->getName();

            if(empty($tagnames))
            {
                $tagnames=1;
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
        if(empty($tabfav))
        {
            $tabfav=1;
        }
        if (empty($ticketslist))
        {
          $ticketslist=1;
        }

    return $this->render('GrcBundle:Default:liste.html.twig', array(
        'ticketslist'=>$ticketslist,
        ));

    }
    public function showDashboardAction (Request $request)
    {
    	$em=$this->getDoctrine()->getManager();
    	$user=$this->getUser();
    	//comptes du haut de dashboard
    	//status
    	$toclasstickets=$em->getRepository('GrcBundle:Ticket')->findBy(
    		array('idreceiver'=>$user->getId(), 'status'=>'A traiter')
    		);
    	$nbtoclass = count($toclasstickets);
    	
       	$inprogresstickets=$em->getRepository('GrcBundle:Ticket')->findBy(
    		array('idreceiver'=>$user->getId(), 'status'=>'En cours')
    		);
    	$nbinprogress = count($inprogresstickets);

		$closedtickets=$em->getRepository('GrcBundle:Ticket')->findBy(
    		array('idreceiver'=>$user->getId(), 'status'=>'Cloturés')
    		);
    	$nbclosed = count($closedtickets);

		$archivedtickets=$em->getRepository('GrcBundle:Ticket')->findBy(
    		array('idreceiver'=>$user->getId(), 'status'=>'Archivés')
    		);
    	$nbarchived = count($archivedtickets);

    	//priorités

		$hutickets=$em->getRepository('GrcBundle:Ticket')->findBy(
    		array('idreceiver'=>$user->getId(), 'priority'=>'Très urgent')
    		);
    	$nbhu = count($hutickets);

		$urgenttickets=$em->getRepository('GrcBundle:Ticket')->findBy(
    		array('idreceiver'=>$user->getId(), 'priority'=>'Urgent')
    		);
    	$nburgent = count($urgenttickets);

		$blockingtickets=$em->getRepository('GrcBundle:Ticket')->findBy(
    		array('idreceiver'=>$user->getId(), 'priority'=>'Bloquant')
    		);
    	$nbblocking = count($blockingtickets);

		$simpletickets=$em->getRepository('GrcBundle:Ticket')->findBy(
    		array('idreceiver'=>$user->getId(), 'priority'=>'Simple')
    		);
    	$nbsimple = count($simpletickets);

		$lowtickets=$em->getRepository('GrcBundle:Ticket')->findBy(
    		array('idreceiver'=>$user->getId(), 'priority'=>'Basse')
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



    	$newtickets=$em->getRepository('GrcBundle:Ticket')->findBy(
    		array('idreceiver' => $user->getId()), // Critere
  			array('date' => 'desc'),        // Tri
  			5,                              // Limite
  			0                               // Offset
    	);
    	foreach ($newtickets as $ticket)
    	{
    		$ticketid = $ticket->getId();
    		$date=$ticket->getDate();
    		$priority=$ticket->getPriority();

    		$sender= $em->getRepository('AppBundle:User')->findOneById($ticket->getIdsender());
    		$ticketsender=$sender->getUsername();

    		$category=$em->getRepository('GrcBundle:Grccategory')->findOneById($ticket->getIdcategory());
    		$categoryname=$category->getName();

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

    	$highlyurgenttickets=$em->getRepository('GrcBundle:Ticket')->findBy(
    		array('idreceiver'=>$user->getId(), 'priority'=>'Très urgent'),
    		array('date'=>'desc'),
    		5,
    		0
    		);
    	foreach ($highlyurgenttickets as $huticket) {
    		
    		$ticketid = $ticket->getId();
    		$date=$ticket->getDate();
    		$priority=$ticket->getPriority();

    		$sender= $em->getRepository('AppBundle:User')->findOneById($ticket->getIdsender());
    		$ticketsender=$sender->getUsername();

    		$category=$em->getRepository('GrcBundle:Grccategory')->findOneById($ticket->getIdcategory());
    		$categoryname=$category->getName();

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

    	$urgenttickets=$em->getRepository('GrcBundle:Ticket')->findBy(
    		array('idreceiver'=>$user->getId(), 'priority'=>'Urgent'),
    		array('date'=>'desc'),
    		5,
    		0
    		);
    	foreach ($urgenttickets as $urgentticket) {
    		
    		$ticketid = $ticket->getId();
    		$date=$ticket->getDate();
    		$priority=$ticket->getPriority();

    		$sender= $em->getRepository('AppBundle:User')->findOneById($ticket->getIdsender());
    		$ticketsender=$sender->getUsername();

    		$category=$em->getRepository('GrcBundle:Grccategory')->findOneById($ticket->getIdcategory());
    		$categoryname=$category->getName();

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

    	$comments=$em->getRepository('GrcBundle:Comment')->findAll(
    		array('date'=>'desc')
    		);
    	$compteur=0;
    	$tab[]=array('id'=>0);
    	foreach ($comments as $comment ) {
    		$counted=0;
    		$i=0;
    		$idticket=$comment->getIdticket();
    		while($i<count($tab))
    		{
    			if( $idticket = $tab[$i]['id'] )
    			{
    				$counted=1;
    			}
    			$i++;
    		}
    		if($counted == 0 && $compteur < 5 )
    		{
    			$ticket= $em->getRepository('GrcBundle:Ticket')->findOneById($idticket);

    			$ticketid = $ticket->getId();
    			$date=$ticket->getDate();
    			$priority=$ticket->getPriority();

    			$sender= $em->getRepository('AppBundle:User')->findOneById($ticket->getIdsender());
    			$ticketsender=$sender->getUsername();

    			$category=$em->getRepository('GrcBundle:Grccategory')->findOneById($ticket->getIdcategory());
    			$categoryname=$category->getName();

    			$commentedticketstab[]=array(
    				'id'=>$ticketid,
    				'date'=>$date,
    				'priority'=>$priority,
    				'sender'=>$ticketsender,
    				'category'=>$categoryname,
    			);
    		}
    	}
    	if(empty($commentedticketstab))
    	{
    		$commentedticketstab=1;
    	}
    	return $this->render('GrcBundle:Default:index.html.twig', array(
    		'newtickets'=>$newticketstab,
    		'hutickets'=>$huticketstab,
    		'urgenttickets'=>$urgentticketstab,
    		'commentedtickets'=>$commentedticketstab,
    		'statstab'=>$statstab,
    		));
    }
}