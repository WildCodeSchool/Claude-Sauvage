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
	    		$categorytab=$em->getRepository('GedBundle:Souscategory')->findOneById($oneupl->getIdsouscategory());
	    		$category=$categorytab->getName();
	    	}
	    	else
	    	{
	    		$categorytab=$em->getRepository('GedBundle:Category')->findOneById($oneupl->getIdcategory());
	    		$category=$categorytab->getName();
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

    public function allAction(Request $request)
    {
    	//récuperation & atribution de l entitiy manager.
    	$em = $em=$this->getDoctrine()->getManager();

    	//récuperation de l'utilisateur courant.
    	$user =$this->getUser();

    	//création d'une nouvelle instance de l'entité Gedfiles.
    	$gedfiles = new Gedfiles();

    	//créetion du formulaire
        $form = $this->createForm(GedfilesType::class, $gedfiles);
        $form->handleRequest($request);

        //Si le formulaire est envoyer et est valide.
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

        //récupération de tout les fichiers de l'utilisateur.
    	$myfiles = $em->getRepository('GedBundle:Gedfiles')->findBy(
    																	array( 'idowner'=> $user->getId() ),
    																	array('date' => 'desc')
    																);
    	//on parcours les fichiers.
    	foreach ($myfiles as $myfile) {
    		//récuperation du type de ficher.
	    	$typeFile = $myfile->getType();

	    	//récuperation du nom du ficher.
	    	$nameFile = $myfile->getPath();

	    	//récuperation de la date.
	    	$dateFile = $myfile->getDate();
	    	
    		//récueration des membres des groupes.
    		$groupMembers = $em->getRepository('GedBundle:Linkgroup')->findByIdgroup($myfile->getIdgroup());
    		foreach ($groupMembers as $groupMember) {
    			$groupMemberId = $groupMember->getIduser;
    			$groupMemberInfo = $em->getRepository('AppBundle:User')->findOneById($groupMemberId);
    			$groupMemberName = $groupMemberInfo->getusername();

    			$tabInfoGroup = array(
    					'groupMemberName'=>$groupMemberName,
    			);
    		}
    		
    		//récupération de la sous-catégory.
    		if (!empty($myfile->getIdsouscategory)){
    			$sousCategoryInfo = $em->getRepository('GedBundle:Souscategory')->findOneById($myfile->getIdsouscategory());
		    	$category = $sousCategoryInfo->getName();
		    }
		    //sinon si la sous-catégorie n'éxiste pas on recupere la catégorie.
	    	else {
	    		$categoryInfo = $em->getRepository('GedBundle:Category')->findOneById($myfile->getIdcategory());
	    		$category= $categoryInfo->getName();
	    	}

	    	//on compte les commentaires lier a un fichier.
		    $comments =$em->getRepository('GedBundle:Gedcom')->findById($myfile->getId());
		    $nbCom = count('$comments');

		    //on recherches les tags lier a un fichier.
		    	
	    	//on recherches les lien de tags par raport a l'id du fichier.
	    	$linkTags=$em->getRepository('GedBundle:Linktag')->findByIdfile($myfile->getId());

	    	//puis on fait une boucle pour parcourir notre abjet de liens de tag.
	    	foreach ($linkTags as $linkTag) {
	    		$infoTag=$em->getRepository('GedBundle:Gedtag')->findOneById($linkTag->getIdtag());
	    		$tagName=$infoTag->getName();

	    		//on stoque tout dans un tableau.
	    		$tabTags[]= array(
	    			'tagName'=>$tagName,
	    			);
	    	}
	    	if (empty($tabInfoGroup)){
	    		$tabInfoGroup = 1;
	    	}
	    	if (empty($tabTags)){
	    		$tabTags = 1;
		    }

		$tabMyFiles = array(
	    					'groupMemberName'=>$tabInfoGroup,
	    					'type'=>$typeFile,
	    					'category'=>$category,
			    			'name'=>$nameFile,
			    			'tagnames'=>$tabTags,
			    			'comments'=>$nbCom,
    					);

    	}

    	//récuperation de tout les fichiers des groupes ou est l'utilisateur.
    	$linkGroups = $em->getRepository('GedBundle:Linkgroup')->findByIduser($user->getId());

		//pour chaque fichiers recupéré recherche ca.														
    	foreach ($linkGroups as $group) {
    		$groupFiles = $em->getRepository('GedBundle:Gedfiles')->findByIdgroup($group->getIdgroup());

// /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ a controler /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ 
    		//récueration des membres des groupes.
    		$groupMemberId = $linkGroups->getIduser;
	    	$groupMemberInfo = $em->getRepository('AppBundle:User')->findOneById($groupMemberId);
	    	$groupMemberName = $groupMemberInfo->getusername();

			$tabInfoGroup = array(
					'groupMemberName'=>$groupMemberName,
			);

	    	foreach ($groupFiles as $file) {
	    		//récuperation du type de ficher.
	    		$typeFile = $file->getType();

	    		//récuperation du nom du ficher.
	    		$nameFile = $file->getPath();

	    		//récuperation de la date.
	    		$dateFile = $file->getDate();

	    		//on recupere la sous-catégory.
		    	if (!empty($file->getIdsouscategory)){

		    		$sousCategoryInfo = $em->getRepository('GedBundle:Souscategory')->findOneById($file->getIdsouscategory());
		    		$sousCategory = $sousCategoryInfo->getName();
		    	}

		    	//sinon si la sous-catégorie n'éxiste pas on recupere la catégorie.
		    	else {
		    		$categoryInfo = $em->getRepository('GedBundle:Category')->findOneById($file->getIdcategory());
		    		$category= $categoryInfo->getName();
		    	}
			    	    	
				//on compte les commentaires lier a un fichier.
		    	$comments =$em->getRepository('GedBundle:Gedcom')->findById($file->getId());

		    	//on compte le nombre de commentaires.
		    	$nbCom = count('$comments');

		    	//on recherches les tags lier a un fichier.
		    	//on recherches les lien de tags par raport a l'id du fichier.
		    	$linkTags=$em->getRepository('GedBundle:Linktag')->findByIdfile($group->getId());

		    	//puis on fait une boucle pour parcourir notre abjet de liens de tag.
		    	foreach ($linkTags as $linkTag) {
		    		$infoTag=$em->getRepository('GedBundle:Gedtag')->findOneById($linkTag->getIdtag());
		    		$tagName=$infoTag->getName();

		    		//on stoque tout dans un tableau.
		    		$tabTags[]= array(
		    			'tagName'=>$tagName,
		    			);
		    	}

		    	//on verifie que le tableau n'est pas vide, sinon on lui attribue la veleur 1.
		    	if (empty($tabTags)){
		    		$tabTags = 1;
		    	}

		    	//On regroupe tout dans un tableau.
		    	$tabGroupFiles[] = array(
		    		'category'=>$category,
		    		'sousCategory'=>$sousCategory,
		    		'name'=>$nameFile,
		    		'type'=>$icoFile,
		    		'tagnames'=>$tabTags,
		    		'comments'=>$nbCom,
		    	);
	    	}
	    }

	    //on verifie que le tableau n'est pas vide, sinon on lui attribue la veleur 1.
	    if (empty($tabGroupFiles)){
		    		$tabGroupFiles = 1;
		}


    	return $this->render('GedBundle::allfiles.html.twig',array( 'myfiles' => $myfiles,
																	'form' => $form->createView(),
																	'user'=> $user,
																	'tabtag' => $tabTags,
																));
    }
}