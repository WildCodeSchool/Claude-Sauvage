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

class DownloadController extends Controller
{
    public function downloadAction($name)
    {
        $request = $this->get('request');
        $path = $this->get('kernel')->getRootDir(). "/../web/uploads/";
        $content = file_get_contents($path.$name);

        //récuperation & atribution de l entitiy manager.
        $em=$this->getDoctrine()->getManager();

        //récupération de l'instance d'entité corespondante.
        $fileSource = $em->getRepository('GrcBundle:Ticket')->findOneByPath($name);

        //récupération du nom original.
        $fileOriginalName = $fileSource->getOriginalname();

        $response = new Response();

        //set headers
        $response->headers->set('Content-Type', 'mime/type');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$fileOriginalName);

        $response->setContent($content);
    return $response;
    }
}