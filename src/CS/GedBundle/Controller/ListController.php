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

    	//récupération des Category.
        $categories = $em->getRepository('GedBundle:Category')->findAll();

        //récuperation des sous-catégories.
        $categoryTab = [];
        foreach ($categories as $category) {

        	$categoryInfos = $em->getRepository('GedBundle:Souscategory')->findByIdcategory($category->getId());

        	if (!empty($categoryInfos)){

        		//On place les sous-catégorie dans un tableau si elle sont définie.
				foreach ($categoryInfos as $categoryInfo) {

        			$categoryName=$categoryInfo->getName();
        			$categoryId=$categoryInfo->getIdcategory();
    		
    				$categoryTab[] = array(
    					'category' => $categoryName,
    					'id' => $categoryId,
					);
        		}
        	}
    	}

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
	    	$date=$fav->getDate();
	    	$name=$fav->getOriginalName();
	    	//trouver la categorie ou souscategorie du fichier
	    	// if (!empty($fav->getIdsouscategory()))
	    	// {
	    	// 	$category=$em->getRepository('GedBundle:Souscategory')->findOneById($fav->getIdsouscategory());
	    	// }
	    	// else
	    	// {
    		$category=$em->getRepository('GedBundle:Category')->findOneById($fav->getIdcategory());
	    	// }
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
	    		"idfile"=>$idfav,
	    		"tagnames"=>$tagnames,
	    		"path"=>$path,
	    		"type"=>$type,
	    		"category"=>$category,
	    		"date"=>$date,
	    		"name"=>$name
	    		);
    	}
    	if(empty($tabfav))
    	{
    		$tabfav=1;
    	}

		//LISTE DES 5 DERNIERS UPLOADS

    	//liste des fichiers de category=brouillon

    	$change = 0;
    	$compte = 0;

    	while ($change < 2 && $compte < 5)
    	{
    		if ($change == 0)
    		{
    			$listupls=$em->getRepository('GedBundle:Gedfiles')->findBy(  
	    		array('idowner' => $iduser, 'idcategory'=>1), // Critere
	  			array('id' => 'desc'),        // Tri
	  			5,                              // Limite
	  			0                               // Offset
				);
    		}
    		else
    		{
    			$listupls=$em->getRepository('GedBundle:Gedfiles')->findBy(  
	    		array('idowner' => $iduser), // Critere
	  			array('id' => 'desc')
				);
				
    		}

	    	foreach ($listupls as $oneupl ) 
	    	{		
	    		
	    		if ($change == 1 && $oneupl->getIdcategory() != 1 && $compte< 5 || $change == 0 && $oneupl->getIdcategory() == 1 && $compte< 5 )
	    		{
	    		
		    		//on assigne à fav la ligne du fichier dans gedfiles
			    	$idupl=$oneupl->getId();	
			    	//trouver le nom du fichier
			    	$path=$oneupl->getPath();
			    	//trouver le type du fichier
			    	$type=$oneupl->getType();
			    	$date=$oneupl->getDate();
			    	$name=$oneupl->getOriginalName();
			    	//trouver la categorie ou souscategorie du fichier
			    	// if (!empty($oneupl->getIdsouscategory()))
			    	// {
			    	// 	$categorytab=$em->getRepository('GedBundle:Souscategory')->findOneById($oneupl->getIdsouscategory());
			    	// 	$category=$categorytab->getName();
			    	// }
			    	// else
			    	// {
		    		$categorytab=$em->getRepository('GedBundle:Category')->findOneById($oneupl->getIdcategory());
		    		$category=$categorytab->getName();
			    	// }
			    	//on recupere tous les tags correspondants au fichier
			    	$linktag = $em->getRepository('GedBundle:Linktag')->findByIdfile($idupl);
			    	$tagnames = [];
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
			    		"idfile"=>$idupl,
			    		"tagnames"=>$tagnames,
			    		"path"=>$path,
			    		"type"=>$type,
			    		"category"=>$category,
			    		"date"=>$date,
			    		"name"=>$name
			    		);


		    		$compte++;
		    	}
	    	}

	    	$change++;
	    	
    	}
    	if (empty($tabupl))
    	{
    		$tabupl=1;
    	}

		
    	//DEBUT DE LA PARTIE "PARTAGÉS AVEC MOI"
    	$listowner=$em->getRepository('GedBundle:Gedfiles')->findByIdowner($iduser);
    	foreach ($listowner as $file) {
    		
    		$type=$file->getType();
			$path=$file->getPath();
			$idfile=$file->getId();
			$date=$file->getDate();
			$name=$file->getOriginalName();

			// if (empty($file->getIdsouscategory() ) )
			// {

			$categorytab=$em->getRepository('GedBundle:Category')->findOneById($file->getIdcategory());
			$category=$categorytab->getName();
			// }
			// else
			// {
			// 	$categorytab=$em->getRepository('GedBundle:Souscategory')->findOneById($file->getIdsouscategory());
			// 	$category=$categorytab->getName();
			// }
			$linktag = $em->getRepository('GedBundle:Linktag')->findByIdfile($idfile);
			$tagnames=[];
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
	    	if (!empty($tabpart))
	    	{
		    	if (count($tabpart)<5)
		    	{
			    	$tabpart[]=array(
			    		"idfile"=>$idfile,
		    			"tagnames"=>$tagnames,
		    			"path"=>$path,
		    			"type"=>$type,
		    			"category"=>$category,
		    			"date"=>$date,
		    			"name"=>$name
		    			);
	    		}
	    	}
	    	else
	    	{
	    		$tabpart[]=array(
	    			"idfile"=>$idfile,
		    		"tagnames"=>$tagnames,
		    		"path"=>$path,
		    		"type"=>$type,
		    		"category"=>$category,
		    		"date"=>$date,
		    		"name"=>$name
		    		);
	    	}
    	}
    	// recup des groupes de l'utilisateur courant
    	$listgroups=$em->getRepository('GedBundle:Linkgroup')->findByIduser($iduser);
    	foreach ($listgroups as $groupfiles) {
    		//pour chaque groupe on récupère la liste des fichiers qui possèdent l'id du groupe
    		$idgroup=$groupfiles->getIdgroup();
    		$listfiles=$em->getRepository('GedBundle:Gedfiles')->findByIdgroup($idgroup);
    		foreach ($listfiles as $file) {
    			if($file->getIdowner() != $user->getId())
    			{
	    			//pour chacun de ces fichiers on récupère toutes les infos relatives à celui ci:
	    			// le type, le path, l'id, la catégory ou sous category, les tags liés à celui ci.
	    			$type=$file->getType();
	    			$path=$file->getPath();
	    			$idfile=$file->getId();
	    			$date=$file->getDate();
	    			$name=$file->getOriginalName();

	    			// if (empty($file->getIdsouscategory() ) )
	    			// {

					$categorytab=$em->getRepository('GedBundle:Category')->findOneById($file->getIdcategory());
					$category=$categorytab->getName();
	    			// }
	    			// else
	    			// {
	    			// 	$categorytab=$em->getRepository('GedBundle:Souscategory')->findOneById($file->getIdsouscategory());
	    			// 	$category=$categorytab->getName();
	    			// }
	    			$linktag = $em->getRepository('GedBundle:Linktag')->findByIdfile($idfile);
	    			$tagnames=[];
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
		    		if (!empty($tabpart))
		    		{
				    	if (count($tabpart)<5)
			    		{
					    	$tabpart[]=array(
					    	"idfile"=>$idfile,
				    		"tagnames"=>$tagnames,
				    		"path"=>$path,
				    		"type"=>$type,
				    		"category"=>$category,
				    		"date"=>$date,
				    		"name"=>$name
				    		);
		    			}
		    		}
		    		else
			    	{
			    		$tabpart[]=array(
			    			"idfile"=>$idfile,
				    		"tagnames"=>$tagnames,
				    		"path"=>$path,
				    		"type"=>$type,
				    		"category"=>$category,
				    		"date"=>$date,
				    		"name"=>$name
				    		);
			    	}
	    		}
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
    			$date=$file->getDate();
    			$name=$file->getOriginalName();

    			// if (empty($file->getIdsouscategory() ) )
    			// {

				$categorytab=$em->getRepository('GedBundle:Category')->findOneById($file->getIdcategory());
				$category=$categorytab->getName();
    			// }
    			// else
    			// {
    			// 	$categorytab=$em->getRepository('GedBundle:Souscategory')->findOneById($file->getIdsouscategory());
    			// 	$category=$categorytab->getName();
    			// }
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
	    		"idfile"=>$idfile,
	    		"tagnames"=>$tagnames,
	    		"path"=>$path,
	    		"type"=>$type,
	    		"category"=>$category,
	    		"date"=>$date,
	    		"name"=>$name
	    		);
    		}
    	};
    	if (empty($tabcom))
    	{
    		$tabcom=1;
    	}

    	//verification du nombre de fichiers brouillon.
    	$brouillon=$em->getRepository('GedBundle:Gedfiles')->findBy(array(
    																'idowner'=> $user->getId(),
    																'idcategory' => 1
    															)
    														);
    	$nbBrouillon=count($brouillon);

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

            $this->get('session')->getFlashBag()->set('success', 'Fichier '.$originalname.' envoyé');

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
    		'categories' => $categories,
			'categoryTab'=> $categoryTab,
			'nbBrouillon' => $nbBrouillon,
    	));
    }

    public function allAction(Request $request)
    {
    	//récuperation & atribution de l entitiy manager.
    	$em=$this->getDoctrine()->getManager();

    	//récuperation de l'utilisateur courant.
    	$user =$this->getUser();

    	//création d'une nouvelle instance de l'entité Gedfiles.
    	$gedfiles = new Gedfiles();

    	//créetion du formulaire
        $form = $this->createForm(GedfilesType::class, $gedfiles);
        $form->handleRequest($request);

        //Si le formulaire est envoyer et est valide.
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

        //récupération des Category.
        $categories = $em->getRepository('GedBundle:Category')->findAll();

        //récuperation des sous-catégories.
        $categoryTab = [];
        foreach ($categories as $category) {

        	$categoryInfos = $em->getRepository('GedBundle:Souscategory')->findByIdcategory($category->getId());

        	if (!empty($categoryInfos)){

        		//On place les sous-catégorie dans un tableau si elle sont définie.
				foreach ($categoryInfos as $categoryInfo) {

        			$categoryName=$categoryInfo->getName();
        			$categoryId=$categoryInfo->getIdcategory();
    		
    				$categoryTab[] = array(
    					'category' => $categoryName,
    					'id' => $categoryId,
					);
        		}
        	}
    	}		

        //récupération de tout les fichiers de l'utilisateur.
    	$myfiles = $em->getRepository('GedBundle:Gedfiles')->findBy(
    																	array( 'idowner'=> $user->getId() ),
    																	array('date' => 'desc')
    																);
    	//on parcours les fichiers.
    	foreach ($myfiles as $myfile) {

    		//récuperation de l'Id du fichier.
    		$idFile = $myfile->getId();

    		//récuperation du type de ficher.
	    	$typeFile = $myfile->getType();

	    	//récuperation du nom du ficher.
	    	$pathFile = $myfile->getPath();

	    	//récuperation de la date.
	    	$dateFile = $myfile->getDate();

	    	//récuperation du nom original.
	    	$nameFile=$myfile->getOriginalName();

	    	//récuperation des favoris.
	    	$bookmarkfile = $em->getRepository('GedBundle:Linkbookmark')->findOneByIdfile($myfile->getId());

    		if (empty($bookmarkfile)){
    		$bookmarkfile = 0;
	    	}

	    	else{
	    		$bookmarkfile = 1;
	    	}
	    	
    		//récuperation des membres des groupes.
    		$groupMembers = $em->getRepository('GedBundle:Linkgroup')->findByIdgroup($myfile->getIdgroup());
    		foreach ($groupMembers as $groupMember) {
    			$groupMemberId = $groupMember->getIduser();
    			$groupMemberInfo = $em->getRepository('AppBundle:User')->findOneById($groupMemberId);
    			$groupMemberName = $groupMemberInfo->getUsername();

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

	    	//on compte les commentaires liés a un fichier.
		    $comments =$em->getRepository('GedBundle:Gedcom')->findByIdfile($myfile->getId());
		    if (empty($comments)){
		    		$nbCom = 0;
		    	}
	    	else {
	    		$nbCom = count($comments);
	    	}

		    //on recherche les tags liés a un fichier.
		    	
	    	//on recherche les liens de tags par rapport a l'id du fichier.
	    	$linkTags=$em->getRepository('GedBundle:Linktag')->findByIdfile($myfile->getId());

	    	//puis on fait une boucle pour parcourir notre objet de liens de tag.
	    	$tabTags = [];
	    	foreach ($linkTags as $linkTag) {
	    		$infoTag=$em->getRepository('GedBundle:Gedtag')->findOneById($linkTag->getIdtag());
	    		$tagName=$infoTag->getName();

	    		//on stocke tout dans un tableau.
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

			$tabMyFiles[]= array(
								'fileId'=>$idFile,
		    					'groupMemberName'=>$tabInfoGroup,
		    					'type'=>$typeFile,
		    					'category'=>$category,
		    					'date'=>$dateFile,
				    			'name'=>$nameFile,
				    			'path'=>$pathFile,
				    			'tagnames'=>$tabTags,
				    			'comments'=>$nbCom,
				    			'bookmark'=>$bookmarkfile,
	    					);
    	}

    	//récuperation de tout les fichiers des groupes ou est l'utilisateur.
    	$linkGroups = $em->getRepository('GedBundle:Linkgroup')->findByIduser($user->getId());

		//pour chaque fichiers recupéré recherche ca.														
    	foreach ($linkGroups as $group) {
    		$groupFiles = $em->getRepository('GedBundle:Gedfiles')->findByIdgroup($group->getIdgroup());

    		//récuperation des membres des groupes.
    		$groupMemberId = $group->getIduser();
	    	$groupMemberInfo = $em->getRepository('AppBundle:User')->findOneById($groupMemberId);
	    	$groupMemberName = $groupMemberInfo->getusername();

			$tabInfoGroup = array(
					'groupMemberName'=>$groupMemberName,
			);

	    	foreach ($groupFiles as $file) {

	    		//récuperation de l'Id du fichier.
    			$idFile = $myfile->getId();

    			//récupération de l'id owner
    			$idowner = $file->getIdowner();

	    		//récuperation du type de ficher.
	    		$typeFile = $file->getType();

	    		//récuperation du nom du ficher.
	    		$pathFile = $file->getPath();

	    		//récuperation de la date.
	    		$dateFile = $file->getDate();

	    		//récuperation du nom original.
	    		$nameFile=$myfile->getOriginalName();

	    		//récuperation des favoris.
	    		$bookmarkfile = $em->getRepository('GedBundle:Linkbookmark')->findOneByIdfile($myfile->getId());

	    		if (empty($bookmarkfile)){
	    			$bookmarkfile = 0;
	    		}
	    		else{
	    			$bookmarkfile = 1;
	    		}

	    		//on recupere la sous-catégory.
		    	if (!empty($file->getIdsouscategory)){

		    		$sousCategoryInfo = $em->getRepository('GedBundle:Souscategory')->findOneById($file->getIdsouscategory());
		    		$category = $sousCategoryInfo->getName();
		    	}

		    	//sinon si la sous-catégorie n'existe pas on recupere la catégorie.
		    	else {
		    		$categoryInfo = $em->getRepository('GedBundle:Category')->findOneById($file->getIdcategory());
		    		$category= $categoryInfo->getName();
		    	}
			    	    	
				//on compte les commentaires liés a un fichier.
		    	$comments =$em->getRepository('GedBundle:Gedcom')->findByIdfile($file->getId());

		    	//on compte le nombre de commentaires.
		    	if (empty($comments)){
		    		$nbCom = 0;
		    	}
		    	else {
		    		$nbCom = count($comments);
		    	}



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
		    	if (empty($sousCategory))
		    	{
		    		$sousCategory=0;
		    	}

		    	//On regroupe tout dans un tableau.
		    	$tabGroupFiles[] = array(
		    		'fileId'=>$idFile,
		    		'category'=>$category,
		    		'groupMemberName'=>$tabInfoGroup,
		    		'name'=>$nameFile,
		    		'path'=>$pathFile,
		    		'type'=>$typeFile,
		    		'date'=>$dateFile,
		    		'tagnames'=>$tabTags,
		    		'comments'=>$nbCom,
		    		'bookmark'=>$bookmarkfile,
		    		'idowner'=>$idowner,
		    	);
	    	}
	    }

	    //on verifie que le tableau n'est pas vide, sinon on lui attribue la valeur 1.
	    if (empty($tabGroupFiles)){
		    		$tabGroupFiles = 1;
		}

		//on verifie que le tableau n'est pas vide, sinon on lui attribue la valeur 1.
	    if (empty($tabMyFiles)){
		    		$tabMyFiles = 1;
		}

    	return $this->render('GedBundle::allfiles.html.twig',array( 
																	'form' => $form->createView(),
																	'user'=> $user,
																	'tabMyFiles' => $tabMyFiles,
																	'tabGroupFiles' => $tabGroupFiles,
																	'categories' => $categories,
																	'categoryTab'=> $categoryTab,
																	));
    }
}