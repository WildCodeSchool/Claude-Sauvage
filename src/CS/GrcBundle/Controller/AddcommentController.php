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

class AddcommentController extends Controller
{
    public function addcommentAction (Request $request)
    {
        $em=$this->getDoctrine()->getManager();
        $user=$this->getUser();

        if ($this->get('security.context')->isGranted('ROLE_COM')){
            $author = "Commercial";
        } elseif ($this->get('security.context')->isGranted('ROLE_CLI')){
            $author = "Client";
        } else {
            $author = "Other";
        }
        $idticket=$request->request->get('idticket');
        $content=$request->request->get('content');

        if (!empty($content))
        {
            $comment= new Comment();
            $comment->setIdticket($idticket);
            $comment->setIdsender($user->getId());
            $comment->setContent($content);
            $comment->setDate(new DateTime());
            $comment->setAuthor($author);

            var_dump($comment);

            $em->persist($comment);
            $em->flush();
        }
        $url = $this -> generateUrl('ticket', array( 'id'=>$idticket ));
        $response = new RedirectResponse($url);
        return $response;
    }
}