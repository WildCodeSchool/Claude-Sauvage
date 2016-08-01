<?php

namespace CS\GrcBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use CS\GrcBundle\Entity\Grccategory;
use CS\GrcBundle\Entity\Grcsouscategory;


class CreatecatController extends Controller
{
public function createcatAction(Request $request)
    {      
		$em = $this->getDoctrine()->getManager();
        $listcat = $em->getRepository('GrcBundle:Grccategory')->findAll();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $username = $user->getUsername();
        $userid = $user->getId();

        return $this->render('GrcBundle:Default:createcat.html.twig', array(
            'listcat' => $listcat,
            'username'=> $username,
            'userid'=> $userid,
        ));
    }
public function submitcatAction(Request $request)
    {      
        $em = $this->getDoctrine()->getManager();
        $listcat = $em->getRepository('GrcBundle:Grccategory')->findAll();
        
        $newcatname=$request->request->get('addcat');
        $newcategory = new Grccategory;
        $newcategory->setName($newcatname);
        
        $em->persist($newcategory);
        $em->flush();

        $this->get('session')->getFlashBag()->set('category', 'Catégorie crée !');

        return $this->redirectToRoute('grc_create_cat');
    }
public function submitsscatAction(Request $request)
    {      
        $em = $this->getDoctrine()->getManager();
        $listcat = $em->getRepository('GrcBundle:Grccategory')->findAll();
        
        $catselected=$request->request->get('cat_selected');
        $newsscatname=$request->request->get('addsscat');
        
        $newsscategory = new Grcsouscategory;
        $newsscategory->setIdcategory($catselected);
        $newsscategory->setName($newsscatname);
        
        $em->persist($newsscategory);
        $em->flush();

        $this->get('session')->getFlashBag()->set('sscategory', 'Sous-catégorie crée !');

        return $this->redirectToRoute('grc_create_cat');
    }
}