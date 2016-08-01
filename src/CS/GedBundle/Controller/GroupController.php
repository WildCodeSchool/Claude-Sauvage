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

//controller des fonctions relatives aux groupes (création/ edition/ suppression/ ajout de commentaires)
class GroupController extends Controller
{
	//fonction de la page de creation de groupe (affichage du formulaire /creation)
	public function createGroupAction (Request $request)
	{
		//recuperation du nom donné au groupe
		$name = $request->request->get('name');
		$checkpage = "newgroup";
		//récuperation de l'entity manager et de l'utilisateur courant
		$em=$this->getDoctrine()->getManager();
		$user=$this->getUser();
		$iduser=$user->getId();
		
		//si le nom du groupe est bien entré on crée le groupe et on y ajoute l'utilisateur courant
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
			//renvoi sur la page d'edition du groupe nouvellement créé
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
            
            if($type==null){
                $type = 'txt';
            }

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
	//fonction d'affichage de la page d'edition de groupe (renommage et suppression)
	public function editGroupAction (Request $request, $id)
	{
		//récuperation de l'entity manager et de l'utilisateur courant
		$em = $this->getDoctrine()->getManager();
		$user = $this->getUser();
		//recuperation des infos du groupe
		$group = $em->getRepository('GedBundle:Groupe')->findOneById($id);
		$idgroup=$id;
		//recuperation des champs des formulaires
		$groupname=$request->request->get('groupname');
		$userremove=$request->request->get('userremove');
		$groupremove=$request->request->get('groupremove');
		//recuperation des utilisateurs du groupe
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

            if($type==null){
                $type = 'txt';
            }
            
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


        //si le nom du groupe est rempli on le change
		if(!empty($groupname))
		{
			$group->setName($groupname);
			$em->persist($group);
			$em->flush();
		}

		//fonction de suppression du groupe
		if(!empty($groupremove))
		{
			//on supprime les infos du groupe
			$groupe=$em->getRepository('GedBundle:Groupe')->findOneById($idgroup);
			$em->remove($groupe);
			$em->flush();
			//on supprime les liens utilisateurs / groupe
			$linkgroup=$em->getRepository('GedBundle:Linkgroup')->findByIdgroup($idgroup);
			foreach ($linkgroup as $onemember) {
				$em->remove($onemember);
				$em->flush();
			}
			//on renvoi sur le dashboard
			$url = $this -> generateUrl('ged_homepage');
        	$response = new RedirectResponse($url);
        	return $response;
		}

		return $this->render('GedBundle::editgroup.html.twig',array(
			'form'=>$form->createView(),
			'user'=>$user,
			'id'=>$idgroup,
			'groupmembers'=>$groupmembers,
			'group'=>$group,
		));
	}
	//fonction d'ajout d'utilisateurs (ajax)
	public function addUserAction(Request $request)
	{
		//récuperation de l'entity manager
		$em=$this->getDoctrine()->getManager();
		//recuperation du nom d'user et de l'idgroup
		$useradd=$request->request->get('useradd');
		$idgroup=$request->request->get('idgroup');

		//si le nom d'utilisateur à bien été rentré on crée un lien user/group
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
		//on renvoie une reponse pour l'ajax
        $response = new JsonResponse();
        return $response->setData(array('newuser' => $usertab));

	}

	//fonction de suppression d'utilisateurs d'un groupe
	public function removeUserAction(Request $request)
	{
		//récuperation de l'entity manager
		$em=$this->getDoctrine()->getManager();
		//recuperation du nom d'user et de l'idgroup
		$username=$request->request->get('username');
		$idgroup=$request->request->get('idgroup');

		//on supprime le lien user/groupe
		$user=$em->getRepository('AppBundle:User')->findOneByUsername($username);
		$linkgroup=$em->getRepository('GedBundle:Linkgroup')->findOneBy(array(
			'iduser'=>$user->getId(),
			'idgroup'=>$idgroup,
			));
		$em->remove($linkgroup);
		$em->flush();
		
		//on renvoie sur la page d'edition
		$url = $this -> generateUrl('ged_editgroup', array( 'id'=>$idgroup ));
        $response = new RedirectResponse($url);
        return $response;
	}
}