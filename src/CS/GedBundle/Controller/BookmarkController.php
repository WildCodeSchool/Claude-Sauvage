<?php

namespace CS\GedBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use CS\GedBundle\Entity\Linkbookmark;
use AppBundle\Entity\User;

class BookmarkController extends Controller
{
    /**
     * @Route("/bookmark", name="ged_bookmark")
     */
    public function bookmarkAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser()->getId();

        $fav = $request->request->get('fav');

        $verifFav = $em->getRepository('GedBundle:Linkbookmark')->findOneBy(array(
        																		'idfile'=>$fav,
        																		'iduser'=>$user,
        																		)
        																	);


       if ($verifFav==null){
	        $newfav= new Linkbookmark();
	        $newfav->setIdfile($fav);
	        $newfav->setIduser($user);

	        $em->persist($newfav);
	        $em->flush();
        }
        else{
        	$em->remove($verifFav);
        	$em->flush();
        }
        
        $response = new JsonResponse();

        return $response->setData(array('response' => $verifFav));
    }
}