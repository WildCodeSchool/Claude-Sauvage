<?php

namespace CS\GedBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use CS\GedBundle\Entity\Gedfiles;
use CS\GedBundle\Form\GedfilesType;
use AppBundle\Entity\User;
use CS\GedBundle\Entity\Gedfiles;
use CS\GedBundle\Entity\Linkbookmark;
use CS\GedBundle\Entity\Category;
use CS\GedBundle\Entity\Souscategory;
use CS\GedBundle\Entity\Linktag;
use CS\GedBundle\Entity\Gedtag;

class ListController extends Controller
{
    public function showDashboardAction()
    {
    	$em=$this->getDoctrine()->getManager();
    	
    	$user = $this->container->get('security.context')->getToken()->getUser();
    	$iduser=$user->getId();
    	//on recupere tous les fichiers mis en fav par l'user
    	$listfavs=$em->getRepository('GedBundle:Linkbookmark')->findByIduser($iduser);
    	foreach ($listfavs as $onefav ) {
    		//on assigne à fav la ligne du fichier dans gedfiles
	    	$idfav=$onefav->getIdfile();	
	    	$fav = $em->getRepository('GedBundle:Gedfiles')->findByOneId($idfav);

	    	//trouver le nom du fichier
	    	$path=$fav->getPath();
	    	//trouver le type du fichier
	    	$type=$fav->getType();
	    	//trouver la categorie ou souscategorie du fichier
	    	if (!empty($fav->getIdsouscategory()))
	    	{
	    		$category=$em->getRepository('GedBundle:Souscategory')->findByOneId($fav->getIdsouscategory())
	    	}
	    	else
	    	{
	    		$category=$em->getRepository('GedBundle:Category')->findByOneId($fav->getIdcategory());
	    	}
	    	//on recupere tous les tags correspondants au fichier
	    	$linktag = $em->getRepository('GedBundle:linktag')->findByIdfile($idfav);
	    	foreach ($linktag as $tag) {
	    		//on recupere l'id du premier tag
	    		$idtag=$tag->getIdtag();
	    		//on recupere la ligne de la table Gedtag correspondante à l'id d'au dessus
	    		$infostag=$em->getRepository('GedBundle:Gedtag')->findByOneId($idtag);
	    		//on recupere le nom du tag et on met tout ca dans un tableau
	    		$tagname=$infostag->getName();
	    		$tagnames[]=array(
	    			'id'=>$idtag,
	    			'name'=>$tagname,
	    			);
	    		//on fout tout dans un tableau et on a des favoris tout neufs
	    	}
	    	$tabfav[]=array(
	    		"tagnames"=>$tagnames,
	    		"path"=>$path,
	    		"type"=>$type,
	    		"category"=>$category,
	    		);
    	}
    	
    }
}
