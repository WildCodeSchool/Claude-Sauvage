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

class AjaxparamticketController extends Controller
{
    public function ajaxparamticketAction(Request $request)
    { 
    
    $em = $this->getDoctrine()->getManager();
    $status = $request->request->get('status');
    $priority = $request->request->get('priority');
    $category = $request->request->get('category');
    $sscategory = $request->request->get('sscategory');
    $ticketid = $request->request->get('ticketid');

    $ticket = $em->getRepository('GrcBundle:Ticket')->findOneById($ticketid);

    if ($status != 0){
        $mystatus = $em->getRepository('GrcBundle:Grcstatus')->findOneById($status);
        $statusname = $mystatus->getName();
        $ticket->setStatus($statusname);
       } else {
        $statusname = "0";
       }

    if ($priority != 0){
        $mypriority = $em->getRepository('GrcBundle:Grcpriority')->findOneById($priority);
        $priorityname = $mypriority->getName();
        $ticket->setPriority($priorityname);
       } else {
        $priorityname = "0";
       }

    if ($category != 0){
        $mycategory = $em->getRepository('GrcBundle:Grccategory')->findOneById($category);
        $categoryid = $mycategory->getId();
        $categoryname = $mycategory->getName();
        $ticket->setIdcategory($categoryid);
            if ($sscategory != 0){
                $mysscategory = $em->getRepository('GrcBundle:Grcsouscategory')->findOneById($sscategory);
                $sscategoryid = $mysscategory->getId();
                $sscategoryname = $mysscategory->getName();
                $ticket->setIdsouscategory($sscategoryid);
            } else {
                $ticket->setIdsouscategory(0);
                $sscategoryname = "Non définie";
       }
       } else {
        $categoryname = "0";
        $sscategoryname = "0";
       }

    $em->persist($ticket);
    $em->flush();

    $update[]=array(
            "status"=>$statusname,
            "priority"=>$priorityname,
            "category"=>$categoryname,
            "sscategory"=>$sscategoryname,
            );

    $response = new JsonResponse();
    return $response->setData(array('update' => $update));
    }
}