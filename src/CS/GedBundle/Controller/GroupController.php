<?php

namespace CS\GedBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\User;
use CS\GedBundle\Entity\Groupe;
use CS\GedBundle\Entity\Linkgroup;
use CS\GedBundle\Entity\Gedfiles;
use CS\GedBundle\Form\GedfilesType;
use CS\GedBundle\Entity\Category;
use DateTime;

class GroupController extends Controller
{
	public function createGroupAction (Request $request)
	{
		$name = $request->request->get('name');
		$checkpage = "newgroup";
		$em=$this->getDoctrine()->getManager();
		$user=$this->getUser();
		$iduser=$user->getId();
		
		if (!empty($name))
		{
			$group = new Groupe();
			$group->setName($name);
			$group->setIdcreator($iduser);
			
			$em->persist($group);
			$em->flush();
			
			$linkgroup = new Linkgroup();
			$linkgroup->setIduser($user->getId());
			$linkgroup->setIdgroup($group->getId());
			$em->persist($linkgroup);
			$em->flush(); 

			$idgroup=$group->getId();
			
			$url = $this -> generateUrl('ged_editgroup', array( 'id'=>$idgroup ));
        	$response = new RedirectResponse($url);
        	return $response;
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

		return $this->render('GedBundle::newgroup.html.twig',array(
			'form'=>$form->createView(),
			'user'=>$user,
			'checkpage'=>$checkpage,
			));
	}

	public function editGroupAction (Request $request, $id)
	{
		$em = $this->getDoctrine()->getManager();
		$user = $this->getUser();
		$group = $em->getRepository('GedBundle:Groupe')->findOneById($id);
		$idgroup=$id;
		$groupname=$request->request->get('groupname');
		$userremove=$request->request->get('userremove');
		$groupremove=$request->request->get('groupremove');

		$linkgroup=$em->getRepository('GedBundle:Linkgroup')->findByIdgroup($idgroup);
		$groupmembers = [];
		if(!empty($linkgroup))
		{
			foreach ($linkgroup as $groupmember)
			{
				$iduser=$groupmember->getIduser();
				$username=$em->getRepository('AppBundle:User')->findOneById($iduser)->getUsername();
				$groupmembers[]=array(
					'username'=>$username,
					'id'=>$iduser,
					);
			}
		}
		else
		{
			$groupmembers=1;
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



		if(!empty($groupname))
		{
			$group->setName($groupname);
			$em->persist($group);
			$em->flush();
		}

		if(!empty($groupremove))
		{
			$groupe=$em->getRepository('GedBundle:Groupe')->findOneById($idgroup);
			$em->remove($groupe);
			$em->flush();
			$linkgroup=$em->getRepository('GedBundle:Linkgroup')->findByIdgroup($idgroup);
			foreach ($linkgroup as $onemember) {
				$em->remove($onemember);
				$em->flush();
			}
			$url = $this -> generateUrl('ged_homepage');
        	$response = new RedirectResponse($url);
        	return $response;
		}

		return $this->render('GedBundle::editgroup.html.twig',array(
			'form'=>$form->createView(),
			'user'=>$user,
			'id'=>$idgroup,
			'groupmembers'=>$groupmembers,
		));
	}
	public function addUserAction(Request $request)
	{
		$em=$this->getDoctrine()->getManager();
		$useradd=$request->request->get('useradd');
		$idgroup=$request->request->get('idgroup');

		if(!empty($useradd))
		{
			$otheruser=$em->getRepository('AppBundle:User')->findOneByUsername($useradd);
			if (!empty($otheruser) && empty($em->getRepository('GedBundle:Linkgroup')->findOneBy(array('idgroup'=>$idgroup, 'iduser'=>$otheruser->getId()))))
			{
				$usertab=[];
				$linkgroup = new Linkgroup();
				$linkgroup->setIduser($otheruser->getId());
				$linkgroup->setIdgroup($idgroup);
				$em->persist($linkgroup);
				$em->flush();
				$usertab=array(
					'username'=>$useradd,
					'id'=>$otheruser->getId(),
				);
			}
			else
			{
				echo "l'utilisateur n'existe pas";
				$usertab=0;
			}
		}

        $response = new JsonResponse();
        return $response->setData(array('newuser' => $usertab));

	}
	public function removeUserAction(Request $request)
	{
		$em=$this->getDoctrine()->getManager();
		$username=$request->request->get('username');
		$idgroup=$request->request->get('idgroup');
		var_dump($username);
		var_dump($idgroup);
		$user=$em->getRepository('AppBundle:User')->findOneByUsername($username);
		$linkgroup=$em->getRepository('GedBundle:Linkgroup')->findOneBy(array(
			'iduser'=>$user->getId(),
			'idgroup'=>$idgroup,
			));
		$em->remove($linkgroup);
		$em->flush();
		
		$url = $this -> generateUrl('ged_editgroup', array( 'id'=>$idgroup ));
        $response = new RedirectResponse($url);
        return $response;
	}
}