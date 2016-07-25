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

        $nameTab=[];
        $tagTab=[];
        $grpNameTab=[];
        $grpTagTab=[];

        $searchRecherche=$request->request->get('recherche');

        // $searchCategorie=$request->request->get('categorie');
        // $searchSscategories=$request->request->get('sscategories');
        // $searchType=$request->request->get('type');


        //recherche le nom du fichier
        $fileNames = $em->getRepository('GedBundle:Gedfiles')->nameSearch($searchRecherche,$user);

        //Pour chaque resultat de recherche par nom de fichier.
        foreach ($fileNames as $fileName){
            //prend le nom du fichier
            $name = $fileName->getOriginalName();
            //Stoque le dans un tableau
            $nameTab[]=array(
                "name"=>$name,
            );
        }

        //récuperation de tout les fichiers des groupes ou est l'utilisateur.
        $linkGroups = $em->getRepository('GedBundle:Linkgroup')->findByIduser($user);

        //recherche le nom du tag
        $fileTags = $em->getRepository('GedBundle:Gedtag')->tagSearch($searchRecherche);

        //Pour chaque resultat de recherche par nom de tag.
        foreach ($fileTags as $fileTag){
            //prend le nom du fichier
            $name = $fileTag->getName();
            //Stoque le dans un tableau
            $tagTab[]=array(
                "name"=>$name,
            );
        }

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

        $response = new JsonResponse();
        
        return $response->setData(array('nameTab' => $nameTab,'tagTab' => $tagTab));
    }

    public function searchAction(Request $request)
    {
        $em=$this->getDoctrine()->getManager();
        $user =$this->getUser()->getId();

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
               $filesTag = $em->getRepository('GedBundle:Gedtag')->tagSearch($searchRecherche);

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
                //rechercher les differents liens de tag.
                $filesTags = $em->getRepository('GedBundle:Linktag')->findByIdfile( $filesAcces->getId());
                //pour chaque liens recupere le nom.
                foreach ($filesTags as $filesTag) {
                   $filesTag = $em->getRepository('GedBundle:Gedtag')->tagSearch($searchRecherche);

                   //Pour chaque resultat de recherche par nom de tag.
                    foreach ($filesTag as $fileTag){
                        //prend le nom du fichier
                        $name = $fileTag->getName();
                        //Stoque le dans un tableau
                        $grpTagTab[]=array(
                            "name"=>$name,
                        );
                    }
                }
            }
        }
        var_dump($tagTab);exit;
        



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
        var_dump($nameTab);
        var_dump('-------------------------');
        var_dump($tagTab);
        var_dump('-------------------------');
        var_dump($grpNameTab);
        var_dump('-------------------------');
        var_dump($grpTagTab);
        
        return $response->setData(array('nameTab' => $nameTab,'tagTab' => $tagTab, 'grpNameTab' => $grpNameTab, 'grpNameTab' => $grpTagTab,));
    }   
}