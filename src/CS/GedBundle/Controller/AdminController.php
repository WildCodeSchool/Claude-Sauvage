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
use DateTime;

// ce controller gère toutes les fonctions relatives aux actions 'administrateur'

class AdminController extends Controller
{
	// fonction d'affichage du dashboard admin ( /gedadmin/ )
	public function showDashboardAction ()
	{
		// recuperation de l'entity manager et de l'utilisateur courant
		$em=$this->getDoctrine()->getManager();
		$user=$this->getUser();

		//liste de tous les utilisateurs
		$otherusers= $em->getRepository('AppBundle:User')->findAll();

		return $this->render('GedBundle:admin:admindashboard.html.twig', array(
			'user'=>$user,
			'otherusers'=>$otherusers,
			));
	}
	public function removeUserAction (Request $request)
	{
		$em=$this->getDoctrine()->getManager();
		$iduser=$request->request->get('iduser');

		$user=$em->getRepository('AppBundle:User')->findOneById($iduser);
		$em->remove($user);
		$em->flush();
		$url = $this -> generateUrl('ged_admin_dashboard');
        $response = new RedirectResponse($url);
        return $response;
	}
	// fonction d'ajout de catégories et sous-catégories ( gedadmin/newcategory/ )
	public function addCategoryAction (Request $request)
	{
		// recuperation de l'entity manager et de l'utilisateur courant
		$em=$this->getDoctrine()->getManager();
		$user=$this->getUser();

		//recupération des formulaires et de la table des catégories
		$categories=$em->getRepository('GedBundle:Category')->findAll();
		$newcategory=$request->request->get('newcategory');
		$categoryselected=$request->request->get('categoryselected');
		$newsscategory=$request->request->get('newsscategory');

		foreach ($categories as $category )
		{
			//récuperation des sous catégories relatives à chaque catégorie
			$sscategories=$em->getRepository('GedBundle:Souscategory')->findByIdcategory($category->getId());
			foreach ($sscategories as $sscategory)
			{
				$sscategoriestab[]=array(
					'idcategory'=>$sscategory->getIdcategory(),
					'id'=>$sscategory->getId(),
					'name'=>$sscategory->getName(),
					);
			}
			if (empty($sscategoriestab))
			{
				$sscategoriestab=0;
			}
			
			$categoriestab[]=array(
				'id'=>$category->getId(),
				'name'=>$category->getName(),
				'sscategories'=>$sscategoriestab,
				);
			$sscategoriestab=[];
		}

		//verification de formulaire et ajout de catégorie
		if(!empty($newcategory) && empty($em->getRepository('GedBundle:Category')->findOneByName($newcategory)) )
		{
			$addcategory = new Category();
			$addcategory->setName($newcategory);

			$em->persist($addcategory);
			$em->flush();
		}
		
		//verification des formulaires et ajout de sous-catégorie
		if(!empty($newsscategory) && !empty($categoryselected) && ($em->getRepository('GedBundle:Souscategory')->findOneBy(array('name'=>$newsscategory, 'idcategory'=>$em->getRepository('GedBundle:Category')->findOneByName($categoryselected)->getId()) ) == null))
		{
			$addsscategory = new Souscategory();
			$addsscategory->setName($newsscategory);
			$addsscategory->setIdcategory($em->getRepository('GedBundle:Category')->findOneByName($categoryselected)->getId());

			$em->persist($addsscategory);
			$em->flush();
		}
		return $this->render('GedBundle:admin:newcategory.html.twig', array(
			'categories'=>$categoriestab,
			'user'=>$user,
			'checkpage'=>'newcategory'
			));
	}
	public function removeCategoryAction (Request $request)
	{
		$em=$this->getDoctrine()->getManager();
		$user=$this->getUser();
		$removecat=$request->request->get('removecat');
		$removesscat=$request->request->get('removesscat');
		var_dump($removecat);
		if(($removecat!=null && $removecat!='0' && !empty($removecat)) && $removesscat == null )
		{
			$category=$em->getRepository('GedBundle:Category')->findOneById($removecat);
			$em->remove($category);
			$em->flush();
			$files=$em->getRepository('GedBundle:Gedfiles')->findByIdcategory($removecat);
			foreach ($files as $file)
			{
				$file->setIdcategory(1);
				$file->setIdsouscategory(null);
				$em->persist($file);
				$em->flush();
			}
		}
		elseif(($removecat!=null || $removecat!='0' || !empty($removecat)) && $removesscat != null)
		{
			$sscategory=$em->getRepository('GedBundle:Souscategory')->findOneById($removesscat);
			$em->remove($sscategory);
			$em->flush();
			$files=$em->getRepository('GedBundle:Gedfiles')->findByIdsouscategory($removesscat);
			foreach ($files as $file)
			{
				$file->setIdsouscategory(null);
				$em->persist($file);
				$em->flush();
			}
		}
		
		$url = $this -> generateUrl('ged_addcategory');
        $response = new RedirectResponse($url);
        return $response;
	}
}