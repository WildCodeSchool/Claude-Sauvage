<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Datauser;
use AppBundle\Form\DatauserForm;

class ProfileController extends Controller
{
    public function newAction(Request $request)
    {
    	//datauser=product et brochure=profileimage
        $em=$this->getDoctrine()->getManager();
        $datauser = new Datauser();
        $form = $this->createForm('AppBundle\Form\DatauserType', $datauser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $file stores the uploaded PDF file
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $datauser->getProfileimage();

            // Generate a unique name for the file before saving it
            $fileName = md5(uniqid()).'.'.$file->guessExtension();

            // Move the file to the directory where brochures are stored
            $pathDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads/profileimages';
            $file->move($pathDir, $fileName);
            

            // Update the 'brochure' property to store the PDF file name
            // instead of its contents
            $datauser->setProfileimage($fileName);

            // ... persist the $product variable or any other work
            $em->persist($datauser);
            $em->flush();

            return $this->redirect($this->generateUrl('new_profile'));
        }

        return $this->render('/default/formprofile.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}