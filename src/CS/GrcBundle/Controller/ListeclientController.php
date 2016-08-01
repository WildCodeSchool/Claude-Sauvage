<?php

namespace CS\GrcBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ListeclientController extends Controller
{
    
  /**
   * @Security("has_role('ROLE_COM')")
   */
    public function listeclientAction(Request $request)
    {
    
    $em = $this->getDoctrine()->getManager();
    $user = $this->container->get('security.context')->getToken()->getUser();
    $userid = $user->getId();
    $username = $user->getUsername();
    
    $allclients=$em->getRepository('AppBundle:User')->findAll();

    foreach ($allclients as $client) {

            $roles = $client->getRoles();
            if (in_array("ROLE_CLI", $roles)) {
                
                $id = $client->getId();
                $username = $client->getUsername();
                $email = $client->getEmail();
                $datauser = $em->getRepository('AppBundle:Datauser')->findOneByIduser($id);
                    if ($datauser != null) {
                        $firstname = $datauser->getFirstname();
                        $surname = $datauser->getSurname();
                    } else {
                        $firstname = null;
                        $lastname = null;                   
                    }

                    $clienttab[]=array(
                        'id' => $id,
                        'username' => $username,
                        'email' => $email,
                        'firstname' => $firstname,
                        'surname' => $surname,
                    ); 
                } 
            }
            
    return $this->render('GrcBundle:Default:listeclient.html.twig', array(
        'username'=>$username,
        'userid'=>$userid,
        'clienttab'=>$clienttab,
        ));

    }
}