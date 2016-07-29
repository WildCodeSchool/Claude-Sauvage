<?php

namespace CS\GedBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use CS\GedBundle\Entity\Gedfiles;
use CS\GedBundle\Form\GedfilesType;
use AppBundle\Entity\User;
use CS\GedBundle\Entity\Linkbookmark;
use CS\GedBundle\Entity\Category;
use CS\GedBundle\Entity\Souscategory;
use CS\GedBundle\Entity\Linktag;
use CS\GedBundle\Entity\Gedtag;
use CS\GedBundle\Entity\Gedcom;
use CS\GedBundle\Entity\Groupe;
use CS\GedBundle\Entity\Linkgroup;
use DateTime;

//controller gérant la page d'un fichier (ajout de commentaires/ affichage/ ajout au groupe)
class FileController extends Controller
{
	// fonction d'affichage du fichier
    public function showAction (Request $request, $id)
	{
		//récuperation de l'entity manager et de l'utilisateur courant
        $em=$this->getDoctrine()->getManager();
		$user=$this->getUser();
        //récupération du fichier et de ses commentaires
		$file=$em->getRepository('GedBundle:Gedfiles')->findOneById($id);
        $comments=$em->getRepository('GedBundle:Gedcom')->findByIdfile($id);
        $groupmember=0;

        //récuperation du groupe de partage de ce fichier s'il existe
        if(!empty($file->getIdgroup()))
        {
            $filegroup=[];
            $filegroupobj=$em->getRepository('GedBundle:Groupe')->findOneById($file->getIdgroup());
            $filegroup=array(
                'name'=>$filegroupobj->getName(),
                'idcreator'=>$filegroupobj->getIdcreator(),
                'id'=>$filegroupobj->getId(),
                );
        }
        else
        {
            $filegroup=null;
        }
        //récuperation des groupes existants pour les options de partage
        $linkgroups=$em->getRepository('GedBundle:Linkgroup')->findByIduser($user->getId());
        foreach ($linkgroups as $linkgroup) {
            $group=$em->getRepository('GedBundle:Groupe')->findOneById($linkgroup->getIdgroup()); 

            if( $filegroup!=null && $linkgroup->getIdgroup() == $filegroup['id'])
            {
                $groupmember=1;
            }
            $tabgroup[]=array(
                'idgroup'=>$group->getId(),
                'groupname'=>$group->getName(),
                );
        }

        if(empty($tabgroup))
        {
            $tabgroup=1;
        }

        //mise en forme des commentaires
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

        //recuperation de l'adresse finale du fichier
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
        //verification des droits d'accès au fichier de l'utlisateur
        if($user->getId()==$file->getIdowner() || $user->getId()==$filegroup['idcreator'] || $groupmember==1 )
        {    
        	return $this->render('GedBundle::onefile.html.twig', array(
        		'form' => $form->createView(),
        		'user'=>$user,
        		'idfile'=>$id,
        		'file'=>$file,
        		'textfile'=>$textfile,
                'tabcom'=>$tabcom,
                'tabgroup'=>$tabgroup,
                'filegroup'=>$filegroup,
        		));
        }
        else
        {
            $url = $this -> generateUrl('ged_homepage');
            $response = new RedirectResponse($url);
            return $response;
        }
	}
    //ajout de commentaire (ajax)
    public function addCommentAction (Request $request)
    {
        //récuperation de l'entity manager et de l'utilisateur courant
        $em=$this->getDoctrine()->getManager();
        $user=$this->getUser();
        
        //récuperation du commentaire
        $idfile=$request->request->get('idfile');
        $content=$request->request->get('content');

        //recupération du fichier
        $file=$em->getRepository('GedBundle:Gedfiles')->findOneById($idfile);
        
        //ajout du commentaire en bdd
        if (!empty($content))
        {
            $comtab=[];
            $gedcom= new Gedcom();
            $gedcom->setIdfile($idfile);
            $gedcom->setIduser($user->getId());
            $gedcom->setContent($content);
            $gedcom->setDate(new DateTime());

            $em->persist($gedcom);
            $em->flush();
            
            $comtab[]=array(
                'idcom'=>$gedcom->getId(),
                'owner'=>$em->getRepository('AppBundle:User')->findOneById($gedcom->getIduser())->getUsername(),
                'date'=>$gedcom->getDate(),
                'content'=>$gedcom->getContent(),
                );
        }
        else
        {
            $comtab = 0;
        }

        //return pour l'ajax
        $response = new JsonResponse();
        return $response->setData(array('comtab' => $comtab));
    }
    //ajout à un groupe
    public function addToGroupAction (Request $request, $idfile)
    {
        //récuperation de l'entity manager
        $em=$this->getDoctrine()->getManager();
        //récuperation du groupe sélectionné
        $groupname=$request->request->get('groupname');
        $id=$em->getRepository('GedBundle:Groupe')->findOneByName($groupname)->getId();

        //mise en place de l'idgroupe pour le fichier (ajout au groupe)
        $file=$em->getRepository('GedBundle:Gedfiles')->findOneById($idfile);
        $file->setIdgroup($id);
        $em->persist($file);
        $em->flush();
        
        //renvoi sur la page du fichier
        $url = $this -> generateUrl('one_file', array( 'id'=>$idfile ));
        $response = new RedirectResponse($url);
        return $response;
    }
}