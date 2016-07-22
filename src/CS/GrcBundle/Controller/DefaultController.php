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
        'mycategory'=>$mycategory,
        'mysscategory'=>$mysscategory,
        'listcomments'=>$listcomments,
        'liststatus'=>$liststatus,
        'listpriorities'=>$listpriorities,
        'listcategories'=>$listcategories,
        'listsscategories'=>$listsscategories,
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

    public function searchAction(Request $request)
    {
    
    $em = $this->getDoctrine()->getManager();
    $user = $this->container->get('security.context')->getToken()->getUser();
    $iduser=$user->getId();
    
    $keyword = $request->request->get('keyword');
    $mysearch = $em->getRepository('GrcBundle:Ticket')->recherche($keyword);

    var_dump($mysearch);

    foreach ($mysearch as $ticket) {
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

            $searchlist[]=array(
                "idticket"=>$idticket,
                "catname"=>$catname,
                "sscatname"=>$sscatname,
                "iddemandeur"=>$iddemandeur,
                "dateticket"=>$dateticket,
                "prioticket"=>$prioticket,
                "statusticket"=>$statusticket
                );
        }
        if (empty($searchlist))
        {
          $searchlist=1;
        }

    return $this->render('GrcBundle:Default:search.html.twig', array(
        'searchlist'=>$searchlist,
        'keyword'=>$keyword,
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

    public function ajaxparamticketAction(Request $request)
    { 
    
    $em = $this->getDoctrine()->getManager();
    $status = $request->request->get('status');
    $priority = $request->request->get('priority');
    $category = $request->request->get('category');
    $sscategory = $request->request->get('sscategory');
    $ticketid = $request->request->get('ticketid');

    $ticket = $em->getRepository('GrcBundle:Ticket')->findOneById($ticketid);

    if ($status != 0){
        $mystatus = $em->getRepository('GrcBundle:Grcstatus')->findOneById($status);
        $statusname = $mystatus->getName();
        $ticket->setStatus($statusname);
       } else {
        $statusname = "0";
       }

    if ($priority != 0){
        $mypriority = $em->getRepository('GrcBundle:Grcpriority')->findOneById($priority);
        $priorityname = $mypriority->getName();
        $ticket->setPriority($priorityname);
       } else {
        $priorityname = "0";
       }

    if ($category != 0){
        $mycategory = $em->getRepository('GrcBundle:Grccategory')->findOneById($category);
        $categoryid = $mycategory->getId();
        $categoryname = $mycategory->getName();
        $ticket->setIdcategory($categoryid);
            if ($sscategory != 0){
                $mysscategory = $em->getRepository('GrcBundle:Grcsouscategory')->findOneById($sscategory);
                $sscategoryid = $mysscategory->getId();
                $sscategoryname = $mysscategory->getName();
                $ticket->setIdsouscategory($sscategoryid);
            } else {
                $ticket->setIdsouscategory(0);
                $sscategoryname = "Non définie";
       }
       } else {
        $categoryname = "0";
        $sscategoryname = "0";
       }

    $em->persist($ticket);
    $em->flush();

    $update[]=array(
            "status"=>$statusname,
            "priority"=>$priorityname,
            "category"=>$categoryname,
            "sscategory"=>$sscategoryname,
            );

    $response = new JsonResponse();
    return $response->setData(array('update' => $update));
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
