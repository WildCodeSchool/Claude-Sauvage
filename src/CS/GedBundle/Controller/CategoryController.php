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

// controller gérant les listes des différentes catégories de fichiers et des sous catégories
class CategoryController extends Controller
{
    public function categoryAction(Request $request, $id)
    {
    	//récuperation & atribution de l'entitiy manager.
    	$em=$this->getDoctrine()->getManager();

    	//récuperation de l'utilisateur courant.
    	$user =$this->getUser();

    	//création d'une nouvelle instance de l'entité Gedfiles.
    	$gedfiles = new Gedfiles();

    	//création du formulaire
        $form = $this->createForm(GedfilesType::class, $gedfiles);
        $form->handleRequest($request);

        //Si le formulaire est envoyé et est valide.
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

        //récupération des Categories.
        $categories = $em->getRepository('GedBundle:Category')->findAll();

        //récuperation des sous-catégories.
        $categoryTab = [];
        foreach ($categories as $category) {

        	$categoryInfos = $em->getRepository('GedBundle:Souscategory')->findByIdcategory($category->getId());

        	if (!empty($categoryInfos)){

        		//On place les sous-catégories dans un tableau si elles sont définies.
				foreach ($categoryInfos as $categoryInfo) {

        			$categoryName=$categoryInfo->getName();
        			$categoryId=$categoryInfo->getIdcategory();
    				$ssCategory=$categoryInfo->getId();
    		
    				$categoryTab[] = array(
    					'category' => $categoryName,
    					'id' => $categoryId,
    					'ssid'=>$ssCategory,
					);
        		}
        	}
    	}

    	//on récupere le nom de la catégorie
    	$title = $em ->getRepository('GedBundle:Category')->findOneById($id);

        //récupération de tous les fichiers de l'utilisateur.
    	$myfiles = $em->getRepository('GedBundle:Gedfiles')->findBy(
    																	array( 
    																			'idowner'=> $user->getId(),
    																			'idcategory' => $id
    																		),
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
	    	$bookmarkfile = $em->getRepository('GedBundle:Linkbookmark')->findBy(array('idfile'=>$myfile->getId(), 'iduser'=>$user->getId()));

    		if (empty($bookmarkfile)){
    		$bookmarkfile = 0;
	    	}

	    	else{
	    		$bookmarkfile = 1;
	    	}
	    	
    		//récuperation des membres des groupes.
    		$groupMembers = $em->getRepository('GedBundle:Linkgroup')->findByIdgroup($myfile->getIdgroup());
    		
    		$tabInfoGroup=[];
    		foreach ($groupMembers as $groupMember) {
    			$groupMemberId = $groupMember->getIduser();
    			$groupMemberInfo = $em->getRepository('AppBundle:User')->findOneById($groupMemberId);
    			$groupMemberName = $groupMemberInfo->getUsername();

    			$tabInfoGroup[] = array(
    					'groupMemberName'=>$groupMemberName,
    			);    			
    		}

    		//récupération de la sous-catégorie.
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

	    	//puis on fait une boucle pour parcourir nos objets de liens de tags.
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

    	//récuperation de tous les fichiers des groupes ou est l'utilisateur.
    	$linkGroups = $em->getRepository('GedBundle:Linkgroup')->findByIduser($user->getId());

		//pour chaque fichiers recupéré recherche ca.														
    	foreach ($linkGroups as $group) {
    		$groupFiles = $em->getRepository('GedBundle:Gedfiles')->findBy(
    																		array(
    																				'idgroup' => $group->getIdgroup(),
    																				'idcategory' => $id
    																			)
    																		);

	    	foreach ($groupFiles as $file) {

	    		//récuperation de l'Id du fichier.
    			$idFile = $file->getId();

    			//récupération de l'id owner
    			$idowner = $file->getIdowner();

	    		//récuperation du type de ficher.
	    		$typeFile = $file->getType();

	    		//récuperation du nom du ficher.
	    		$pathFile = $file->getPath();

	    		//récuperation de la date.
	    		$dateFile = $file->getDate();

	    		//récuperation du nom original.
	    		$nameFile=$file->getOriginalName();

	    		//récuperation des favoris.
	    		$bookmarkfile = $em->getRepository('GedBundle:Linkbookmark')->findBy(array('idfile'=>$file->getId(), 'iduser'=>$user->getId()));

	    		if (empty($bookmarkfile)){
	    			$bookmarkfile = 0;
	    		}
	    		else{
	    			$bookmarkfile = 1;
	    		}

	    		//on recupere la sous-catégorie.
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

		    	//on recherche les tags liés a un fichier.
		    	
		    	//on recherche les liens de tags par rapport a l'id du fichier.
		    	$linkTags=$em->getRepository('GedBundle:Linktag')->findByIdfile($file->getId());

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

		    	//on verifie que le tableau n'est pas vide, sinon on lui attribue la veleur 1.
		    	if (empty($tabTags)){
		    		$tabTags = 1;
		    	}
		    	if (empty($sousCategory))
		    	{
		    		$sousCategory=0;
		    	}

		    	//on recupere les partages
		    	$groupMembers = $em->getRepository('GedBundle:Linkgroup')->findByIdgroup($file->getIdgroup());
    		
	    		$tabInfoGroup=[];
	    		foreach ($groupMembers as $groupMember) {
	    			$groupMemberId = $groupMember->getIduser();
	    			$groupMemberInfo = $em->getRepository('AppBundle:User')->findOneById($groupMemberId);
	    			$groupMemberName = $groupMemberInfo->getUsername();

	    			$tabInfoGroup[] = array(
	    					'groupMemberName'=>$groupMemberName,
	    			);    			
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

    	return $this->render('GedBundle::category.html.twig',array( 
																	'form' => $form->createView(),
																	'user'=> $user,
																	'tabMyFiles' => $tabMyFiles,
																	'tabGroupFiles' => $tabGroupFiles,
																	'categories' => $categories,
																	'categoryTab'=> $categoryTab,
																	'title' => $title
																	));
    }

    public function sscategoryAction(Request $request,$ssid)
    {
    	//récuperation & attribution de l'entity manager.
    	$em=$this->getDoctrine()->getManager();

    	//récuperation de l'utilisateur courant.
    	$user =$this->getUser();

    	//création d'une nouvelle instance de l'entité Gedfiles.
    	$gedfiles = new Gedfiles();

    	//créetion du formulaire
        $form = $this->createForm(GedfilesType::class, $gedfiles);
        $form->handleRequest($request);

        //Si le formulaire est envoyé et est valide.
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

        //récupération des Categories.
        $categories = $em->getRepository('GedBundle:Category')->findAll();

        //récuperation des sous-catégories.
        $categoryTab = [];
        foreach ($categories as $category) {

        	$categoryInfos = $em->getRepository('GedBundle:Souscategory')->findByIdcategory($category->getId());

        	if (!empty($categoryInfos)){

        		//On place les sous-catégories dans un tableau si elles sont définies.
				foreach ($categoryInfos as $categoryInfo) {

        			$categoryName=$categoryInfo->getName();
        			$categoryId=$categoryInfo->getIdcategory();
        			$ssCategory=$categoryInfo->getId();
    		
    				$categoryTab[] = array(
    					'category' => $categoryName,
    					'id' => $categoryId,
    					'ssid'=>$ssCategory,
					);
        		}
        	}
    	}

    	//on recupere le nom de la catégorie
    	$title = $em ->getRepository('GedBundle:Souscategory')->findOneById($ssid);

        //récupération de tous les fichiers de l'utilisateur.
    	$myfiles = $em->getRepository('GedBundle:Gedfiles')->findBy(
    																	array( 
    																			'idowner'=> $user->getId(),
    																			'idsouscategory' => $ssid
    																		),
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
	    	$bookmarkfile = $em->getRepository('GedBundle:Linkbookmark')->findBy(array('idfile'=>$myfile->getId(), 'iduser'=>$user->getId()));

    		if (empty($bookmarkfile)){
    		$bookmarkfile = 0;
	    	}

	    	else{
	    		$bookmarkfile = 1;
	    	}
	    	
    		//récuperation des membres des groupes.
    		$groupMembers = $em->getRepository('GedBundle:Linkgroup')->findByIdgroup($myfile->getIdgroup());
    		
    		$tabInfoGroup=[];
    		foreach ($groupMembers as $groupMember) {
    			$groupMemberId = $groupMember->getIduser();
    			$groupMemberInfo = $em->getRepository('AppBundle:User')->findOneById($groupMemberId);
    			$groupMemberName = $groupMemberInfo->getUsername();

    			$tabInfoGroup[] = array(
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
    		$groupFiles = $em->getRepository('GedBundle:Gedfiles')->findBy(
    																		array(
    																				'idgroup' => $group->getIdgroup(),
    																				'idsouscategory' => $ssid
    																			)
    																		);

	    	foreach ($groupFiles as $file) {

	    		//récuperation de l'Id du fichier.
    			$idFile = $file->getId();

    			//récupération de l'id owner
    			$idowner = $file->getIdowner();

	    		//récuperation du type de ficher.
	    		$typeFile = $file->getType();

	    		//récuperation du nom du ficher.
	    		$pathFile = $file->getPath();

	    		//récuperation de la date.
	    		$dateFile = $file->getDate();

	    		//récuperation du nom original.
	    		$nameFile=$file->getOriginalName();

	    		//récuperation des favoris.
	    		$bookmarkfile = $em->getRepository('GedBundle:Linkbookmark')->findBy(array('idfile'=>$file->getId(), 'iduser'=>$user->getId()));

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

		    	//on recherche les tags liés a un fichier.
		    	
		    	//on recherche les liens de tags par rapport a l'id du fichier.
		    	$linkTags=$em->getRepository('GedBundle:Linktag')->findByIdfile($file->getId());

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

		    	//on verifie que le tableau n'est pas vide, sinon on lui attribue la veleur 1.
		    	if (empty($tabTags)){
		    		$tabTags = 1;
		    	}
		    	if (empty($sousCategory))
		    	{
		    		$sousCategory=0;
		    	}

		    	//on recupere les partages
		    	$groupMembers = $em->getRepository('GedBundle:Linkgroup')->findByIdgroup($file->getIdgroup());
    		
	    		$tabInfoGroup=[];
	    		foreach ($groupMembers as $groupMember) {
	    			$groupMemberId = $groupMember->getIduser();
	    			$groupMemberInfo = $em->getRepository('AppBundle:User')->findOneById($groupMemberId);
	    			$groupMemberName = $groupMemberInfo->getUsername();

	    			$tabInfoGroup[] = array(
	    					'groupMemberName'=>$groupMemberName,
	    			);    			
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

    	return $this->render('GedBundle::sub-category.html.twig',array( 
																	'form' => $form->createView(),
																	'user'=> $user,
																	'tabMyFiles' => $tabMyFiles,
																	'tabGroupFiles' => $tabGroupFiles,
																	'categories' => $categories,
																	'categoryTab'=> $categoryTab,
																	'title' => $title
																	));
    }
}