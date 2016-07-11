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

class SearchController extends Controller
{
    /**
     * @Route("/search", name="ged_search")
     */
    public function searchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user =$this->getUser();

        $content = $this->getRequest()->request->get('Recherche');
        $Catégorie = $this->getRequest()->request->get('Catégorie');
        $SSCatégorie = $this->getRequest()->request->get('SSCatégorie');
        $type = $this->getRequest()->request->get('Type');

        // définition plus tard du la recherche de type de fichier.

        if($SSCatégorie != "Toutes les sous-catégories" ){

            $SSCatégorie = $em->getRepository('GedBundle:Category')->findOneByName($Catégorie);
            $SSCatégorieId = $CatégorieInfo->getId();

            //recherche mes fichier.

            $Myfiles = $em->getRepository('GedBundle:Gedfiles')->findBy( array(
                                                                                        'idcategory'=>$SSCatégorieId,
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

        elseif($Catégorie != "Toutes les catégories"){
            $CatégorieInfo = $em->getRepository('GedBundle:Category')->findOneByName($Catégorie);
            $CatégorieId = $CatégorieInfo->getId();

            //recherche mes fichier.

            $Myfiles = $em->getRepository('GedBundle:Gedfiles')->findBy( array(
                                                                                        'idcategory'=>$CatégorieId,
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

            var_dump($tabFiles);exit;
        }

        else{
        }
    }

                
   
}


        // $string = $this->getRequest()->request->get('recherche');

        // $recherche = $em->getRepository('GedBundle:Category')->findBy(
        // 														array('name' => $string,), // Critere
	  					// 										array('id' => 'desc'),        // Tri
								// 					  			5,                              // Limite
								// 					  			0                               // Offset
								// 							);
        // $encoders = array(new XmlEncoder(), new JsonEncoder());
        // $normalizers = array(new GetSetMethodNormalizer());
        // $serializer = new Serializer($normalizers, $encoders);
        // $jsonContent = $serializer->serialize($recherche, 'json');
        // $response = new Response($jsonContent);

        // var_dump($response);exit;
        // return $response;
