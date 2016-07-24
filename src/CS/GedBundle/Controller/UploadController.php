<?php

namespace CS\GedBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use CS\GedBundle\Entity\Gedfiles;
use CS\GedBundle\Form\GedfilesType;
use AppBundle\Entity\User;
use CS\GedBundle\Entity\Category;
use CS\GedBundle\Entity\Souscategory;
use CS\GedBundle\Entity\Linktag;
use CS\GedBundle\Entity\Gedtag;
use DateTime;

class UploadController extends Controller
{
    public function uploadListAction(Request $request)
    {
        //récuperation & atribution de l entitiy manager.
        $em=$this->getDoctrine()->getManager();

        //récuperation de l'utilisateur courant.
        $user =$this->getUser();
        $iduser=$user->getId();

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
                    $ssCategory=$categoryInfo->getId();
            
                    $categoryTab[] = array(
                        'category' => $categoryName,
                        'id' => $categoryId,
                        'ssid'=>$ssCategory,
                    );
                }
            }
        }

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

        //liste des fichiers de category=brouillon

        $change = 0;

        while ($change < 2)
        {
            if ($change == 0)
            {
                $listupls=$em->getRepository('GedBundle:Gedfiles')->findBy(  
                array('idowner' => $iduser, 'idcategory'=>1), // Critere
                array('id' => 'desc'));
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
                
                if ($change == 1 && $oneupl->getIdcategory() != 1 || $change == 0 && $oneupl->getIdcategory() == 1)
                {
                
                    //on assigne à fav la ligne du fichier dans gedfiles
                    $idupl=$oneupl->getId();    
                    //trouver le nom du fichier
                    $path=$oneupl->getPath();
                    //trouver le type du fichier
                    $type=$oneupl->getType();
                    $date=$oneupl->getDate();
                    $name=$oneupl->getOriginalName();

                    //on recupére si il est en favoris
                    $bookmarkfile = $em->getRepository('GedBundle:Linkbookmark')->findOneByIdfile($oneupl->getId());

                    if (empty($bookmarkfile)){
                    $bookmarkfile = 0;
                    }

                    else{
                        $bookmarkfile = 1;
                    }

                    //on compte les commentaires liés a un fichier.
                    $comments =$em->getRepository('GedBundle:Gedcom')->findByIdfile($oneupl->getId());

                    //on compte le nombre de commentaires.
                    if (empty($comments)){
                        $nbCom = 0;
                    }
                    else {
                        $nbCom = count($comments);
                    }

                    //on recupere les partages
                    $groupMembers = $em->getRepository('GedBundle:Linkgroup')->findByIdgroup($oneupl->getIdgroup());
                
                    $tabInfoGroup=[];
                    foreach ($groupMembers as $groupMember) {
                        $groupMemberId = $groupMember->getIduser();
                        $groupMemberInfo = $em->getRepository('AppBundle:User')->findOneById($groupMemberId);
                        $groupMemberName = $groupMemberInfo->getUsername();

                        $tabInfoGroup[] = array(
                                'groupMemberName'=>$groupMemberName,
                        );
                    }

                    //s'il n'existe pas de groupe, on assigne 1
                    if (empty($tabInfoGroup)){
                        $tabInfoGroup = 1;
                    }

                    //trouver la categorie ou souscategorie du fichier
                    // if (!empty($oneupl->getIdsouscategory()))
                    // {
                    //  $categorytab=$em->getRepository('GedBundle:Souscategory')->findOneById($oneupl->getIdsouscategory());
                    //  $category=$categorytab->getName();
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
                        "name"=>$name,
                        'bookmark'=>$bookmarkfile,
                        'comments'=>$nbCom,
                        'groupMemberName'=>$tabInfoGroup
                        );
                }
            }

            $change++;
            
        }
        if (empty($tabupl))
        {
            $tabupl=1;
        }

        return $this->render('GedBundle::uploads.html.twig',array(
                                                                    'categories' => $categories,
                                                                    'categoryTab'=> $categoryTab,
                                                                    'form' => $form->createView(),
                                                                    'user'=>$user,
                                                                    'uploads'=>$tabupl,
                                                                ));
    }
}