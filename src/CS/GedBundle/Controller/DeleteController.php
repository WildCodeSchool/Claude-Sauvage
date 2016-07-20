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

class DeleteController extends Controller
{
	public function showTrashbinAction (Request $request)
	{
		$em=$this->getDoctrine()->getManager();
		$user=$this->getUser();
		$trashs=$em->getRepository('GedBundle:Trash')->findAll();
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
		$em=$this->getDoctrine()->getManager();
		$user=$this->getUser();
		$file=$em->getRepository('GedBundle:Gedfiles')->findOneById($id);
		
		$trash = new Trash();
		$trash->setIdowner($file->getIdowner());
		$trash->setType($file->getType());
		$trash->setPath($file->getPath());
		$trash->setIdcategory($file->getIdcategory());
		$trash->setIdsouscategory($file->getIdsouscategory());
		$trash->setIdgroup($file->getIdgroup());
		$trash->setOriginalname($file->getOriginalname());
		
		$comments=$em->getRepository('GedBundle:Gedcom')->findByIdfile($id);
		foreach ($comments as $comment)
		{
			$em->remove($comment);
			$em->flush();
		}	

		$tags=$em->getRepository('GedBundle:Linktag')->findByIdfile($id);
		foreach ($tags as $tag)
		{
			$em->remove($tag);
			$em->flush();
		}	
		$em->persist($trash);
		$em->flush();

		$em->remove($file);
		$em->flush();
		

		$url = $this -> generateUrl('ged_homepage');
        $response = new RedirectResponse($url);
        return $response;
	}

	public function recoverFileAction (Request $request, $id)
	{
		$em=$this->getDoctrine()->getManager();
		$user=$this->getUser();
		$trash=$em->getRepository('GedBundle:Trash')->findOneById($id);
		
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

		$em->remove($trash);
		$em->flush();

		$url = $this -> generateUrl('ged_homepage');
        $response = new RedirectResponse($url);
        return $response;
	}

	public function deleteOfDoomAction (Request $request, $id)
	{
		$em=$this->getDoctrine()->getManager();
		$user=$this->getUser();
		$trash=$em->getRepository('GedBundle:Trash')->findOneById($id);
		if($user->getId() == $trash->getIdowner())
		{
			unlink('./uploads/'.$trash->getPath());
			$em->remove($trash);
			$em->flush();
		}
		
		$url = $this -> generateUrl('ged_homepage');
        $response = new RedirectResponse($url);
        return $response;
	}
}