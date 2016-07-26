<?php

namespace CS\GedBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use CS\GedBundle\Entity\Category;
use CS\GedBundle\Entity\Gedfiles;
use Symfony\Component\HttpFoundation\JsonResponse;
use CS\GedBundle\Form\GedfilesType;

class SearchController extends Controller
{
    public function ajaxsscatAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $idCategorie = $request->request->get('categorie');

        $ssCategories = $em->getRepository('GedBundle:Souscategory')->findByIdcategory($idCategorie);

        foreach ($ssCategories as $ssCategorie){
            $id = $ssCategorie->getId();
            $name = $ssCategorie->getName();

            $ssCategorieTab[]=array(
                "id"=>$id,
                "name"=>$name,
                );
        }

        $response = new JsonResponse();
        return $response->setData(array('ssCategorieTab' => $ssCategorieTab));
    }

    public function autocompletionAction(Request $request)
    {
        $em=$this->getDoctrine()->getManager();
        $user =$this->getUser()->getId();

        $searchRecherche=$request->request->get('recherche');

        //RECHERCHE POUR L' UTILISATEUR - Fichiers

        //recherche le nom du fichier
        $fileNames = $em->getRepository('GedBundle:Gedfiles')->nameSearch($searchRecherche, $user);

        //Pour chaque resultat de recherche par nom de fichier.
        foreach ($fileNames as $fileName){
            //prend le nom du fichier
            $name = $fileName->getOriginalName();
            //Stoque le dans un tableau
            $nameTab[]=array(
                "name"=>$name,
            );
        }

        //RECHERCHE POUR L' UTILISATEUR - Tags

        //pour chaque fichier au quel l'utilisateur a acces recherche le nom du tag
        $filesAccess = $em->getRepository('GedBundle:Gedfiles')->findByIdowner($user);

        //faire une boucle pour cahque fichier trouvé.
        foreach ($filesAccess as $filesAcces) {
            //rechercher les differents liens de tag.
            $filesTags = $em->getRepository('GedBundle:Linktag')->findByIdfile( $filesAcces->getId());
            //pour chaque liens recupere le nom.
            foreach ($filesTags as $filesTag) {

                $idTag = $filesTag->getIdtag();

                $filesTag = $em->getRepository('GedBundle:Gedtag')->tagSearch($searchRecherche, $idTag);

                //Pour chaque resultat de recherche par nom de tag.
                foreach ($filesTag as $fileTag){
                    //prend le nom du fichier
                    $name = $fileTag->getName();
                    //Stoque le dans un tableau
                    $tagTab[]=array(
                        "name"=>$name,
                    );
                }
            }
        }
       
        //RECHERCHE POUR LES GROUPES - Fichiers

        //récuperation de tout les fichiers des groupes ou est l'utilisateur.
        $linkGroups = $em->getRepository('GedBundle:Linkgroup')->findByIduser($user);

        foreach ($linkGroups as $group) {
            
            $idgrp = $group->getIdgroup();

            $groupFiles = $em->getRepository('GedBundle:Gedfiles')->grpNameSearch($searchRecherche, $idgrp);

            foreach ($groupFiles as $file) {

                $name = $file->getOriginalName();
                //Stoque le dans un tableau
                $grpNameTab[]=array(
                    "name"=>$name,
                );
            }

            //RECHERCHE POUR LES GROUPES - Tags

            $groupTags = $em->getRepository('GedBundle:Gedfiles')->findByIdgroup($idgrp);

            //faire une boucle pour cahque fichier trouvé.
            foreach ($groupTags as $filesAcces) {
                
                $accesid =$filesAcces->getId();

                //rechercher les differents liens de tag.
                $filesTags = $em->getRepository('GedBundle:Linktag')->findByIdfile($accesid);

                //pour chaque liens recupere le nom.
                foreach ($filesTags as $filesTag) {

                    $idFileTag = $filesTag->getIdtag();

                    // tag par iD !!!!
                    $tags = $em->getRepository('GedBundle:Gedtag')->tagSearch($searchRecherche, $idFileTag);

                    //Pour chaque resultat de recherche par nom de tag & par id.
                    foreach ($tags as $tag) {
                    
                        //prend le nom du fichier
                        $name = $tag->getName();

                        //Stoque le dans un tableau
                        $grpTagTab[]=array('name'=>$name);
                    }                    
                }
            }
        }

        if(!isset($nameTab)){
            $nameTab=[];
        }
        if(!isset($tagTab)){
            $tagTab=[];
        }
        if(!isset($grpNameTab)){
            $grpNameTab=[];
        }
        if(!isset($grpTagTab)){
            $grpTagTab=[];
        }

        $response = new JsonResponse();
        
        return $response->setData(array('nameTab' => $nameTab,'tagTab' => $tagTab, 'grpNameTab' => $grpNameTab, 'grpTagTab' => $grpTagTab,));
    }

    public function searchAction(Request $request)
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

        $nameTab=[];
        $tagTab=[];
        $grpNameTab=[];
        $grpTagTab=[];

        $searchRecherche=$request->request->get('recherche');

        // $searchCategorie=$request->request->get('categorie');
        // $searchSscategories=$request->request->get('sscategories');
        // $searchType=$request->request->get('type');

        //RECHERCHE POUR L' UTILISATEUR - Fichiers

        //recherche le nom du fichier
        $fileNames = $em->getRepository('GedBundle:Gedfiles')->nameSearch($searchRecherche, $user);

        //Pour chaque resultat de recherche par nom de fichier.
        foreach ($fileNames as $fileName){
            //prend le nom du fichier
            $name = $fileName->getOriginalName();
            //Stoque le dans un tableau
            $nameTab[]=array(
                "name"=>$name,
            );
        }

        //RECHERCHE POUR L' UTILISATEUR - Tags

        //pour chaque fichier au quel l'utilisateur a acces recherche le nom du tag
        $filesAccess = $em->getRepository('GedBundle:Gedfiles')->findByIdowner($user);

        //faire une boucle pour cahque fichier trouvé.
        foreach ($filesAccess as $filesAcces) {
            //rechercher les differents liens de tag.
            $filesTags = $em->getRepository('GedBundle:Linktag')->findByIdfile( $filesAcces->getId());
            //pour chaque liens recupere le nom.
            foreach ($filesTags as $filesTag) {

                $idTag = $filesTag->getIdtag();

                $filesTag = $em->getRepository('GedBundle:Gedtag')->tagSearch($searchRecherche, $idTag);

                //Pour chaque resultat de recherche par nom de tag.
                foreach ($filesTag as $fileTag){
                    //prend le nom du fichier
                    $name = $fileTag->getName();
                    //Stoque le dans un tableau
                    $tagTab[]=array(
                        "name"=>$name,
                    );
                }
            }
        }
       
        //RECHERCHE POUR LES GROUPES - Fichiers

        //récuperation de tout les fichiers des groupes ou est l'utilisateur.
        $linkGroups = $em->getRepository('GedBundle:Linkgroup')->findByIduser($user);

        foreach ($linkGroups as $group) {
            
            $idgrp = $group->getIdgroup();

            $groupFiles = $em->getRepository('GedBundle:Gedfiles')->grpNameSearch($searchRecherche, $idgrp);

            foreach ($groupFiles as $file) {

                $name = $file->getOriginalName();
                //Stoque le dans un tableau
                $grpNameTab[]=array(
                    "name"=>$name,
                );
            }

            //RECHERCHE POUR LES GROUPES - Tags

            $groupTags = $em->getRepository('GedBundle:Gedfiles')->findByIdgroup($idgrp);

            //faire une boucle pour cahque fichier trouvé.
            foreach ($groupTags as $filesAcces) {
                
                $accesid =$filesAcces->getId();

                //rechercher les differents liens de tag.
                $filesTags = $em->getRepository('GedBundle:Linktag')->findByIdfile($accesid);

                //pour chaque liens recupere le nom.
                foreach ($filesTags as $filesTag) {

                    $idFileTag = $filesTag->getIdtag();

                    // tag par iD !!!!
                    $tags = $em->getRepository('GedBundle:Gedtag')->tagSearch($searchRecherche, $idFileTag);

                    //Pour chaque resultat de recherche par nom de tag & par id.
                    foreach ($tags as $tag) {
                    
                        //prend le nom du fichier
                        $name = $tag->getName();

                        //Stoque le dans un tableau
                        $grpTagTab[]=array('name'=>$name);
                    }                    
                }
            }
        }
        
        if(!isset($nameTab)){
            $nameTab=[];
        }
        if(!isset($tagTab)){
            $tagTab=[];
        }
        if(!isset($grpNameTab)){
            $grpNameTab=[];
        }
        if(!isset($grpTagTab)){
            $grpTagTab=[];
        }

        //CONDITION DE TRI 

        // //si pas de sous catégorie défini.
        // if (empty($searchSscategories)||($searchSscategories==0)){
            
        //     //on recherche alors si la catégorie et défini.
        //     if ($searchCategorie!=0){

        //         echo 'catégorie a la valeur'.$searchCategorie;
        //     }
        //     //Si la catégorie n'est pas défini alors.
        //     else{
        //          echo'pas de catégorie & pas de sous catégorie';
        //     }
        // }

        // //on obtien donc il de la sous catégorie.
        // else{
        //     echo 'ss-catégorie a la valeur'.$searchSscategories;
        // }
        var_dump('recherche = '.$searchRecherche);
        var_dump('-------------------------');
        var_dump('Mes fichier : nom');
        var_dump($nameTab);
        var_dump('-------------------------');
        var_dump('Mes fichier : tag');
        var_dump($tagTab);
        var_dump('-------------------------');
        var_dump('Mes groupes : nom');
        var_dump($grpNameTab);
        var_dump('-------------------------');
        var_dump('Mes groupes : tag');
        var_dump($grpTagTab);

        return $this->render('GedBundle::search_result.html.twig',array(
                                                                    'form' => $form->createView(),
                                                                    'user'=>$user,
                                                                    'categories' => $categories,
                                                                    'categoryTab'=> $categoryTab,
                                                                ));
    }   
}