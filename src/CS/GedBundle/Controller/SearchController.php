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

    public function searchAction(Request $request)
    {
        $em=$this->getDoctrine()->getManager();
        $user =$this->getUser();

        $searchRecherche=$request->request->get('recherche');
        $searchCategorie=$request->request->get('categorie');
        $searchSscategories=$request->request->get('sscategories');
        $searchType=$request->request->get('type');

        $file = $em->getRepository('GedBundle:Gedfiles')->findSearch('ALED');

        var_dump($file);exit;

        //si pas de sous catégorie défini.
        if (empty($searchSscategories)||($searchSscategories==0)){
            
            //on recherche alors si la catégorie et défini.
            if ($searchCategorie!=0){
                echo $searchCategorie;
            }
            //Si la catégorie n'est pas défini alors.
            else{
                 echo'pas de catégorie & pas de sous catégorie';
            }
        }

        //on obtien donc il de la sous catégorie.
        else{
            echo $searchSscategories;
        }
        var_dump($searchRecherche);
        var_dump('--------------------------');
        var_dump($searchCategorie);
        var_dump('--------------------------');
        var_dump($searchSscategories);
        var_dump('--------------------------');
        var_dump($searchType);exit;
        
    }    
}