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
use CS\GedBundle\Entity\Trash;
use DateTime;

// controller gérant la fonction de suppression des fichiers (ajout à la corbeille / recuperation et suppression definitive)
class DeleteController extends Controller
{
	public function showTrashbinAction (Request $request)
	{
		//récuperation de l'entity manager et de l'utilisateur courant
		$em=$this->getDoctrine()->getManager();
		$user=$this->getUser();
		//récuperation des fichiers de la corbeille
		$trashs=$em->getRepository('GedBundle:Trash')->findAll();
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

		return $this->render('GedBundle::trashbin.html.twig', array(
			'trashs'=>$trashs,
			'user'=>$user,
			'form' => $form->createView(),
			'categories'=>$categories,
			'categoryTab'=>$categoryTab,
			));
	}

	public function deleteFileAction (Request $request, $id)
	{
		//récuperation de l'entity manager et de l'utilisateur courant
		$em=$this->getDoctrine()->getManager();
		$user=$this->getUser();
		// recuperation du fichier a supprimer
		$file=$em->getRepository('GedBundle:Gedfiles')->findOneById($id);
		
		// creation du fichier corbeille
		$trash = new Trash();
		$trash->setIdowner($file->getIdowner());
		$trash->setType($file->getType());
		$trash->setPath($file->getPath());
		$trash->setIdcategory($file->getIdcategory());
		$trash->setIdsouscategory($file->getIdsouscategory());
		$trash->setIdgroup($file->getIdgroup());
		$trash->setOriginalname($file->getOriginalname());
		
		//suppresion des commentaires du fichier
		$comments=$em->getRepository('GedBundle:Gedcom')->findByIdfile($id);
		foreach ($comments as $comment)
		{
			$em->remove($comment);
			$em->flush();
		}	

		//suppression des tags du fichier
		$tags=$em->getRepository('GedBundle:Linktag')->findByIdfile($id);
		foreach ($tags as $tag)
		{
			$em->remove($tag);
			$em->flush();
		}

		//suppression de l'attribut "favori"
		$favs=$em->getRepository('GedBundle:Linkbookmark')->findByIdfile($id);
		foreach ($favs as $fav)
		{
			$em->remove($fav);
			$em->flush();
		}	
		$em->persist($trash);
		$em->flush();

		$em->remove($file);
		$em->flush();
		
		//renvoi sur le dashboard 
		$url = $this -> generateUrl('ged_homepage');
        $response = new RedirectResponse($url);
        return $response;
	}

	public function recoverFileAction (Request $request, $id)
	{
		//récuperation de l'entity manager et de l'utilisateur courant
		$em=$this->getDoctrine()->getManager();
		$user=$this->getUser();
		// recuperation du fichier à recuperer
		$trash=$em->getRepository('GedBundle:Trash')->findOneById($id);
		
		//on recrée l'entrée de l'entité Gedfiles correspondante au fichier en question
		$file = new Gedfiles();
		$file->setIdowner($trash->getIdowner());
		$file->setType($trash->getType());
		$file->setPath($trash->getPath());
		$file->setDate(new DateTime());
		$file->setIdcategory($trash->getIdcategory());
		$file->setIdsouscategory($trash->getIdsouscategory());
		$file->setIdgroup($trash->getIdgroup());
		$file->setOriginalname($trash->getOriginalname());
		
		$em->persist($file);
		$em->flush();

		//on supprime le fichier de la corbeille
		$em->remove($trash);
		$em->flush();

		//renvoi sur le dashboard
		$url = $this -> generateUrl('ged_homepage');
        $response = new RedirectResponse($url);
        return $response;
	}

	public function deleteOfDoomAction (Request $request, $id)
	{
		//récuperation de l'entity manager et de l'utilisateur courant
		$em=$this->getDoctrine()->getManager();
		$user=$this->getUser();

		//on prend le fichier ciblé
		$trash=$em->getRepository('GedBundle:Trash')->findOneById($id);
		//si l'utilisateur est bien le propriétaire du fichier la suppression definitive se fait
		if($user->getId() == $trash->getIdowner())
		{
			unlink('./uploads/'.$trash->getPath());
			$em->remove($trash);
			$em->flush();
		}
		//renvoi sur le dashboard
		$url = $this -> generateUrl('ged_homepage');
        $response = new RedirectResponse($url);
        return $response;
	}
}