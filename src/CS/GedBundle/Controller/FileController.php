<?php

namespace CS\GedBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use CS\GedBundle\Entity\Gedfiles;
use CS\GedBundle\Form\GedfilesType;
use AppBundle\Entity\User;
use CS\GedBundle\Entity\Linkbookmark;
use CS\GedBundle\Entity\Category;
use CS\GedBundle\Entity\Souscategory;
use CS\GedBundle\Entity\Linktag;
use CS\GedBundle\Entity\Gedtag;
use CS\GedBundle\Entity\Gedcom;
use DateTime;

class FileController extends Controller
{
	public function showAction (Request $request, $id)
	{
		$em=$this->getDoctrine()->getManager();
		$user=$this->getUser();
		$file=$em->getRepository('GedBundle:Gedfiles')->findOneById($id);
        $comments=$em->getRepository('GedBundle:Gedcom')->findByIdfile($id);
        if(!empty($comments))
        {
            foreach ($comments as $comment)
            {
                $comcontent=$comment->getContent();
                $poster=$em->getRepository('AppBundle:User')->findOneById($comment->getIduser());
                $comowner=$poster->getUsername();
                $comdate=$comment->getDate();

                $tabcom[]=array(
                    "owner"=>$comowner,
                    "date"=>$comdate,
                    "content"=>$comcontent,
                    );
            }
        }
        else
        {
            $tabcom=1;
        }

 		$fichier = '../web/uploads/'.$file->getPath(); 

        if ( (!empty($fichier)) && (is_readable($fichier)) )
        { 
    	    $textfile = file_get_contents($fichier); 
        } 
        else 
        { 
        	$extfile=1;
            echo 'Le fichier '.$fichier.' n\'existe pas ou n\'est pas disponible en ouverture '; 
        } 


		//fonction d'upload
		$gedfiles = new Gedfiles();

        $form = $this->createForm(GedfilesType::class, $gedfiles);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $originalgetting=$form->getNormData()->getPath('originalName');
            $originalname=$originalgetting->getClientOriginalName();
            
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
            $gedfiles->setOriginalname($originalname);
            
            $em->persist($gedfiles);
            $em->flush();

            $this->get('session')->getFlashBag()->set('success', 'Fichier envoyé');

            return $this->redirectToRoute('ged_homepage');
        }

		return $this->render('GedBundle::onefile.html.twig', array(
			'form' => $form->createView(),
    		'user'=>$user,
    		'idfile'=>$id,
    		'file'=>$file,
    		'textfile'=>$textfile,
            'tabcom'=>$tabcom,
			));
	}
    public function addCommentAction (Request $request)
    {
        $em=$this->getDoctrine()->getManager();
        $user=$this->getUser();
        
        $idfile=$request->request->get('idfile');
        $content=$request->request->get('content');

        $file=$em->getRepository('GedBundle:Gedfiles')->findOneById($idfile);
        if (!empty($content))
        {
            $gedcom= new Gedcom();
            $gedcom->setIdfile($idfile);
            $gedcom->setIduser($user->getId());
            $gedcom->setContent($content);
            $gedcom->setDate(new DateTime());

            $em->persist($gedcom);
            $em->flush();
        }
        $url = $this -> generateUrl('one_file', array( 'id'=>$idfile ));
        $response = new RedirectResponse($url);
        return $response;
    }
}