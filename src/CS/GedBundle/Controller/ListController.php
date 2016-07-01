<?php

namespace CS\GedBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use CS\GedBundle\Entity\Gedfiles;
use CS\GedBundle\Form\GedfilesType;
use AppBundle\Entity\User;
use CS\GedBundle\Entity\Linkbookmark;
use CS\GedBundle\Entity\Category;
use CS\GedBundle\Entity\Souscategory;
use CS\GedBundle\Entity\Linktag;
use CS\GedBundle\Entity\Gedtag;

class ListController extends Controller
{
    public function showDashboardAction(Request $request)
    {
    	$em=$this->getDoctrine()->getManager();
    	
    	$user = $this->container->get('security.context')->getToken()->getUser();
    	$iduser=$user->getId();
    	//on recupere tous les fichiers mis en fav par l'user
    	$listfavs=$em->getRepository('GedBundle:Linkbookmark')->findBy(  
    		array('iduser' => $iduser), // Critere
  			array('id' => 'desc'),        // Tri
  			5,                              // Limite
  			0                               // Offset
		);
    	foreach ($listfavs as $onefav ) {
    		//on assigne à fav la ligne du fichier dans gedfiles
	    	$idfav=$onefav->getIdfile();	
	    	$fav = $em->getRepository('GedBundle:Gedfiles')->findOneById($idfav);

	    	//trouver le nom du fichier
	    	$path=$fav->getPath();
	    	//trouver le type du fichier
	    	$type=$fav->getType();
	    	//trouver la categorie ou souscategorie du fichier
	    	if (!empty($fav->getIdsouscategory()))
	    	{
	    		$category=$em->getRepository('GedBundle:Souscategory')->findOneById($fav->getIdsouscategory());
	    	}
	    	else
	    	{
	    		$category=$em->getRepository('GedBundle:Category')->findOneById($fav->getIdcategory());
	    	}
	    	//on recupere tous les tags correspondants au fichier
	    	$linktag = $em->getRepository('GedBundle:Linktag')->findByIdfile($idfav);
	    	foreach ($linktag as $tag) {
	    		//on recupere l'id du premier tag
	    		$idtag=$tag->getIdtag();
	    		//on recupere la ligne de la table Gedtag correspondante à l'id d'au dessus
	    		$infostag=$em->getRepository('GedBundle:Gedtag')->findOneById($idtag);
	    		//on recupere le nom du tag et on met tout ca dans un tableau
	    		$tagname=$infostag->getName();
	    		$tagnames[]=array(
	    			'id'=>$idtag,
	    			'name'=>$tagname,
	    			);
	    		//on fout tout dans un tableau et on a des favoris tout neufs
	    	}
	    	if(empty($tagnames))
	    	{
	    		$tagnames=1;
	    	}
	    	$tabfav[]=array(
	    		"tagnames"=>$tagnames,
	    		"path"=>$path,
	    		"type"=>$type,
	    		"category"=>$category,
	    		);
    	}
    	if(empty($tabfav))
    	{
    		$tabfav=1;
    	}

		//LISTE DES 5 DERNIERS UPLOADS
		$listupls=$em->getRepository('GedBundle:Gedfiles')->findByIdowner($iduser);
    	foreach ($listupls as $oneupl ) {
    		//on assigne à fav la ligne du fichier dans gedfiles
	    	$idupl=$oneupl->getId();	
	    	//trouver le nom du fichier
	    	$path=$oneupl->getPath();
	    	//trouver le type du fichier
	    	$type=$oneupl->getType();
	    	//trouver la categorie ou souscategorie du fichier
	    	if (!empty($oneupl->getIdsouscategory()))
	    	{
	    		$category=$em->getRepository('GedBundle:Souscategory')->findOneById($oneupl->getIdsouscategory());
	    	}
	    	else
	    	{
	    		$category=$em->getRepository('GedBundle:Category')->findOneById($oneupl->getIdcategory());
	    	}
	    	//on recupere tous les tags correspondants au fichier
	    	$linktag = $em->getRepository('GedBundle:Linktag')->findByIdfile($idupl);
	    	foreach ($linktag as $tag) {
	    		//on recupere l'id du premier tag
	    		$idtag=$tag->getIdtag();
	    		//on recupere la ligne de la table Gedtag correspondante à l'id d'au dessus
	    		$infostag=$em->getRepository('GedBundle:Gedtag')->findOneById($idtag);
	    		//on recupere le nom du tag et on met tout ca dans un tableau
	    		$tagname=$infostag->getName();
	    		$tagnames[]=array(
	    			'id'=>$idtag,
	    			'name'=>$tagname,
	    			);
	    		//on fout tout dans un tableau et on a des favoris tout neufs
	    	}
	    	if(empty($tagnames))
	    	{
	    		$tagnames=1;
	    	}
	    	$tabupl[]=array(
	    		"tagnames"=>$tagnames,
	    		"path"=>$path,
	    		"type"=>$type,
	    		"category"=>$category,
	    		);
    	}
    	if(empty($tabupl))
    	{
    		$tabupl=1;
    	}

    	// $em = $this->getDoctrine()->getManager();
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

            // return $this->render('GedBundle::index.html.twig', array(
            // 'form' => $form->createView(),
        // ));
        }
    	return $this->render('GedBundle::index.html.twig',array(
    		'tabfav'=>$tabfav,
    		'tabupl'=>$tabupl,
    		'form' => $form->createView(),
    		'user'=>$user
    		));
    }
}
