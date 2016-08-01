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

class AjaxsscatController extends Controller
{
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

        if(empty($sscatlist)){
            $sscatlist= 0 ;
        }

    $response = new JsonResponse();
    return $response->setData(array('sscatlist' => $sscatlist));
    }
}