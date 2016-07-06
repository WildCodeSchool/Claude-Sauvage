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
    /**
     * @Route("/", name="ged_homepage")
     */
    public function uploadAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

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

            $fileId= $gedfiles->getId();

            // var_dump($fileId);exit;

            $this->get('session')->getFlashBag()->set('success', 'Fichier envoyé');

            return $this->redirectToRoute('ged_homepage');
        }

        $file = $em->getRepository('GedBundle:Gedfiles')->findOneby( 
                                                                        array('idowner'=> $user->getId(), 'idcategory' => 1 ),
                                                                        array('date' => 'desc'),
                                                                        1,
                                                                        0
                                                                    );
        $fileId = $file->getId();

        return $this->render('GedBundle::index.html.twig', array(
            'form' => $form->createView(), 
            'user'=>$user,
            'file'=>$fileId,
        ));
    }
}
