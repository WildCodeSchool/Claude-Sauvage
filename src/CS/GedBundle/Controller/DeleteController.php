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
use CS\GedBundle\Entity\Trash;
use DateTime;

class DeleteController extends Controller
{
	public function deleteFileAction (Request $request, $id)
	{
		$em=$this->getDoctrine()->getManager();
		$user=$this->getUser();
		$file=$em->getRepository('GedBundle:Gedfiles')->findOneById($id);
		
		$trash = new Trash();
		$trash->setIdowner($file->getIdowner());
		$trash->setType($file->getType());
		$trash->setPath($file->getPath());
		$trash->setIdcategory($file->getIdcategory ());
		$trash->setIdsouscategory($file->getIdsouscategory ());
		$trash->seIdgroup($file->getIdgroup());
		$trash->setOriginalname($file->getOriginalname());
		
		$comments=$em->getRepository('GedBundle:Gedcom')->findByIdfile($id);
		foreach ($comments as $comment)
		{
			$em->remove($comment);
			$em->flush;
		}	

		$tags=$em->getRepository('GedBundle:Linktag')->findByIdfile($id);
		foreach ($tags as $tag)
		{
			$em->remove($tag);
			$em->flush;
		}	
		$em->persist($trash);
		$em->flush();

		$em->remove($file);
		$em->flush();

	}
	public function recoverFileAction (Request $request, $id)
	{
		$em=$this->getDoctrine()->getManager();
		$user=$this->getUser();
		$file=$em->getRepository('GedBundle:Trash')->findOneById($id);
		
		$trash = new Trash();
		$trash->setIdowner($file->getIdowner());
		$trash->setType($file->getType());
		$trash->setPath($file->getPath());
		$trash->setIdcategory($file->getIdcategory ());
		$trash->setIdsouscategory($file->getIdsouscategory ());
		$trash->seIdgroup($file->getIdgroup());
		$trash->setOriginalname($file->getOriginalname());
		
		$comments=$em->getRepository('GedBundle:Gedcom')->findByIdfile($id);
		foreach ($comments as $comment)
		{
			$em->remove($comment);
			$em->flush;
		}	

		$tags=$em->getRepository('GedBundle:Linktag')->findByIdfile($id);
		foreach ($tags as $tag)
		{
			$em->remove($tag);
			$em->flush;
		}	
		$em->persist($trash);
		$em->flush();

		$em->remove($file);
		$em->flush();

	}
}