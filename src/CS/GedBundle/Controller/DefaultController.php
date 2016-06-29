<?php

namespace CS\GedBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use CS\GedBundle\Entity\Gedfiles;
use CS\GedBundle\Form\GedfilesType;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        // $user = $this->getUser();
		// var_dump($user);exit;
        $gedfiles = new Gedfiles();
        $form = $this->createForm(GedfilesType::class, $gedfiles);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $file stores the uploaded PDF file
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $gedfiles->getPath();

            // Generate a unique name for the file before saving it
            $fileName = md5(uniqid()).'.'.$file->guessExtension();

            // Move the file to the directory where brochures are stored
            $pathDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads';
            $file->move($pathDir, $fileName);
            // Update the 'brochure' property to store the PDF file name
            // instead of its contents

            // $gedfiles->setPath($fileName),
            // 		->setIdowner($),

            // ... persist the $product variable or any other work

            return $this->render('GedBundle::index.html.twig', array(
            'form' => $form->createView(),
        ));
        }

        // var_dump($form);exit;
        return $this->render('GedBundle::index.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
