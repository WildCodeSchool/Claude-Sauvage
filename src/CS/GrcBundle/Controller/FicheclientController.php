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
    
    $userclient = $em->getRepository('AppBundle:User')->findOneById($id);
    $clientusername = $userclient->getUsername();
    
    $iscommercial= $this->get('security.context')->isGranted('ROLE_COM');

    if ($userid == $id OR $iscommercial) 
        {
            $em=$this->getDoctrine()->getManager();
            $mytickets=$em->getRepository('GrcBundle:Ticket')->findByIdsender($id);

            foreach ($mytickets as $ticket)
            {
                $id = $ticket->getId();
                $date = $ticket->getDate();
                $status = $ticket->getStatus();

                $ticketcategory= $em->getRepository('GrcBundle:Grccategory')->findOneById($ticket->getIdcategory());

                    if ($ticketcategory != null){
                        $category = $ticketcategory->getName();
                    } else {
                        $category = "Non défini";
                    }

                    $tickettab[]=array(
                        'id' => $id,
                        'date' => $date,
                        'category' => $category,
                        'status' => $status,
                    );
            }

            if (empty($tickettab)){
                $tickettab = 0;
            }

            $email = $userclient->getEmail();
            $datauser = $em->getRepository('AppBundle:Datauser')->findOneByIduser($userclient->getId());
                if ($datauser != null) {
                    $firstname = $datauser->getFirstname();
                    $surname = $datauser->getSurname();
                    $tel1 = $datauser->getTel1();
                    $tel2 = $datauser->getTel2();
                } else {
                    $firstname = null;
                    $surname = null;
                    $tel1 = null;
                    $tel2 = null;                 
                }                

            return $this->render('GrcBundle:Default:ficheclient.html.twig', array(
            'userid'  =>$userid,
            'username' => $username,
            'clientusername' => $clientusername,
            'mytickets' => $mytickets,
            'tickettab' => $tickettab,
            'email' => $email,
            'tel1' => $tel1,
            'tel2' => $tel2,
            'firstname' => $firstname,
            'surname' => $surname,

            ));
        
        } else {

            $url = $this -> generateUrl('grc_fiche_client', array( 'id'=> $userid ));
            $response = new RedirectResponse($url);
            return $response;

        }
    } 
}