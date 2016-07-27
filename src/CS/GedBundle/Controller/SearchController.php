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

        $exist = 0;

        //RECHERCHE POUR L' UTILISATEUR - Fichiers

        //recherche le nom du fichier
        $fileNames = $em->getRepository('GedBundle:Gedfiles')->nameSearch($searchRecherche, $user, array('date' => 'desc'));

        //Pour chaque resultat de recherche par nom de fichier.
        foreach ($fileNames as $fileName){
            //prend le nom du fichier
            $name = $fileName->getOriginalName();
            $name = 
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
                //prend le nom du fichier
                $name = $file->getOriginalName();

                if(isset($nameTab))
                {
                    //Vérifie que chaque tag n'est pas egal a un nom de mes tag
                    foreach ($nameTab as $fileName){
                        $myfileName= $fileName['name'];
                        if ($myfileName != $name) {
                            $grpNameTab[]=array('name'=>$name);
                        }
                    }
                }
                else
                {
                    $grpNameTab[]=array('name'=>$name);
                }
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

                        if(isset($tagTab))
                        {
                            //Vérifie que chaque tag n'est pas egal a un nom de mes tag
                            foreach ($tagTab as $fileName){
                                if ($fileName == $name) {
                                    //prend le nom du fichier, Stoque le dans un tableau
                                    $exist = 1;
                                }
                            }
                            if ($exist == 1)
                            {
                                $grpTagTab[]=array('name'=>$name);
                                $exist = 0;
                            }
                        }
                        else
                        {
                           $grpTagTab[]=array('name'=>$name); 
                        }
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
        $em=$this->getDoctrine()->getManager();
        $user =$this->getUser();
        $idUser=$user->getId();

        $searchRecherche=$request->request->get('recherche');
        $searchCategorie=$request->request->get('categorie');
        $searchSscategorie=$request->request->get('sscategories');
        $searchtype=$request->request->get('type');

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
                    $ssCategory=$categoryInfo->getId();
            
                    $categoryTab[] = array(
                        'category' => $categoryName,
                        'id' => $categoryId,
                        'ssid'=>$ssCategory,
                    );
                }
            }
        }


        $exist = 0;

        //si sous-catégory est remseingné
        if (($searchSscategorie != null) && ($searchSscategorie != 0)){
            var_dump('sous catégory est remseingné');
            var_dump($searchCategorie);
            var_dump($searchSscategorie);
        }

        //si catégory est remseingné
        elseif (($searchCategorie != 0)){
            var_dump('catégory est remseingné');
            var_dump($searchCategorie);
            var_dump($searchSscategorie);
        }

        //Si rien n' est défini
        else{
            var_dump('rien');
            var_dump($searchCategorie);
            var_dump($searchSscategorie);
        }


        //RECHERCHE POUR L' UTILISATEUR - Fichiers

        //recherche le nom du fichier
        $fileNames = $em->getRepository('GedBundle:Gedfiles')->nameSearch($searchRecherche, $idUser, array('date' => 'desc'));

        //Pour chaque resultat de recherche par nom de fichier.
        foreach ($fileNames as $fileName){
            //prend le nom du fichier
            $name = $fileName->getOriginalName();
            //son id
            $id = $fileName->getId();
            //son type
            $type = $fileName->getType();
            //son chemin
            $path = $fileName->getPath();

            //récuperation des favoris.
            $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneByIdfile($id);

            if (empty($bookmark)){
            $bookmark = 0;
            }

            else{
                $bookmark = 1;
            }

            //Stoque le dans un tableau
            $nameTab[]=array(
                "name"=>$name,
                "id"=>$id,
                "type"=>$type,
                "path"=>$path,
                "bookmark"=>$bookmark,
            );
        }

        //RECHERCHE POUR L' UTILISATEUR - Tags

        //pour chaque fichier au quel l'utilisateur a acces recherche le nom du tag
        $filesAccess = $em->getRepository('GedBundle:Gedfiles')->findByIdowner($idUser);

        //faire une boucle pour cahque fichier trouvé.
        foreach ($filesAccess as $filesAcces) {

            $idfile = $filesAcces->getId();

            //rechercher les differents liens de tag.
            $filesTags = $em->getRepository('GedBundle:Linktag')->findByIdfile($idfile);
            $idfile = $filesAcces->getId();

            //pour chaque liens recupere le nom.
            foreach ($filesTags as $filesTag) {

                $idTag = $filesTag->getIdtag();

                $tags = $em->getRepository('GedBundle:Gedtag')->tagSearch($searchRecherche, $idTag);

                //Pour chaque resultat de recherche par nom de tag.
                foreach ($tags as $tag){
                    //prend le nom du fichier
                    $name = $tag->getName();
                    $idTag = $tag->getId();

                    $linksTag= $em->getRepository('GedBundle:linkTag')->findByIdtag($idTag);

                    foreach ($linksTag as $linkTag) {

                        if( $idfile ==($linkTag->getIdfile()) ){
                           $filesWithTag= $em->getRepository('GedBundle:Gedfiles')->findOneBy(
                                                                                            array('id'=>$linkTag->getIdfile(),'idowner'=>$idUser,)
                                                                                        );
                            $name = $filesWithTag->getOriginalName();
                            //son id
                            $id = $filesWithTag->getId();
                            //son type
                            $type = $filesWithTag->getType();
                            //son chemin
                            $path = $filesWithTag   ->getPath();

                            //récuperation des favoris.
                            $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneByIdfile($id);

                            if (empty($bookmark)){
                            $bookmark = 0;
                            }

                            else{
                                $bookmark = 1;
                            }

                            if(isset($nameTab))
                            {
                                //Vérifie que chaque nom n'est pas egal a un nom de ficher
                                foreach ($nameTab as $fileName){
                                    //si le nom du fichier et egale au nom d'un fichier de nametab
                                    //prend le nom du fichier, Stoque le dans un tableau
                                    $myfileName= $fileName['name'];
                                    if ($myfileName != $name) {
                                        $nameTab[]=array(
                                                            'name'=>$name,
                                                            "id"=>$id,
                                                            "type"=>$type,
                                                            "path"=>$path,
                                                            "bookmark"=>$bookmark,
                                                        );
                                    }
                                }
                            }
                            else
                            {
                                $nameTab[]=array(
                                                    'name'=>$name,
                                                    "id"=>$id,
                                                    "type"=>$type,
                                                    "path"=>$path,
                                                    "bookmark"=>$bookmark,
                                                );
                            }
                        }
                    }
                }
            }
        }
       
        //RECHERCHE POUR LES GROUPES - Fichiers

        //récuperation de tout les fichiers des groupes ou est l'utilisateur.
        $linkGroups = $em->getRepository('GedBundle:Linkgroup')->findByIduser($idUser);
        
        foreach ($linkGroups as $group) {
            
            $idgrp = $group->getIdgroup();

            $groupFiles = $em->getRepository('GedBundle:Gedfiles')->grpNameSearch($searchRecherche, $idgrp, array('date' => 'desc'));

            foreach ($groupFiles as $file) {
                //prend le nom du fichier
                $name = $file->getOriginalName();
                //son id
                $id = $file->getId();
                //son type
                $type = $file->getType();
                //son chemin
                $path = $file->getPath();

                //récuperation des favoris.
                $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneByIdfile($id);

                if (empty($bookmark)){
                $bookmark = 0;
                }

                else{
                    $bookmark = 1;
                }

                if(isset($nameTab))
                {
                    //Vérifie que chaque nom n'est pas egal a un nom de ficher
                    foreach ($nameTab as $fileName){
                        //si le nom du fichier et egale au nom d'un fichier de nametab
                        //prend le nom du fichier, Stoque le dans un tableau
                        $myfileName= $fileName['name'];
                        if ($myfileName != $name) {
                            $grpNameTab[]=array(
                                                'name'=>$name,
                                                "id"=>$id,
                                                "type"=>$type,
                                                "path"=>$path,
                                                "bookmark"=>$bookmark,
                                            );
                        }
                    }
                }
                else
                {
                    $grpNameTab[]=array(
                                        'name'=>$name,
                                        "id"=>$id,
                                        "type"=>$type,
                                        "path"=>$path,
                                        "bookmark"=>$bookmark,
                                    );
                }
            }

            //RECHERCHE POUR LES GROUPES - Tags

            $groupTags = $em->getRepository('GedBundle:Gedfiles')->findByIdgroup($idgrp);

            $inc=0;
            //faire une boucle pour cahque fichier trouvé.
            foreach ($groupTags as $filesAcces) {
                
                $accesid =$filesAcces->getId();

                //rechercher les differents liens de tag.
                $filesTags = $em->getRepository('GedBundle:Linktag')->findByIdfile($accesid);

                //pour chaque liens recupere le nom.
                foreach ($filesTags as $filesTag) {
                    

                    $idFileTag = $filesTag->getIdtag();

                    // tag par iD !!!!
                    $tags = $em->getRepository('GedBundle:Gedtag')->tagSearch($searchRecherche, $idFileTag, array('id' => 'desc'));

                    //Pour chaque resultat de recherche par nom de tag.
                    foreach ($tags as $tag){
                        //prend le nom du fichier
                        $name = $tag->getName();
                        $idTag = $tag->getId();

                        $linkTag= $em->getRepository('GedBundle:linkTag')->findOneByIdtag($idTag)->getIdfile();

                        $filesWithTag= $em->getRepository('GedBundle:Gedfiles')->findOneById($linkTag);

                        $name = $filesWithTag->getOriginalName();
                        //son id
                        $id = $filesWithTag->getId();
                        //son type
                        $type = $filesWithTag->getType();
                        //son chemin
                        $path = $filesWithTag->getPath();

                        //récuperation des favoris.
                        $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneByIdfile($id);

                        if (empty($bookmark)){
                        $bookmark = 0;
                        }

                        else{
                            $bookmark = 1;
                        }

                        if(isset($nameTab))
                        {
                            //Vérifie que chaque nom n'est pas egal a un nom de ficher
                            foreach ($nameTab as $fileName){
                                
                                //si le nom du fichier et egale au nom d'un fichier de nametab
                                //prend le nom du fichier, Stoque le dans un tableau
                                            // $myfileName= $fileName['name'];
                                if (($fileName != $name) && $inc==0) {
                                    $grpNameTab[]=array(
                                                        'name'=>$name,
                                                        "id"=>$id,
                                                        "type"=>$type,
                                                        "path"=>$path,
                                                        "bookmark"=>$bookmark,
                                                    );
                                    $inc++;
                                }
                            }
                        }
                        if($inc == 0)
                        {
                            
                            $grpNameTab[]=array(
                                                'name'=>$name,
                                                "id"=>$id,
                                                "type"=>$type,
                                                "path"=>$path,
                                                "bookmark"=>$bookmark,
                                            );
                            $inc++;
                        }
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

        return $this->render('GedBundle::search_result.html.twig',array(
                                                                        'form' => $form->createView(),
                                                                        'user'=>$user,
                                                                        'categories' => $categories,
                                                                        'categoryTab'=> $categoryTab,
                                                                        'nameTab' => $nameTab,
                                                                        'tagTab' => $tagTab,
                                                                        'grpNameTab' => $grpNameTab,
                                                                        'grpTagTab' => $grpTagTab,
                                                                        ));
    }   
}