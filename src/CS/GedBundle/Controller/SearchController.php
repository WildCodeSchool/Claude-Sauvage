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
        $em = $this->getDoctrine()->getManager();
        $user =$this->getUser();

        $content = $this->getRequest()->request->get('Recherche');
        $Catégorie = $this->getRequest()->request->get('Catégorie');
        $SSCatégorie = $this->getRequest()->request->get('SSCatégorie');
        $type = $this->getRequest()->request->get('Type');

        // définition plus tard du la recherche de type de fichier.

        if($SSCatégorie != 0){

            //recherche mes fichier.

            $Myfiles = $em->getRepository('GedBundle:Gedfiles')->findBy( array(
                                                                                'idsouscategory'=>$SSCatégorie,
                                                                                'idowner'=>$user->getId(),
                                                                            ));

            foreach ($Myfiles as $Myfile)
            {
                $type=$Myfile->getType();
                $path=$Myfile->getPath();
                $idfile=$Myfile->getId();
                $date=$Myfile->getDate();
                $name=$Myfile->getOriginalName();

                $linktag = $em->getRepository('GedBundle:Linktag')->findByIdfile($idfile);
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

                //récuperation des favoris.
                $bookmarkfile = $em->getRepository('GedBundle:Linkbookmark')->findOneByIdfile($Myfile->getId());

                if (empty($bookmarkfile)){
                $bookmarkfile = 0;
                }

                else{
                    $bookmarkfile = 1;
                }

                //on compte les commentaires liés a un fichier.
                $comments =$em->getRepository('GedBundle:Gedcom')->findByIdfile($Myfile->getId());
                if (empty($comments)){
                        $nbCom = 0;
                    }
                else {
                    $nbCom = count($comments);
                }

                $tabFiles[]=array(
                    "idfile"=>$idfile,
                    "tagnames"=>$tagnames,
                    "path"=>$path,
                    "type"=>$type,
                    "category"=>$SSCatégorie,
                    "date"=>$date,
                    "name"=>$name,
                    "bookmark"=>$bookmarkfile,
                    "com"=>$nbCom,
                );
            }

             //recherche les fichier de mon / mes groupes.

            $listgroups=$em->getRepository('GedBundle:Linkgroup')->findByIduser($user->getId());

            foreach ($listgroups as $listgroup)
            {
                $grpId= $listgroup->getIdgroup();
                $grpFiles=$em->getRepository('GedBundle:Gedfiles')->findByIdgroup($grpId);

                foreach ($grpFiles as $grpFile)
                {
                    $type=$grpFile->getType();
                    $path=$grpFile->getPath();
                    $idfile=$grpFile->getId();
                    $date=$grpFile->getDate();
                    $name=$grpFile->getOriginalName();

                    $linktag = $em->getRepository('GedBundle:Linktag')->findByIdfile($idfile);
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

                    //récuperation des favoris.
                    $bookmarkfile = $em->getRepository('GedBundle:Linkbookmark')->findOneByIdfile($myfile->getId());

                    if (empty($bookmarkfile)){
                    $bookmarkfile = 0;
                    }

                    else{
                        $bookmarkfile = 1;
                    }

                    //on compte les commentaires liés a un fichier.
                    $comments =$em->getRepository('GedBundle:Gedcom')->findByIdfile($Myfile->getId());
                    if (empty($comments)){
                        $nbCom = 0;
                    }
                    else {
                        $nbCom = count($comments);
                    }

                    $tabGrpFiles[]=array(
                        "idfile"=>$idfile,
                        "tagnames"=>$tagnames,
                        "path"=>$path,
                        "type"=>$type,
                        "category"=>$SSCatégorie,
                        "date"=>$date,
                        "name"=>$name,
                        "bookmarkfile"=>$bookmarkfile,
                        "com"=>$nbCom,
                    );
                }
            }
            return $this->render('GedBundle::result_search.html.twig',array(
                                                                            'form' => $form->createView(),
                                                                            'user'=>$user,
                                                                            'tabFiles'=> $tabFiles,
                                                                            'tabGrpFiles'=>$tabGrpFiles,
                                                                        ));
        }

        elseif($Catégorie != 0){
            
            //recherche mes fichier.

            $Myfiles = $em->getRepository('GedBundle:Gedfiles')->findBy( array(
                                                                                        'idcategory'=>$Catégorie,
                                                                                        'idowner'=>$user->getId(),
                                                                            ))  ;

            foreach ($Myfiles as $Myfile)
            {
                $type=$Myfile->getType();
                $path=$Myfile->getPath();
                $idfile=$Myfile->getId();
                $date=$Myfile->getDate();
                $name=$Myfile->getOriginalName();

                $linktag = $em->getRepository('GedBundle:Linktag')->findByIdfile($idfile);
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

                $tabFiles[]=array(
                    "idfile"=>$idfile,
                    "tagnames"=>$tagnames,
                    "path"=>$path,
                    "type"=>$type,
                    "category"=>$Catégorie,
                    "date"=>$date,
                    "name"=>$name
                );
            }

             //recherche les fichier de mon / mes groupes.

            $listgroups=$em->getRepository('GedBundle:Linkgroup')->findByIduser($user->getId());

            foreach ($listgroups as $listgroup)
            {
                $grpId= $listgroup->getIdgroup();
                $grpFiles=$em->getRepository('GedBundle:Gedfiles')->findByIdgroup($grpId);

                foreach ($grpFiles as $grpFile)
                {
                    $type=$grpFile->getType();
                    $path=$grpFile->getPath();
                    $idfile=$grpFile->getId();
                    $date=$grpFile->getDate();
                    $name=$grpFile->getOriginalName();

                    $linktag = $em->getRepository('GedBundle:Linktag')->findByIdfile($idfile);
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

                    $tabGrpFiles[]=array(
                        "idfile"=>$idfile,
                        "tagnames"=>$tagnames,
                        "path"=>$path,
                        "type"=>$type,
                        "category"=>$Catégorie,
                        "date"=>$date,
                        "name"=>$name
                    );
                }
            }
        }

        else{
        }
    }
}