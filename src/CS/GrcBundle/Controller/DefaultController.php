<?php

namespace CS\GrcBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use CS\GrcBundle\Entity\Ticket;
use CS\GrcBundle\Form\TicketType;
use CS\GrcBundle\Entity\Comment;
use CS\GrcBundle\Form\CommentType;
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

		$categories = $em->getRepository('GrcBundle:Grccategory')->findAll();
		$sscategories = $em->getRepository('GrcBundle:Grcsouscategory')->findAll();

		if ($form->isSubmitted() && $form->isValid()) {
            $date = new DateTime;

            $cat = $request->request->get('cat');
            $sscat = $request->request->get('sscat');

            $em = $this->getDoctrine()->getManager();
            $ticket->setDate($date);
            $ticket->setIdsender($iduser);
            $ticket->setIdreceiver(1);
            $ticket->setIdcategory($cat);
            $ticket->setIdsouscategory($sscat);

            $ticket->setStatus("Initial");
            $ticket->setPriority("Not defined");

            $em->persist($ticket);
            $em->flush();

            return $this->redirectToRoute('grc_create_ticket');
        }

		return $this->render('GrcBundle:Default:create.html.twig', array(
		'form' => $form->createView(),
		'categories' => $categories,
		'sscategories' => $sscategories,
        ));
    }
	
	public function ticketAction(Request $request, Ticket $ticket, $id)
	{ 
	$em = $this->getDoctrine()->getManager();

    $idcat= $ticket->getIdcategory();
    $idsscat= $ticket->getIdsouscategory();
    $categorie = $em->getRepository('GrcBundle:Grccategory')->findOneById($idcat);
    $sscategorie = $em->getRepository('GrcBundle:Grcsouscategory')->findOneById($idsscat);

    return $this->render('GrcBundle:Default:ticket.html.twig', array(
        'ticket'=>$ticket,
        'categorie'=>$categorie,
        'sscategorie'=>$sscategorie,
        ));
	}

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

    public function ajaxsscatAction(Request $request)
    { 
    
    $em = $this->getDoctrine()->getManager();
    $idcategorie = $request->request->get('categorie');
    $sscategories = $em->getRepository('GrcBundle:Grcsouscategory')->findByIdcategory($idcategorie);

        foreach ($sscategories as $sscategorie){
            $id = $sscategorie->getId();
            $name = $sscategorie->getName();

            $sscatlist[]=array(
                "id"=>$id,
                "name"=>$name,
                );
        }

    $response = new JsonResponse();
    return $response->setData(array('sscatlist' => $sscatlist));
    }

    public function addCommentAction (Request $request)
    {
        $em=$this->getDoctrine()->getManager();
        $user=$this->getUser();
        
        $idticket=$request->request->get('idticket');
        $content=$request->request->get('content');

        if (!empty($content))
        {
            $comment= new Comment();
            $comment->setIdticket($idticket);
            $comment->setIdsender($user->getId());
            $comment->setContent($content);
            $comment->setDate(new DateTime());

            $em->persist($comment);
            $em->flush();
        }
        $url = $this -> generateUrl('ticket', array( 'id'=>$idticket ));
        $response = new RedirectResponse($url);
        return $response;
    }
}
