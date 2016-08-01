<?php

namespace CS\GrcBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use CS\GrcBundle\Entity\Ticket;
use CS\GrcBundle\Form\TicketType;
use CS\GrcBundle\Entity\Category;


class SearchController extends Controller
{
    public function searchAction(Request $request)
    {
    
    $em = $this->getDoctrine()->getManager();
    $user = $this->container->get('security.context')->getToken()->getUser();
    $iduser=$user->getId();
    $username = $user->getUsername();
    
    $keyword = $request->request->get('keyword');
    $mysearch = $em->getRepository('GrcBundle:Ticket')->recherche($keyword);
    $listcategories = $em->getRepository('GrcBundle:Grccategory')->findAll();



    foreach ($mysearch as $ticket) {
            //recuperer l'ID du ticket
            $idticket=$ticket->getId();    
            //recuperer l'ID du ticket
            $iddemandeur = $ticket->getIdsender(); 
            $sender = $em->getRepository('AppBundle:User')->findOneById($iddemandeur);
            $usernameticket = $sender->getUsername();
            //recuperer la date du ticket
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
                "usernameticket"=>$usernameticket,
                "dateticket"=>$dateticket,
                "prioticket"=>$prioticket,
                "statusticket"=>$statusticket
                );
        }
        if (empty($searchlist))
        {
          $searchlist=0;
        }

    return $this->render('GrcBundle:Default:search.html.twig', array(
        'username'=>$username,
        'searchlist'=>$searchlist,
        'keyword'=>$keyword,
        'listcategories'=>$listcategories,
        ));

    }

    public function searchcatAction(Request $request)
    {
    
    $em = $this->getDoctrine()->getManager();
    $user = $this->container->get('security.context')->getToken()->getUser();
    $iduser=$user->getId();
    $username = $user->getUsername();

    
    $category = $request->request->get('categorysearch');
    $sscategory = $request->request->get('sscategorysearch');

    $listcategories = $em->getRepository('GrcBundle:Grccategory')->findAll();
    
    if ($category!=0){    
        if ($sscategory!=0){
        $mysearch = $em->getRepository('GrcBundle:Ticket')->findBy(
                    array('idcategory' => $category , 'idsouscategory' => $sscategory)  
                    );

        $categoryinput = $em->getRepository('GrcBundle:Grccategory')->findOneById($category);
        $sscategoryinput = $em->getRepository('GrcBundle:Grcsouscategory')->findOneById($sscategory);
        $keyword = $categoryinput->getName()." - ".$sscategoryinput->getName();

        } else {
        $mysearch = $em->getRepository('GrcBundle:Ticket')->findBy(
                    array('idcategory' => $category)      
                    );
        $categoryinput = $em->getRepository('GrcBundle:Grccategory')->findOneById($category);
        $keyword = $categoryinput->getName();
        } 
    
    }   else {
        $mysearch = 0;
        $keyword = "Pas de critères saisis";
    }

    if (count($mysearch) > 0){
        foreach ($mysearch as $ticket) {
                //recuperer l'ID du ticket
                $idticket=$ticket->getId();    
                //recuperer l'ID du ticket
                $iddemandeur = $ticket->getIdsender(); 
                $sender = $em->getRepository('AppBundle:User')->findOneById($iddemandeur);
                $usernameticket = $sender->getUsername();
                //recuperer la date du ticket
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
                    "usernameticket"=>$usernameticket,
                    "dateticket"=>$dateticket,
                    "prioticket"=>$prioticket,
                    "statusticket"=>$statusticket
                    );
        }
    } else {
        $searchlist=0;
    }


    return $this->render('GrcBundle:Default:search.html.twig', array(
        'username'=>$username,
        'searchlist'=>$searchlist,
        'keyword'=>$keyword,
        'listcategories'=>$listcategories,
        ));

    }

}