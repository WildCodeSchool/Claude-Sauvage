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

        $searchRecherche=$request->request->get('recherche');

        // $searchCategorie=$request->request->get('categorie');
        // $searchSscategories=$request->request->get('sscategories');
        // $searchType=$request->request->get('type');


        //recherche le nom du fichier
        $fileNames = $em->getRepository('GedBundle:Gedfiles')->nameSearch($searchRecherche,$user);

        //récuperation de tout les fichiers des groupes ou est l'utilisateur.
        // $linkGroups = $em->getRepository('GedBundle:Linkgroup')->findByIduser($user);

        // var_dump($linkGroups);

        

        //Pour chaque resultat de recherche par nom de fichier.
        foreach ($fileNames as $fileName){
            //prend le nom du fichier
            $name = $fileName->getOriginalName();
            //Stoque le dans un tableau
            $nameTab[]=array(
                "name"=>$name,
            );
        }

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
        $user =$this->getUser();

        $nameTab=[];
        $tagTab=[];

        $searchRecherche=$request->request->get('recherche');

        // $searchCategorie=$request->request->get('categorie');
        // $searchSscategories=$request->request->get('sscategories');
        // $searchType=$request->request->get('type');


        //recherche le nom du fichier
        $fileNames = $em->getRepository('GedBundle:Gedfiles')->nameSearch($searchRecherche);

        //recherche le nom du tag
        $fileTags = $em->getRepository('GedBundle:Gedtag')->tagSearch($searchRecherche);

        //Pour chaque resultat de recherche par nom de fichier.
        foreach ($fileNames as $fileName){
            //prend le nom du fichier
            $name = $fileName->getOriginalName();
            //Stoque le dans un tableau
            $nameTab[]=array(
                "name"=>$name,
            );
        }

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
}