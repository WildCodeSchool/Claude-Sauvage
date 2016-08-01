<?php

namespace CS\GrcBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use CS\GrcBundle\Entity\Ticket;
use CS\GrcBundle\Entity\Grccategory;


class ChartController extends Controller
{
    public function chartAction(Request $request)
    { 
    
    $em = $this->getDoctrine()->getManager();
    $listcategories = $em->getRepository('GrcBundle:Grccategory')->findAll();
    $nbticket = count($em->getRepository('GrcBundle:Ticket')->findAll());
    $nbcat = count($listcategories);

    $countedticket=0;

    foreach ($listcategories as $category){
        $catid = $category->getId();
        $catname = $category->getName();
        $nbticketbycat = count($em->getRepository('GrcBundle:Ticket')->findByIdcategory($catid));
        $countedticket += $nbticketbycat;
        $percentbycat = ($nbticketbycat/$nbticket)*100;

        $catchart[]=array(
            "name"=>$catname,
            "y"=>$percentbycat,
        );

    }
    
    // Non-defined tickets
    $catname = "Non defined";
    $percentbycat = (($nbticket-$countedticket)/$nbticket)*100;

    $catchart[]=array(
        "name"=>$catname,
        "y"=>$percentbycat,
    );

    $response = new JsonResponse();
    return $response->setData(array('catchart' => $catchart));
    }
}