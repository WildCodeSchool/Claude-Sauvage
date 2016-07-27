<?php

namespace CS\GrcBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class FicheclientController extends Controller
{
public function ficheclientAction(Request $request, $id)
	{ 
	$em = $this->getDoctrine()->getManager();

    $user = $this->container->get('security.context')->getToken()->getUser();
    $userid = $user->getId();
    $username = $user->getUsername();
    
    $iscommercial= $this->get('security.context')->isGranted('ROLE_COM');

    if ($userid == $id OR $iscommercial) 
        {
            $em=$this->getDoctrine()->getManager();
            $mytickets=$em->getRepository('GrcBundle:Ticket')->findByIdsender($id);

            return $this->render('GrcBundle:Default:ficheclient.html.twig', array(
            'username'=>$username,
            'mytickets'=>$mytickets,
            ));
        
        } else {

            $url = $this -> generateUrl('grc_fiche_client', array( 'id'=> $userid ));
            $response = new RedirectResponse($url);
            return $response;

        }
    } 
}