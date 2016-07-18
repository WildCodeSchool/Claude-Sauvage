<?php

namespace CS\GedBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use CS\GedBundle\Entity\Gedfiles;
use CS\GedBundle\Form\GedfilesType;
use CS\GedBundle\Entity\Gedtag;
use CS\GedBundle\Entity\Linktag;
use DateTime;

class ParametersController extends Controller
{
    /**
     * @Route("/parameters/{id_file}", name="ged_parameters_file")
     */
    public function ParametersAction(Request $request, $id)
    {
		$em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $addtag= $request->request->get('addtag');


        $file = $em->getRepository('GedBundle:Gedfiles')->findOneById($id);
        $linktags = $em->getRepository('GedBundle:Linktag')->findByIdfile($id);
        foreach ($linktags as $linktag)
        {
            $tag=$em->getRepository('GedBundle:Gedtag')->findOneById($linktag->getIdtag());
            $name=$tag->getName();
            $tabtag[]=array(
                'name'=>$name,
                );
        }
        if(empty($tabtag))
        {
            $tabtag=0;
        }
        if(count($tabtag)<3 && !empty($addtag))
        {
            $newtag = new Gedtag;
            $newtag->setName($addtag);
            $em->persist($newtag);
            $em->flush();
            
            $newlinktag = new Linktag;
            $newlinktag ->setIdfile($id);
            $newlinktag->setIdtag($newtag->getId());
            $em->persist($newlinktag);
            $em->flush();

        }
        elseif(count($tabtag) >= 3 && !empty($addtag))
        {
            $newtag = new Gedtag;
            $newtag->setName($addtag);
            $em->persist($newtag);
            $em->flush();

            $replacelinktag=$em->getRepository('GedBundle:Linktag')->findOneByIdfile($id); 
            $em->remove($replacelinktag);
            $em->flush();

            $replacelinktag = new Linktag;
            $replacelinktag->setIdfile($id);
            $replacelinktag->setIdtag($newtag->getId());
            $em->persist($replacelinktag);
            $em->flush();
        }


        //fonction d'upload
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

            return $this->redirectToRoute('ged_homepage');
            }

        return $this->render('GedBundle::parameters.html.twig', array(
            'form' => $form->createView(), 'user'=>$user,
            'id'=>$id,
        ));
    }
}
