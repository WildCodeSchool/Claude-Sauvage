<?php

namespace CS\GedBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use CS\GedBundle\Entity\Gedfiles;
use CS\GedBundle\Form\GedfilesType;
use DateTime;

class UploadController extends Controller
{
    public function uploadAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
		// var_dump($user);exit;
        $gedfiles = new Gedfiles();
        $form = $this->createForm(GedfilesType::class, $gedfiles);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $file = $gedfiles->getPath();
            
            $type = $file->guessExtension();

            $fileName = md5(uniqid()).'.'.$file->guessExtension();

            $pathDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads';
            $file->move($pathDir, $fileName);

            $gedfiles->setType($type);
            $gedfiles->setPath($fileName);
            $gedfiles->setIdowner($user->getId());
            $gedfiles->setIdCategory(1);
            $gedfiles->setDate( new DateTime());

            $em->persist($gedfiles);
            $em->flush();

           $this->get('session')->getFlashBag()->set('success', 'Fichier envoyé');

            return $this->render('GedBundle::index.html.twig', array(
            'form' => $form->createView(), 'user'=>$user, 'file'=>$gedfiles,
        ));
            // return $this->render('GedBundle::index.html.twig', array( 'form' => $form->createView() ));
        }

        // var_dump($form);exit;
        return $this->render('GedBundle::index.html.twig', array(
            'form' => $form->createView(), 'user'=>$user,
        ));
    }
}
