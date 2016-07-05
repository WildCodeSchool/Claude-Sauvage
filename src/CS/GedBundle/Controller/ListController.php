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
use DateTime;

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

    	//liste des fichiers de category=brouillon


		$listupls=$em->getRepository('GedBundle:Gedfiles')->findBy(  
    		array('idowner' => $iduser, 'idcategory'=>1), // Critere
  			array('id' => 'desc'),        // Tri
  			5,                              // Limite
  			0                               // Offset
		);
		$compte=0;
    	foreach ($listupls as $oneupl ) {
    		$compte=$compte+1;
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

    	//s'il y a moins de 5 fichiers brouillons on ajoute des fichiers uploadés récemment
		
		$listupls2=$em->getRepository('GedBundle:Gedfiles')->findBy(  
    		array('idowner' => $iduser), // Critere
  			array('id' => 'desc'),        // Tri
  			'all',
  			0
		);
    	foreach ($listupls2 as $oneupl) {
	    	if($compte<5 && ($oneupl->getIdcategory() ) != 1)
	    	{
	    		$compte=$compte+1;
	    		//on assigne à fav la ligne du fichier dans gedfiles
		    	$idupl=$oneupl->getId();	
		    	//trouver le nom du fichier
		    	$path=$oneupl->getPath();
		    	//trouver le type du fichier
		    	$type=$oneupl->getType();
		    	//trouver la categorie ou souscategorie du fichier
		    	if (!empty($oneupl->getIdsouscategory()))
		    	{
		    		$categorytab=$em->getRepository('GedBundle:Souscategory')->findOneById($oneupl->getIdsouscategory());
		    		$category= $categorytab->getName();
		    	}
		    	else
		    	{
		    		$categorytab=$em->getRepository('GedBundle:Category')->findOneById($oneupl->getIdcategory());
		    		$category= $categorytab->getName();
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
		}


		if(empty($tabupl))
    	{
    		$tabupl=1;
    	}
    	

    	//DEBUT DE LA PARTIE "PARTAGÉS AVEC MOI"
    	$listowner=$em->getRepository('GedBundle:Gedfiles')->findByIdowner($iduser);
    	foreach ($listowner as $file) {
    		
    		$type=$file->getType();
			$path=$file->getPath();
			$idfile=$file->getId();

			if (empty($file->getIdsouscategory() ) )
			{

				$categorytab=$em->getRepository('GedBundle:Category')->findOneById($file->getIdcategory());
				$category=$categorytab->getName();
			}
			else
			{
				$categorytab=$em->getRepository('GedBundle:Souscategory')->findOneById($file->getIdsouscategory());
				$category=$categorytab->getName();
			}
			$linktag = $em->getRepository('GedBundle:Linktag')->findByIdfile($idfile);
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
	    	$tabpart[]=array(
    		"tagnames"=>$tagnames,
    		"path"=>$path,
    		"type"=>$type,
    		"category"=>$category,
    		);
    	}

    	// recup des groupes de l'utilisateur courant
    	$listgroups=$em->getRepository('GedBundle:Linkgroup')->findByIduser($iduser);
    	foreach ($listgroups as $groupfiles) {
    		//pour chaque groupe on récupère la liste des fichiers qui possèdent l'id du groupe
    		$idgroup=$groupfiles->getIdgroup();
    		$listfiles=$em->getRepository('GedBundle:Gedfiles')->findByIdgroup($idgroup);
    		foreach ($listfiles as $file) {
    			//pour chacun de ces fichiers on récupère toutes les infos relatives à celui ci:
    			// le type, le path, l'id, la catégory ou sous category, les tags liés à celui ci.
    			$type=$file->getType();
    			$path=$file->getPath();
    			$idfile=$file->getId();

    			if (empty($file->getIdsouscategory() ) )
    			{

    				$categorytab=$em->getRepository('GedBundle:Category')->findOneById($file->getIdcategory());
    				$category=$categorytab->getName();
    			}
    			else
    			{
    				$categorytab=$em->getRepository('GedBundle:Souscategory')->findOneById($file->getIdsouscategory());
    				$category=$categorytab->getName();
    			}
    			$linktag = $em->getRepository('GedBundle:Linktag')->findByIdfile($idfile);
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
		    	//s'il n'existe pas de tag, on assigne 1 au tableau des tags
		    	if(empty($tagnames))
		    	{
		    		$tagnames=1;
		    	}
		    	$tabpart[]=array(
	    		"tagnames"=>$tagnames,
	    		"path"=>$path,
	    		"type"=>$type,
	    		"category"=>$category,
	    		);
    		}
    	}
    	if (empty($tabpart) )
    	{
    		$tabpart=1;
    	}

    	//DERNIERS COMMENTÉS
// requete sur les commentaires (par date la plus récente)
// prise d'idfile verification des droits et s'il a deja été compté et prise des infos
// notation de l'idfile dans un tableau pour le compter



    	$filescom=$em->getRepository('GedBundle:Gedcom')->findAll(
    		array('date'=>'desc'),
    		'all',
    		0
    		);
    	$compteur=0;
    	$tab[]=array('id'=>0);
    	foreach ($filescom as $filecom ) {
    		$i=0;
    		$counted=0;
    		$idfile=$filecom->getIdfile();
    		while($i<count($tab))
    		{
    			if( $idfile = $tab[$i]['id'] )
    			{
    				$counted=1;
    			}
    			$i++;
    		}
    		if ($counted=0 && $compteur<5)
    		{
    			//on compte le fichier comme compté dans la liste
    			$tab[]=array('id'=>$idfile);
    			
    			$file = $em->getRepository('GedBundle:Gedfiles')->findOneById($idfile);
    			
    			$type=$file->getType();
    			$path=$file->getPath();

    			if (empty($file->getIdsouscategory() ) )
    			{

    				$categorytab=$em->getRepository('GedBundle:Category')->findOneById($file->getIdcategory());
    				$category=$categorytab->getName();
    			}
    			else
    			{
    				$categorytab=$em->getRepository('GedBundle:Souscategory')->findOneById($file->getIdsouscategory());
    				$category=$categorytab->getName();
    			}
    			$linktag = $em->getRepository('GedBundle:Linktag')->findByIdfile($idfile);
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
		    	//s'il n'existe pas de tag, on assigne 1 au tableau des tags
		    	if(empty($tagnames))
		    	{
		    		$tagnames=1;
		    	}
		    	$tabcom[]=array(
	    		"tagnames"=>$tagnames,
	    		"path"=>$path,
	    		"type"=>$type,
	    		"category"=>$category,
	    		);
    		}
    	};
    	if (empty($tabcom))
    	{
    		$tabcom=1;
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

        $file = $em->getRepository('GedBundle:Gedfiles')->findOneby( 
                                                                        array('idowner'=> $user->getId(), 'idcategory' => 1 ),
                                                                        array('date' => 'desc'),
                                                                        1,
                                                                        0
                                                                    );
        if (!empty($file))
        {
        	$fileId = $file->getId();
        }
        else
        {
        	$fileId=1;
        }
    	return $this->render('GedBundle::index.html.twig',array(
    		'tabfav'=>$tabfav,
    		'tabupl'=>$tabupl,
    		'tabpart'=>$tabpart,
    		'tabcom'=>$tabcom,
    		'form' => $form->createView(),
    		'user'=>$user,
    		'file'=>$fileId,
    	));
    }
}
