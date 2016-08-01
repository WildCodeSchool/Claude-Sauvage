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
use DateTime;

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
        $myfiles = $em->getRepository('GedBundle:Gedfiles')->nameSearch($searchRecherche, $user);
        $nameTab =[];

        foreach ($myfiles as $myfile) {            
            //nom du fichier
            $name = $myfile->getOriginalName();
            //on met tout dans un tableau
            $nameTab[] = array(
                'name' => $name,
            );
        }

        //RECHERCHE POUR L' UTILISATEUR - Tags

        //on recherche tout les tag ayant la valeur
        $allTag = $em->getRepository('GedBundle:Gedtag')->tagSearch($searchRecherche);
        //on defini un tableau
        $tagTab = [];
        //pour chaque occurrence
        foreach ($allTag as $tag) {
            //prendre Id
            $idTag = $tag->getId();
            //et le nom
            $nameTag = $tag->getName();

            //rechercher les lien pour chaque id du tag
            $links = $em->getRepository('GedBundle:Linktag')->findByIdtag($idTag);

            //si les liens on ete trouvé
            if($links!=null){            
                //pour chaque occurrence de liens
                foreach ($links as $link) {
                    //recupére Id du fichier corespondant
                    $idFile = $link->getIdfile();

                    //puis faire une requete pour savoir si idowner = user
                    $file = $em->getRepository('GedBundle:Gedfiles')->findOneBy(
                        array(
                            'idowner'=>$user,
                            'id'=>$idFile,
                        )
                    );
                    //si la recherche aboutis le tag est donc bien au fichier du user
                    if($file!=null){
                        //si tabTag et defini
                        if($tagTab!=null){
                            $count=0;
                            //pourchaque entre dans le tableau tagTab
                            foreach ($tagTab as $tag) {
                                //on recupere id 
                                $name = $tag['name'];

                                //pour chaque entré du tableau et on le compare au nom du tag (ligne 76)
                                if($name==$nameTag){
                                    $count++;
                                }
                            }
                            //si le compteur et toujour a 0 ses que le tag n est pas dans le tableau
                            if($count==0){
                                //l'ajoute donc
                                $tagTab[] = array(
                                    'name' => $nameTag,
                                );
                            }
                        }
                        //sinon remplir le tableau
                        if($tagTab==null){
                            $tagTab[]=array(
                                'name' => $nameTag,
                            );
                        }                       
                    }
                }
            }
        }

        //RECHERCHE POUR LES GROUPES - Fichiers & Tag

        //on recherche dans quels groupe l'utilisateur est
        $linkGroups = $em->getRepository('GedBundle:Linkgroup')->findByIduser($user);
        
        //si on un resultat
        if($linkGroups!=null){
            $groupTab = [];
            //pour chaque resultat, ajout les dans un tableau
            foreach ($linkGroups as $group) {
                $idGrp = $group->getIdgroup();
                array_push($groupTab, $idGrp);
            }

            //Fichiers
            
            $grpNameTab = [];
            //pour chaque group contenu dans mon tableau
            foreach ($groupTab as $group) {
                $groupFiles = $em->getRepository('GedBundle:Gedfiles')->grpNameSearch($searchRecherche,$group);

                if($groupFiles!=null){
                    foreach ($groupFiles as $files) {
                        $nameGrpFile = $files->getOriginalName();
                        $count = 0;
                        
                        //si mes ficher ne son pas vide
                        if ($nameTab!=null) {
                            //pour chaque ficher dans mes ficher 
                            foreach ($nameTab as $fileTab) {
                                //si le nom du tableau mon ficher est egale au nom de ficher de mon groupe
                                if( ($fileTab['name'] == $nameGrpFile) ){
                                    $count ++;                                            
                                }
                            }                                    
                        }
                        // si aucun nom n'a été trouver dans $nameTab ou si $nameTab nexista pas 
                        if($count==0){

                            // verification si le grpNameTab n'est pas defini
                            if($grpNameTab==null){
                                //on le remplis tout dans un tableau
                                $grpNameTab[] = array(
                                    'name' => $nameGrpFile,
                                );
                            }
                            //si le tableau est deja défini alors le parcourir et verifier
                            if($grpNameTab!=null){                             
                                foreach ($grpNameTab as $fileTab) {
                                    //si le nom contenu dans mon tableau n est
                                    if($fileTab['name'] == $nameGrpFile){
                                        $count ++;                                            
                                    }
                                }
                                //si le nom na pas ete trouver alors le rajouté
                                if($count==0){
                                    //on met tout dans un tableau
                                    $grpNameTab[] = array(
                                        'name' => $nameGrpFile,                                       
                                    );                                            
                                }
                            }
                        }
                    }
                }
            }

            //Tags

            $grpTagTab = [];
            //pour chaque group contenu dans mon tableau
            foreach ($groupTab as $group) {
                //on recherche tout les tag ayant la valeur
                $allTag = $em->getRepository('GedBundle:Gedtag')->tagSearch($searchRecherche);
                
                //pour chaque occurrence
                foreach ($allTag as $tag) {
                    //prendre Id
                    $idTag = $tag->getId();
                    //et le nom
                    $nameTag = $tag->getName();

                    //rechercher les lien pour chaque id du tag
                    $links = $em->getRepository('GedBundle:Linktag')->findByIdtag($idTag);

                    //si les liens on ete trouvé
                    if($links!=null){            
                        //pour chaque occurrence de liens
                        foreach ($links as $link) {
                            //recupére Id du fichier corespondant
                            $idFile = $link->getIdfile();

                            //puis faire une requete pour savoir si idgroup = group
                            $file = $em->getRepository('GedBundle:Gedfiles')->findOneBy(
                                array(
                                    'idgroup'=>$group,
                                    'id'=>$idFile,
                                )
                            );
                            //si la recherche aboutis le tag est donc bien au fichier du group
                            if($file!=null){
                                //si grpTabTag et defini
                                if($grpTagTab!=null){
                                    $count=0;

                                    // pourchaque entre dans le tableau groupTab
                                    foreach ($grpTagTab as $tag) {
                                        //on recupere le nom
                                        $name = $tag['name'];

                                        //pour chaque entré du tableau et on le compare au nom du tag (ligne 76)
                                        if($name==$nameTag){
                                            $count++;
                                        }
                                    }
                                    //si tabTag et defini
                                    if($tagTab!=null){
                                        foreach ($tagTab as $tag) {
                                            //on recupere le nom
                                            $name = $tag['name'];

                                            //pour chaque entré du tableau et on le compare au nom du tag (ligne 76)
                                            if($name==$nameTag){
                                                $count++;
                                            }
                                        }
                                    }
                                    //si le compteur et toujour a 0 ses que le tag n est pas dans le tableau
                                    if($count==0){
                                        //l'ajoute donc
                                        $grpTagTab[] = array(
                                            'name' => $nameTag,
                                        );
                                    }
                                }
                                //si tabTag et defini
                                if($tagTab!=null){
                                    $count=0;
                                    foreach ($tagTab as $tag) {
                                        //on recupere le nom
                                        $name = $tag['name'];

                                        //pour chaque entré du tableau et on le compare au nom du tag (ligne 76)
                                        if($name==$nameTag){
                                            $count++;
                                        }
                                    }
                                    //si le compteur et toujour a 0 ses que le tag n est pas dans le tableau
                                    if($count==0){
                                        //l'ajoute donc
                                        $grpTagTab[] = array(
                                            'name' => $nameTag,
                                        );
                                    }
                                }
                                //sinon tabTag et grpTagTab n'est pas defini remplir le tableau
                                if(($grpTagTab==null)&&($tagTab==null)){
                                    $grpTagTab[]=array(
                                        'name' => $nameTag,
                                    );
                                }                      
                            }
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

        $search=$request->request->get('recherche');
        $searchCategorie=$request->request->get('categorie');
        $searchSscategorie=$request->request->get('sscategories');
        $searchtype=$request->request->get('type');        
        
        //Videos
        if(4==$searchtype){
            $fileType = array('mp4','avi', );
        }

        //Audio
        if(3==$searchtype){
            $fileType = array('mp3','wave', );
        }

        //Image
        if(2==$searchtype){
            $fileType = array('jpeg','png', );
        }

        //Texte
        if(1==$searchtype){
            $fileType = array('doc','txt',);
        }

        //Aucune selection
        if(0==$searchtype){
            $fileType = 0;
        }
        
        //suppression des espace
        //conversion de la chaine de caractère en tableau pour multiple recherche
        $tabSearch = explode( ',',str_replace(' ','',$search),5);

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

            if($type==null){
                $type = 'txt';
            }

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

        $nameTab =[];
        $tagTab = [];
        $grpNameTab = [];
        $grpTagTab = [];
        
        if(strlen($search)>=1){
            //si sous-catégory est remseingné
            if (($searchSscategorie != null) && ($searchSscategorie != 0)){
                //RECHERCHE POUR L' UTILISATEUR - Fichiers
                $searchCount=0;
                foreach ($tabSearch as $searchRecherche) {
                    if($searchCount<=4 && $searchRecherche!=''){
                        //recherche le nom du fichier
                        //si le type et defini
                        if($fileType!=0){
                            $myfiles = $em->getRepository('GedBundle:Gedfiles')->sscategoryTypeSearch($searchRecherche, $idUser, $searchCategorie, $searchSscategorie, $fileType);
                        }
                        //sinon
                        else{
                            $myfiles = $em->getRepository('GedBundle:Gedfiles')->sscategorySearch($searchRecherche, $idUser, $searchCategorie, $searchSscategorie);
                        }

                        foreach ($myfiles as $myfile) {
                            
                            //nom du fichier
                            $name = $myfile->getOriginalName();
                            //son id
                            $id = $myfile->getId();
                            //son type
                            $type = $myfile->getType();
                            //son chemin
                            $path = $myfile->getPath();

                            //récuperation des favoris.
                            $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$id,
                                                                                                        'iduser'=>$idUser,
                                                                                                        )
                            );
                            if ($bookmark!=null){
                                $bookmark = 1;
                            }

                            else{
                                $bookmark = 0;
                            }

                            //on met tout dans un tableau
                            array_push($nameTab, array(
                                'name' => $name,
                                'id' => $id,
                                'type' => $type,
                                'path' => $path,
                                'bookmark' => $bookmark,
                            ));
                        }
                    }
                    $searchCount++;
                }
                //RECHERCHE POUR L' UTILISATEUR - Tags
                $searchCount=0;
                foreach ($tabSearch as $searchRecherche) {
                    if($searchCount<=4 && $searchRecherche!=''){
                        //on recherche tout les tag ayant la valeur
                        $allTag = $em->getRepository('GedBundle:Gedtag')->tagSearch($searchRecherche);
                        $linkTagTab=[];
                        foreach ($allTag as $tag) {
                            $idTags = $tag->getId();

                            $links = $em->getRepository('GedBundle:Linktag')->findByIdtag($idTags);

                            //si il a bien trouver un lien
                            if($links != null){

                                //pour chaque lien recupére Id du fichier corespondant
                                foreach ($links as $link) {
                                    $idFile = $link->getIdfile();

                                    //si linkyTagTab et defini
                                    if($linkTagTab!=null){
                                        //compare le tableau a la valeur de idfile
                                        //on definit un compteur pour n' avoir q'une seule ocurence.
                                        $countTag = 0;
                                        foreach ($linkTagTab as $test) {
                                            //si la valeur est diffente alors ajoute la au tableau.
                                            if(($test['idFile'] != $idFile)&& $countTag==0){

                                                $linkTagTab[]= array('idFile' =>$idFile,);

                                                $countTag++;
                                            }
                                        }                                
                                    }
                                    //sinon le definir
                                    else{
                                        $linkTagTab[]= array('idFile' =>$idFile,);
                                    }                            
                                }
                            }
                        }
                        //pour chaque lien d'id de fichier et de l'id utilisateur aller les chercher et remplir un tableau
                        
                        foreach ($linkTagTab as $tagId) {
                            
                            //si le type est defini
                            if ($fileType!=0){
                                foreach ($fileType as $defType){
                                    $tagFile = $em->getRepository('GedBundle:Gedfiles')->findOneBy(array(
                                                                                                    'id'=>$tagId['idFile'],
                                                                                                    'idowner'=>$idUser,
                                                                                                    'idcategory'=>$searchCategorie,
                                                                                                    'idsouscategory'=>$searchSscategorie,
                                                                                                    'type'=>$defType,
                                                                                                ));
                                    if($tagFile!=null){
                                        // on recupere son id
                                        $id = $tagFile->getId();
                                        $count=0;
                                        if($nameTab!=null){                                    
                                            //pourchaque entre dans le tableau myFileTab
                                            foreach ($nameTab as $myFile) {
                                                //on recupere id pour chaque entré du tableau
                                                $myFileId = $myFile['id'];

                                                //et on la compare a chaque fois avec id de tagFile.
                                                if($myFileId==$id){
                                                    $count++;
                                                }
                                            }
                                        }
                                        if($tagTab!=null){
                                            //pourchaque entre dans le tableau myFileTab
                                            foreach ($tagTab as $myFile) {
                                                //on recupere id pour chaque entré du tableau
                                                $myFileId = $myFile['id'];
                                                //et on la compare a chaque fois avec id de tagFile.
                                                if($myFileId==$id){
                                                    $count++;
                                                }
                                            }
                                        }
                                        if($count==0){
                                            //nom du fichier
                                            $name = $tagFile->getOriginalName();
                                            //son id
                                            $id = $tagFile->getId();
                                            //son type
                                            $type = $tagFile->getType();
                                            //son chemin
                                            $path = $tagFile->getPath();

                                            //récuperation des favoris.
                                            $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$id,
                                                                                                                        'iduser'=>$idUser,
                                                                                                                        )
                                            );
                                            if ($bookmark!=null){
                                                $bookmark = 1;
                                            }

                                            else{
                                                $bookmark = 0;
                                            }

                                            //on met tout dans un tableau
                                            array_push($tagTab, array(
                                                'name' => $name,
                                                'id' => $id,
                                                'type' => $type,
                                                'path' => $path,
                                                'bookmark' => $bookmark,
                                            ));
                                        }
                                    }                                                      
                                }
                            }
                            //sinon
                            if ($fileType==0) {
                                $tagFile = $em->getRepository('GedBundle:Gedfiles')->findOneBy(array(
                                                                                                        'id'=>$tagId['idFile'],
                                                                                                        'idowner'=>$idUser,
                                                                                                        'idcategory'=>$searchCategorie,
                                                                                                        'idsouscategory'=>$searchSscategorie,
                                                                                                    ));
                                if($tagFile!=null){
                                    // on recupere son id
                                    $id = $tagFile->getId();
                                    $count=0;
                                    if($nameTab!=null){
                                        //pourchaque entre dans le tableau myFileTab
                                        foreach ($nameTab as $myFile) {
                                            //on recupere id pour chaque entré du tableau
                                            $myFileId = $myFile['id'];

                                            //et on la compare a chaque fois avec id de tagFile.
                                            if($myFileId==$id){
                                                $count++;
                                            }
                                        }
                                    }
                                    if($tagTab!=null){
                                        //pourchaque entre dans le tableau myFileTab
                                        foreach ($tagTab as $myFile) {
                                            //on recupere id pour chaque entré du tableau
                                            $myFileId = $myFile['id'];
                                            //et on la compare a chaque fois avec id de tagFile.
                                            if($myFileId==$id){
                                                $count++;
                                            }
                                        }
                                    }
                                    if($count==0){
                                        //nom du fichier
                                        $name = $tagFile->getOriginalName();
                                        //son id
                                        $id = $tagFile->getId();
                                        //son type
                                        $type = $tagFile->getType();
                                        //son chemin
                                        $path = $tagFile->getPath();

                                        //récuperation des favoris.
                                        $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$id,
                                                                                                                    'iduser'=>$idUser,
                                                                                                                    )
                                        );
                                        if ($bookmark!=null){
                                            $bookmark = 1;
                                        }

                                        else{
                                            $bookmark = 0;
                                        }

                                        //on met tout dans un tableau
                                        array_push($tagTab, array(
                                            'name' => $name,
                                            'id' => $id,
                                            'type' => $type,
                                            'path' => $path,
                                            'bookmark' => $bookmark,
                                        ));
                                    }
                                }                        
                            }
                        }
                    }
                    $searchCount++;
                }

                //RECHERCHE POUR LES GROUPES - Fichiers

                //on recherche dans quels groupe l'utilisateur est
                $linkGroups = $em->getRepository('GedBundle:Linkgroup')->findByIduser($idUser);
                $grpNameTab = [];
                //si on un resultat
                if($linkGroups!=null){
                    $groupTab = [];
                    //pour chaque resultat, ajout les dans un tableau
                    foreach ($linkGroups as $group) {
                        $idGrp = $group->getIdgroup();
                        array_push($groupTab, $idGrp);
                    }

                    //pour chaque group contenu dans mon tableau
                    foreach ($groupTab as $group) {
                        $searchCount=0;
                        foreach ($tabSearch as $searchRecherche) {
                            if($searchCount<=4 && $searchRecherche!=''){
                                if($fileType!=0){
                                    $groupFiles = $em->getRepository('GedBundle:Gedfiles')->sscategoryGrpTypeSearch($searchRecherche, $group, $searchCategorie, $searchSscategorie, $fileType);
                                    if($groupFiles!=null){
                                        foreach ($groupFiles as $files) {
                                            $idGrpFile = $files->getId();
                                            $count = 0;
                                            
                                            //si mes ficher ne son pas vide
                                            if ($nameTab!=null) {
                                                //pour chaque ficher dans mes ficher 
                                                foreach ($nameTab as $fileTab) {
                                                    //si id de mon ficher est egale a id de ficher de mon groupe
                                                    if( ($fileTab['id'] == $idGrpFile) ){
                                                        $count ++;                                            
                                                    }
                                                }                                    
                                            }
                                            // si aucun Id n'a été trouver dans $nameTab ou si $nameTab nexista pas 
                                            if($count==0){
                                                //verification pour les tag 
                                                //si tagTab existe
                                                if (isset($tagTab)) {
                                                    //pour chaque ficher dans mes ficher 
                                                    foreach ($tagTab as $fileTab) {
                                                        //si id de mon ficher est egale a id de ficher de mon groupe
                                                        if( ($fileTab['id'] == $idGrpFile) ){
                                                            $count ++;                                            
                                                        }
                                                    }                                                                        
                                                }
                                            }
                                            if($count==0){
                                                // verification si le grpNameTab n'est pas defini
                                                if($grpNameTab==null){

                                                    // nom du fichier
                                                    $name = $files->getOriginalName();
                                                    //son type
                                                    $type = $files->getType();
                                                    //son chemin
                                                    $path = $files->getPath();

                                                    //récuperation des favoris.

                                                    $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$idGrpFile,
                                                                                                                                'iduser'=>$idUser,
                                                                                                                                )
                                                    );

                                                    if ($bookmark!=null){
                                                        $bookmark = 1;
                                                    }

                                                    else{
                                                        $bookmark = 0;
                                                    }

                                                    //on met tout dans un tableau
                                                    array_push($grpNameTab, array(
                                                        'name' => $name,
                                                        'id' => $idGrpFile,
                                                        'type' => $type,
                                                        'path' => $path,
                                                        'bookmark' => $bookmark,
                                                    ));
                                                }
                                                //si le tableau est deja défini alors le parcourir et verifier
                                                if($grpNameTab!=null){
                                                    
                                                    foreach ($grpNameTab as $fileTab) {
                                                        //si id contenu dans mon tableau n est
                                                        if( ($fileTab['id'] == $idGrpFile) ){
                                                            $count ++;                                            
                                                        }
                                                    }
                                                    //si l id na pas ete trouver alors le rajouté
                                                    if($count==0){

                                                        // nom du fichier
                                                        $name = $files->getOriginalName();
                                                        //son type
                                                        $type = $files->getType();
                                                        //son chemin
                                                        $path = $files->getPath();

                                                        //récuperation des favoris.
                                                        $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$idGrpFile,
                                                                                                                                    'iduser'=>$idUser,
                                                                                                                                    )
                                                        );
                                                        if ($bookmark!=null){
                                                            $bookmark = 1;
                                                        }

                                                        else{
                                                            $bookmark = 0;
                                                        }

                                                        //on met tout dans un tableau
                                                        array_push($grpNameTab, array(
                                                            'name' => $name,
                                                            'id' => $idGrpFile,
                                                            'type' => $type,
                                                            'path' => $path,
                                                            'bookmark' => $bookmark,
                                                        ));                                            
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                else{
                                    $groupFiles = $em->getRepository('GedBundle:Gedfiles')->sscategoryGrpSearch($searchRecherche,$group,$searchCategorie,$searchSscategorie);

                                    if($groupFiles!=null){
                                        foreach ($groupFiles as $files) {
                                            $idGrpFile = $files->getId();
                                            $count = 0;
                                            
                                            //si mes ficher ne son pas vide
                                            if ($nameTab!=null) {
                                                //pour chaque ficher dans mes ficher 
                                                foreach ($nameTab as $fileTab) {
                                                    //si id de mon ficher est egale a id de ficher de mon groupe
                                                    if( ($fileTab['id'] == $idGrpFile) ){
                                                        $count ++;                                            
                                                    }
                                                }                                    
                                            }
                                            // si aucun Id n'a été trouver dans $nameTab ou si $nameTab nexista pas 
                                            if($count==0){
                                                //verification pour les tag 
                                                //si tagTab existe
                                                if (isset($tagTab)) {
                                                //pour chaque ficher dans mes ficher 
                                                    foreach ($tagTab as $fileTab) {
                                                        //si id de mon ficher est egale a id de ficher de mon groupe
                                                        if( ($fileTab['id'] == $idGrpFile) ){
                                                            $count ++;                                            
                                                        }
                                                    }                                                                        
                                                }
                                            }
                                            if($count==0){

                                                // verification si le grpNameTab n'est pas defini
                                                if($grpNameTab==null){

                                                    // nom du fichier
                                                    $name = $files->getOriginalName();
                                                    //son type
                                                    $type = $files->getType();
                                                    //son chemin
                                                    $path = $files->getPath();

                                                    //récuperation des favoris.

                                                    $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$idGrpFile,
                                                                                                                                'iduser'=>$idUser,
                                                                                                                                )
                                                    );

                                                    if ($bookmark!=null){
                                                        $bookmark = 1;
                                                    }

                                                    else{
                                                        $bookmark = 0;
                                                    }

                                                    //on met tout dans un tableau
                                                    array_push($grpNameTab, array(
                                                        'name' => $name,
                                                        'id' => $idGrpFile,
                                                        'type' => $type,
                                                        'path' => $path,
                                                        'bookmark' => $bookmark,
                                                    ));
                                                }
                                                //si le tableau est deja défini alors le parcourir et verifier
                                                if($grpNameTab!=null){
                                                    
                                                    foreach ($grpNameTab as $fileTab) {
                                                        //si id contenu dans mon tableau n est
                                                        if( ($fileTab['id'] == $idGrpFile) ){
                                                            $count ++;                                            
                                                        }
                                                    }
                                                    //si l id na pas ete trouver alors le rajouté
                                                    if($count==0){

                                                        // nom du fichier
                                                        $name = $files->getOriginalName();
                                                        //son type
                                                        $type = $files->getType();
                                                        //son chemin
                                                        $path = $files->getPath();

                                                        //récuperation des favoris.
                                                        $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$idGrpFile,
                                                                                                                                    'iduser'=>$idUser,
                                                                                                                                    )
                                                        );
                                                        if ($bookmark!=null){
                                                            $bookmark = 1;
                                                        }

                                                        else{
                                                            $bookmark = 0;
                                                        }

                                                        //on met tout dans un tableau
                                                        array_push($grpNameTab, array(
                                                            'name' => $name,
                                                            'id' => $idGrpFile,
                                                            'type' => $type,
                                                            'path' => $path,
                                                            'bookmark' => $bookmark,
                                                        ));                                            
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $searchCount++;
                        }
                    }
                }

                //RECHERCHE POUR LES GROUPES - tags

                //on recherche dans quels groupe l'utilisateur est
                $linkGroups = $em->getRepository('GedBundle:Linkgroup')->findByIduser($idUser);
                $grpTagTab = [];
                //si on un resultat
                if($linkGroups!=null){
                    $groupsTab = [];
                    //pour chaque resultat, ajout les dans un tableau
                    foreach ($linkGroups as $group) {
                        // var_dump($groupTab);
                        $idGrp = $group->getIdgroup();
                        array_push($groupsTab, $idGrp);
                    }
                    //on garde donc le tableau des group de l' utilisateur pour une comparaison plus tard
                    $searchCount=0;
                    foreach ($tabSearch as $searchRecherche) {
                        if($searchCount<=4 && $searchRecherche!=''){
                            //on recherhce dans tout les mots clef une corespondance.
                            $tags = $em->getRepository('GedBundle:Gedtag')->tagSearch($searchRecherche);

                            //pour chaque tag trouver
                            foreach ($tags as $tag) {
                                //on recupere l id du tag
                                $idTag = $tag->getId();

                                //on recherche ses differents lien avec les fichers
                                $linksTag = $em->getRepository('GedBundle:Linktag')->findByIdtag($idTag);

                                // var_dump($linksTag);

                                //si il y a bien des lien pour le tag
                                if($linksTag!=null){

                                    //pour chaque lien trouvé
                                    foreach ($linksTag as $linkTag) {
                                        //on récupere l' id du fichier.
                                        $id = $linkTag->getIdfile();

                                        // puis on recherche le fichier.
                                        //pour chaqu' un  de mes groupes
                                        foreach ($groupsTab as $group) {
                                            if ($fileType!=0){
                                                foreach ($fileType as $defType){
                                                    //recherche du fichier correspondant Id du fichier et Id du groupe
                                                    $grpFileIdSearch = $em->getRepository('GedBundle:Gedfiles')->findOneBy( array(
                                                        'idgroup' => $group,
                                                        'id' =>$id,
                                                        'idcategory' =>$searchCategorie,
                                                        'idsouscategory'=>$searchSscategorie,
                                                        'type'=>$defType,
                                                        )
                                                    );
                                                    //si il trouve un resultat alors asigne lui la valeur.
                                                    if($grpFileIdSearch!=null){
                                                        $grpFileId=$grpFileIdSearch;
                                                    }
                                                }
                                            }
                                            else{
                                                //recherche du fichier correspondant Id du fichier et Id du groupe
                                                $grpFileId = $em->getRepository('GedBundle:Gedfiles')->findOneBy( array(
                                                    'idgroup' => $group,
                                                    'id' =>$id,
                                                    'idcategory' =>$searchCategorie,
                                                    'idsouscategory'=>$searchSscategorie,
                                                    )
                                                );
                                            }
                                            if(!isset($grpFileId)){
                                                $grpFileId=null;
                                            }
                                            //le resultat n'est pas null
                                            if($grpFileId!=null){

                                                //on initialise un compeur
                                                $count = 0;
                                                //on recupere id du fichier trouvé
                                                $idFile = $grpFileId->getId();

                                                //on a maintennant le fichier correspondant il manque plus a le trier.
                                                
                                                //si nameTab est defini
                                                if($nameTab!=null){
                                                    foreach ($nameTab as $fileTab) {
                                                        //si id contenu dans mon tableau correspon a l' id de mon fichier
                                                        if( ($fileTab['id'] == $idFile) ){
                                                            //incremente le compteur
                                                            $count ++;                                            
                                                        }
                                                    }
                                                }                                        
                                                //si tagTab est defini
                                                if($tagTab!=null){
                                                    foreach ($tagTab as $fileTab) {
                                                        //si id contenu dans mon tableau correspon a l' id de mon fichier
                                                        if( ($fileTab['id'] == $idFile) ){
                                                            //incremente le compteur
                                                            $count ++;                                            
                                                        }
                                                    }
                                                }
                                                //si grpNameTab est defini
                                                if($grpNameTab!=null){
                                                    foreach ($grpNameTab as $fileTab) {
                                                        //si id contenu dans mon tableau correspon a l' id de mon fichier
                                                        if( ($fileTab['id'] == $idFile) ){
                                                            //incremente le compteur
                                                            $count ++;                                            
                                                        }
                                                    }
                                                }
                                                //si grpTagTab est defini
                                                if($grpTagTab!=null){
                                                    foreach ($grpTagTab as $fileTab) {
                                                        //si id contenu dans mon tableau correspon a l' id de mon fichier
                                                        if( ($fileTab['id'] == $idFile) ){
                                                            //incremente le compteur
                                                            $count ++;                                            
                                                        }
                                                    }                                                
                                                }
                                                //si le compeur est a 0
                                                if($count==0){
                                                    //C'est que le ficher n' est present nul part !
                                                    // nom du fichier
                                                    $name = $grpFileId->getOriginalName();
                                                    //son type
                                                    $type = $grpFileId->getType();
                                                    //son chemin
                                                    $path = $grpFileId->getPath();

                                                    //récuperation des favoris.
                                                    $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$idFile,
                                                                                                                                'iduser'=>$idUser,
                                                                                                                                )
                                                    );
                                                    if ($bookmark!=null){
                                                        $bookmark = 1;
                                                    }

                                                    else{
                                                        $bookmark = 0;
                                                    }

                                                    //on met tout dans un tableau
                                                    array_push($grpTagTab, array(
                                                        'name' => $name,
                                                        'id' => $idFile,
                                                        'type' => $type,
                                                        'path' => $path,
                                                        'bookmark' => $bookmark,
                                                    ));                                                                                        
                                                }
                                            }                                   
                                        }
                                    }
                                }
                            }
                        }
                        $searchCount++;
                    }
                }
            }
            //si catégory est defini
            elseif (($searchCategorie != 0)){
                //RECHERCHE POUR L' UTILISATEUR - Fichiers

                //recherche le nom du fichier
                //si le type et defini
                $searchCount=0;
                foreach ($tabSearch as $searchRecherche) {
                    if($searchCount<=4 && $searchRecherche!=''){
                        if($fileType!=0){
                            $myfiles = $em->getRepository('GedBundle:Gedfiles')->categoryTypeSearch($searchRecherche, $idUser, $searchCategorie, $fileType);
                        }
                        //sinon
                        else{
                            $myfiles = $em->getRepository('GedBundle:Gedfiles')->categorySearch($searchRecherche, $idUser, $searchCategorie);
                        }

                        foreach ($myfiles as $myfile) {
                            
                            //nom du fichier
                            $name = $myfile->getOriginalName();
                            //son id
                            $id = $myfile->getId();
                            //son type
                            $type = $myfile->getType();
                            //son chemin
                            $path = $myfile->getPath();

                            //récuperation des favoris.
                            $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$id,
                                                                                                        'iduser'=>$idUser,
                                                                                                        )
                            );
                            if ($bookmark!=null){
                                $bookmark = 1;
                            }

                            else{
                                $bookmark = 0;
                            }

                            //on met tout dans un tableau
                            array_push($nameTab, array(
                                'name' => $name,
                                'id' => $id,
                                'type' => $type,
                                'path' => $path,
                                'bookmark' => $bookmark,
                            ));
                        }
                    }
                    $searchCount++;
                }

                //RECHERCHE POUR L' UTILISATEUR - Tags

                $searchCount=0;
                foreach ($tabSearch as $searchRecherche) {
                    if($searchCount<=4 && $searchRecherche!=''){
                        //on recherche tout les tag ayant la valeur
                        $allTag = $em->getRepository('GedBundle:Gedtag')->tagSearch($searchRecherche);
                        $linkTagTab=[];
                        foreach ($allTag as $tag) {
                            $idTags = $tag->getId();

                            $links = $em->getRepository('GedBundle:Linktag')->findByIdtag($idTags);

                            //si il a bien trouver un lien
                            if($links != null){

                                //pour chaque lien recupére Id du fichier corespondant
                                foreach ($links as $link) {
                                    $idFile = $link->getIdfile();

                                    //si linkyTagTab et defini
                                    if($linkTagTab!=null){
                                        //compare le tableau a la valeur de idfile
                                        //on definit un compteur pour n' avoir q'une seule ocurence.
                                        $countTag = 0;
                                        foreach ($linkTagTab as $test) {
                                            //si la valeur est diffente alors ajoute la au tableau.
                                            if(($test['idFile'] != $idFile)&& $countTag==0){

                                                $linkTagTab[]= array('idFile' =>$idFile,);

                                                $countTag++;
                                            }
                                        }                                
                                    }
                                    //sinon le definir
                                    else{
                                        $linkTagTab[]= array('idFile' =>$idFile,);
                                    }                            
                                }
                            }
                        }
                        //pour chaque lien d'id de fichier et de l'id utilisateur aller les chercher et remplir un tableau
                        
                        foreach ($linkTagTab as $tagId) {
                            //si le type est defini
                            if ($fileType!=0){
                                foreach ($fileType as $defType){
                                    $tagFile = $em->getRepository('GedBundle:Gedfiles')->findOneBy(array(
                                                                                                    'id'=>$tagId['idFile'],
                                                                                                    'idowner'=>$idUser,
                                                                                                    'idcategory'=>$searchCategorie,
                                                                                                    'type'=>$defType,
                                                                                                ));
                                    if($tagFile!=null){
                                        // on recupere son id
                                        $id = $tagFile->getId();
                                        $count=0;
                                        if($nameTab!=null){                                    
                                            //pourchaque entre dans le tableau myFileTab
                                            foreach ($nameTab as $myFile) {
                                                //on recupere id pour chaque entré du tableau
                                                $myFileId = $myFile['id'];

                                                //et on la compare a chaque fois avec id de tagFile.
                                                if($myFileId==$id){
                                                    $count++;
                                                }
                                            }
                                        }
                                        if($tagTab!=null){
                                            //pourchaque entre dans le tableau myFileTab
                                            foreach ($tagTab as $myFile) {
                                                //on recupere id pour chaque entré du tableau
                                                $myFileId = $myFile['id'];
                                                //et on la compare a chaque fois avec id de tagFile.
                                                if($myFileId==$id){
                                                    $count++;
                                                }
                                            }
                                        }
                                        if($count==0){
                                            //nom du fichier
                                            $name = $tagFile->getOriginalName();
                                            //son id
                                            $id = $tagFile->getId();
                                            //son type
                                            $type = $tagFile->getType();
                                            //son chemin
                                            $path = $tagFile->getPath();

                                            //récuperation des favoris.
                                            $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$id,
                                                                                                                        'iduser'=>$idUser,
                                                                                                                        )
                                            );
                                            if ($bookmark!=null){
                                                $bookmark = 1;
                                            }

                                            else{
                                                $bookmark = 0;
                                            }

                                            //on met tout dans un tableau
                                            array_push($tagTab, array(
                                                'name' => $name,
                                                'id' => $id,
                                                'type' => $type,
                                                'path' => $path,
                                                'bookmark' => $bookmark,
                                            ));
                                        }
                                    }                                                      
                                }
                            }
                            //sinon
                            if ($fileType==0) {
                                $tagFile = $em->getRepository('GedBundle:Gedfiles')->findOneBy(array(
                                                                                                        'id'=>$tagId['idFile'],
                                                                                                        'idowner'=>$idUser,
                                                                                                        'idcategory'=>$searchCategorie,
                                                                                                    ));
                                if($tagFile!=null){
                                    // on recupere son id
                                    $id = $tagFile->getId();
                                    $count=0;
                                    if($nameTab!=null){
                                        //pourchaque entre dans le tableau myFileTab
                                        foreach ($nameTab as $myFile) {
                                            //on recupere id pour chaque entré du tableau
                                            $myFileId = $myFile['id'];

                                            //et on la compare a chaque fois avec id de tagFile.
                                            if($myFileId==$id){
                                                $count++;
                                            }
                                        }
                                    }
                                    if($tagTab!=null){
                                        //pourchaque entre dans le tableau myFileTab
                                        foreach ($tagTab as $myFile) {
                                            //on recupere id pour chaque entré du tableau
                                            $myFileId = $myFile['id'];
                                            //et on la compare a chaque fois avec id de tagFile.
                                            if($myFileId==$id){
                                                $count++;
                                            }
                                        }
                                    }
                                    if($count==0){
                                        //nom du fichier
                                        $name = $tagFile->getOriginalName();
                                        //son id
                                        $id = $tagFile->getId();
                                        //son type
                                        $type = $tagFile->getType();
                                        //son chemin
                                        $path = $tagFile->getPath();

                                        //récuperation des favoris.
                                        $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$id,
                                                                                                                    'iduser'=>$idUser,
                                                                                                                    )
                                        );
                                        if ($bookmark!=null){
                                            $bookmark = 1;
                                        }

                                        else{
                                            $bookmark = 0;
                                        }

                                        //on met tout dans un tableau
                                        array_push($tagTab, array(
                                            'name' => $name,
                                            'id' => $id,
                                            'type' => $type,
                                            'path' => $path,
                                            'bookmark' => $bookmark,
                                        ));
                                    }
                                }                        
                            }                    
                        }
                    }
                    $searchCount++;
                }

                //RECHERCHE POUR LES GROUPES - Fichiers

                //on recherche dans quels groupe l'utilisateur est
                $linkGroups = $em->getRepository('GedBundle:Linkgroup')->findByIduser($idUser);
                
                //si on un resultat
                if($linkGroups!=null){
                    $groupTab = [];
                    //pour chaque resultat, ajout les dans un tableau
                    foreach ($linkGroups as $group) {
                        $idGrp = $group->getIdgroup();
                        array_push($groupTab, $idGrp);
                    }

                    //pour chaque group contenu dans mon tableau
                    foreach ($groupTab as $group) {
                        $searchCount=0;
                        foreach ($tabSearch as $searchRecherche) {
                            if($searchCount<=4 && $searchRecherche!=''){
                                if($fileType!=0){
                                    $groupFiles = $em->getRepository('GedBundle:Gedfiles')->categoryGrpTypeSearch($searchRecherche, $group, $searchCategorie, $fileType);
                                    if($groupFiles!=null){
                                        foreach ($groupFiles as $files) {
                                            $idGrpFile = $files->getId();
                                            $count = 0;
                                            
                                            //si mes ficher ne son pas vide
                                            if ($nameTab!=null) {
                                                //pour chaque ficher dans mes ficher 
                                                foreach ($nameTab as $fileTab) {
                                                    //si id de mon ficher est egale a id de ficher de mon groupe
                                                    if( ($fileTab['id'] == $idGrpFile) ){
                                                        $count ++;                                            
                                                    }
                                                }                                    
                                            }
                                            // si aucun Id n'a été trouver dans $nameTab ou si $nameTab nexista pas 
                                            if($count==0){
                                                //verification pour les tag 
                                                //si tagTab existe
                                                if (isset($tagTab)) {
                                                    //pour chaque ficher dans mes ficher 
                                                    foreach ($tagTab as $fileTab) {
                                                        //si id de mon ficher est egale a id de ficher de mon groupe
                                                        if( ($fileTab['id'] == $idGrpFile) ){
                                                            $count ++;                                            
                                                        }
                                                    }                                                                        
                                                }
                                            }
                                            if($count==0){
                                                // verification si le grpNameTab n'est pas defini
                                                if($grpNameTab==null){

                                                    // nom du fichier
                                                    $name = $files->getOriginalName();
                                                    //son type
                                                    $type = $files->getType();
                                                    //son chemin
                                                    $path = $files->getPath();

                                                    //récuperation des favoris.

                                                    $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$idGrpFile,
                                                                                                                                'iduser'=>$idUser,
                                                                                                                                )
                                                    );

                                                    if ($bookmark!=null){
                                                        $bookmark = 1;
                                                    }

                                                    else{
                                                        $bookmark = 0;
                                                    }

                                                    //on met tout dans un tableau
                                                    array_push($grpNameTab, array(
                                                        'name' => $name,
                                                        'id' => $idGrpFile,
                                                        'type' => $type,
                                                        'path' => $path,
                                                        'bookmark' => $bookmark,
                                                    ));
                                                }
                                                //si le tableau est deja défini alors le parcourir et verifier
                                                if($grpNameTab!=null){
                                                    
                                                    foreach ($grpNameTab as $fileTab) {
                                                        //si id contenu dans mon tableau n est
                                                        if( ($fileTab['id'] == $idGrpFile) ){
                                                            $count ++;                                            
                                                        }
                                                    }
                                                    //si l id na pas ete trouver alors le rajouté
                                                    if($count==0){

                                                        // nom du fichier
                                                        $name = $files->getOriginalName();
                                                        //son type
                                                        $type = $files->getType();
                                                        //son chemin
                                                        $path = $files->getPath();

                                                        //récuperation des favoris.
                                                        $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$idGrpFile,
                                                                                                                                    'iduser'=>$idUser,
                                                                                                                                    )
                                                        );
                                                        if ($bookmark!=null){
                                                            $bookmark = 1;
                                                        }

                                                        else{
                                                            $bookmark = 0;
                                                        }

                                                        //on met tout dans un tableau
                                                        array_push($grpNameTab, array(
                                                            'name' => $name,
                                                            'id' => $idGrpFile,
                                                            'type' => $type,
                                                            'path' => $path,
                                                            'bookmark' => $bookmark,
                                                        ));                                            
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                else{
                                    $groupFiles = $em->getRepository('GedBundle:Gedfiles')->categoryGrpSearch($searchRecherche,$group,$searchCategorie);

                                    if($groupFiles!=null){
                                        foreach ($groupFiles as $files) {
                                            $idGrpFile = $files->getId();
                                            $count = 0;
                                            
                                            //si mes ficher ne son pas vide
                                            if ($nameTab!=null) {
                                                //pour chaque ficher dans mes ficher 
                                                foreach ($nameTab as $fileTab) {
                                                    //si id de mon ficher est egale a id de ficher de mon groupe
                                                    if( ($fileTab['id'] == $idGrpFile) ){
                                                        $count ++;                                            
                                                    }
                                                }                                    
                                            }
                                            // si aucun Id n'a été trouver dans $nameTab ou si $nameTab nexista pas 
                                            if($count==0){
                                                //verification pour les tag 
                                                //si tagTab existe
                                                if (isset($tagTab)) {
                                                //pour chaque ficher dans mes ficher 
                                                    foreach ($tagTab as $fileTab) {
                                                        //si id de mon ficher est egale a id de ficher de mon groupe
                                                        if( ($fileTab['id'] == $idGrpFile) ){
                                                            $count ++;                                            
                                                        }
                                                    }                                                                        
                                                }
                                            }
                                            if($count==0){

                                                // verification si le grpNameTab n'est pas defini
                                                if($grpNameTab==null){

                                                    // nom du fichier
                                                    $name = $files->getOriginalName();
                                                    //son type
                                                    $type = $files->getType();
                                                    //son chemin
                                                    $path = $files->getPath();

                                                    //récuperation des favoris.

                                                    $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$idGrpFile,
                                                                                                                                'iduser'=>$idUser,
                                                                                                                                )
                                                    );

                                                    if ($bookmark!=null){
                                                        $bookmark = 1;
                                                    }

                                                    else{
                                                        $bookmark = 0;
                                                    }

                                                    //on met tout dans un tableau
                                                    array_push($grpNameTab, array(
                                                        'name' => $name,
                                                        'id' => $idGrpFile,
                                                        'type' => $type,
                                                        'path' => $path,
                                                        'bookmark' => $bookmark,
                                                    ));
                                                }
                                                //si le tableau est deja défini alors le parcourir et verifier
                                                if($grpNameTab!=null){
                                                    
                                                    foreach ($grpNameTab as $fileTab) {
                                                        //si id contenu dans mon tableau n est
                                                        if( ($fileTab['id'] == $idGrpFile) ){
                                                            $count ++;                                            
                                                        }
                                                    }
                                                    //si l id na pas ete trouver alors le rajouté
                                                    if($count==0){

                                                        // nom du fichier
                                                        $name = $files->getOriginalName();
                                                        //son type
                                                        $type = $files->getType();
                                                        //son chemin
                                                        $path = $files->getPath();

                                                        //récuperation des favoris.
                                                        $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$idGrpFile,
                                                                                                                                    'iduser'=>$idUser,
                                                                                                                                    )
                                                        );
                                                        if ($bookmark!=null){
                                                            $bookmark = 1;
                                                        }

                                                        else{
                                                            $bookmark = 0;
                                                        }

                                                        //on met tout dans un tableau
                                                        array_push($grpNameTab, array(
                                                            'name' => $name,
                                                            'id' => $idGrpFile,
                                                            'type' => $type,
                                                            'path' => $path,
                                                            'bookmark' => $bookmark,
                                                        ));                                            
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $searchCount++;
                        }
                    }
                }

                //RECHERCHE POUR LES GROUPES - tags

                //on recherche dans quels groupe l'utilisateur est
                $linkGroups = $em->getRepository('GedBundle:Linkgroup')->findByIduser($idUser);
                $grpTagTab = [];
                //si on un resultat
                if($linkGroups!=null){
                    $groupsTab = [];
                    //pour chaque resultat, ajout les dans un tableau
                    foreach ($linkGroups as $group) {
                        // var_dump($groupTab);
                        $idGrp = $group->getIdgroup();
                        array_push($groupsTab, $idGrp);
                    }
                    //on garde donc le tableau des group de l' utilisateur pour une comparaison plus tard
                    $searchCount=0;
                    foreach ($tabSearch as $searchRecherche) {
                        if($searchCount<=4 && $searchRecherche!=''){
                            //on recherhce dans tout les mots clef une corespondance.
                            $tags = $em->getRepository('GedBundle:Gedtag')->tagSearch($searchRecherche);

                            //pour chaque tag trouver
                            foreach ($tags as $tag) {
                                //on recupere l id du tag
                                $idTag = $tag->getId();

                                //on recherche ses differents lien avec les fichers
                                $linksTag = $em->getRepository('GedBundle:Linktag')->findByIdtag($idTag);

                                //si il y a bien des lien pour le tag
                                if($linksTag!=null){

                                    //pour chaque lien trouvé
                                    foreach ($linksTag as $linkTag) {
                                        //on récupere l' id du fichier.
                                        $id = $linkTag->getIdfile();

                                        // puis on recherche le fichier.
                                        //pour chaqu' un  de mes groupes
                                        foreach ($groupsTab as $group) {
                                            if ($fileType!=0){
                                                foreach ($fileType as $defType){
                                                    //recherche du fichier correspondant Id du fichier et Id du groupe
                                                    $grpFileIdSearch = $em->getRepository('GedBundle:Gedfiles')->findOneBy( array(
                                                        'idgroup' => $group,
                                                        'id' =>$id,
                                                        'idcategory' =>$searchCategorie,
                                                        'type'=>$defType,
                                                        )
                                                    );
                                                    //si il trouve un resultat alors asigne lui la valeur.
                                                    if($grpFileIdSearch!=null){
                                                        $grpFileId=$grpFileIdSearch;
                                                    }
                                                }
                                            }
                                            else{
                                                //recherche du fichier correspondant Id du fichier et Id du groupe
                                                $grpFileId = $em->getRepository('GedBundle:Gedfiles')->findOneBy( array(
                                                    'idgroup' => $group,
                                                    'id' =>$id,
                                                    'idcategory' =>$searchCategorie,
                                                    )
                                                );
                                            }
                                            if(!isset($grpFileId)){
                                                $grpFileId=null;
                                            }
                                            //le resultat n'est pas null
                                            if($grpFileId!=null){
                                                //on initialise un compeur
                                                $count = 0;
                                                //on recupere id du fichier trouvé
                                                $idFile = $grpFileId->getId();
                                                //on a maintennant le fichier correspondant il manque plus a le trier.
                                                
                                                //si nameTab est defini
                                                if($nameTab!=null){
                                                    foreach ($nameTab as $fileTab) {
                                                        //si id contenu dans mon tableau correspon a l' id de mon fichier
                                                        if( ($fileTab['id'] == $idFile) ){
                                                            //incremente le compteur
                                                            $count ++;                                            
                                                        }
                                                    }
                                                }                                        
                                                //si tagTab est defini
                                                if($tagTab!=null){
                                                    foreach ($tagTab as $fileTab) {
                                                        //si id contenu dans mon tableau correspon a l' id de mon fichier
                                                        if( ($fileTab['id'] == $idFile) ){
                                                            //incremente le compteur
                                                            $count ++;                                            
                                                        }
                                                    }
                                                }
                                                //si grpNameTab est defini
                                                if($grpNameTab!=null){
                                                    foreach ($grpNameTab as $fileTab) {
                                                        //si id contenu dans mon tableau correspon a l' id de mon fichier
                                                        if( ($fileTab['id'] == $idFile) ){
                                                            //incremente le compteur
                                                            $count ++;                                            
                                                        }
                                                    }
                                                }
                                                //si grpTagTab est defini
                                                if($grpTagTab!=null){
                                                    foreach ($grpTagTab as $fileTab) {
                                                        //si id contenu dans mon tableau correspon a l' id de mon fichier
                                                        if( ($fileTab['id'] == $idFile) ){
                                                            //incremente le compteur
                                                            $count ++;                                            
                                                        }
                                                    }                                                
                                                }
                                                //si le compeur est a 0
                                                if($count==0){
                                                    //C'est que le ficher n' est present nul part !
                                                    // nom du fichier
                                                    $name = $grpFileId->getOriginalName();
                                                    //son type
                                                    $type = $grpFileId->getType();
                                                    //son chemin
                                                    $path = $grpFileId->getPath();

                                                    //récuperation des favoris.
                                                    $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$idFile,
                                                                                                                                'iduser'=>$idUser,
                                                                                                                                )
                                                    );
                                                    if ($bookmark!=null){
                                                        $bookmark = 1;
                                                    }

                                                    else{
                                                        $bookmark = 0;
                                                    }

                                                    //on met tout dans un tableau
                                                    array_push($grpTagTab, array(
                                                        'name' => $name,
                                                        'id' => $idFile,
                                                        'type' => $type,
                                                        'path' => $path,
                                                        'bookmark' => $bookmark,
                                                    ));                                                                                        
                                                }
                                            }                                   
                                        }
                                    }
                                }
                            }
                        }
                        $searchCount++;
                    }
                }
            }
            //Si rien n' est défini
            else{
                //RECHERCHE POUR L' UTILISATEUR - Fichiers

                $searchCount=0;
                foreach ($tabSearch as $searchRecherche) {
                    if($searchCount<=4 && $searchRecherche!=''){
                        //recherche le nom du fichier
                        //si le type et defini
                        if($fileType!=0){
                            $myfiles = $em->getRepository('GedBundle:Gedfiles')->nameTypeSearch($searchRecherche, $idUser, $fileType);
                        }
                        //sinon
                        else{
                            $myfiles = $em->getRepository('GedBundle:Gedfiles')->nameSearch($searchRecherche, $idUser);
                        }

                        foreach ($myfiles as $myfile) {
                            
                            //nom du fichier
                            $name = $myfile->getOriginalName();
                            //son id
                            $id = $myfile->getId();
                            //son type
                            $type = $myfile->getType();
                            //son chemin
                            $path = $myfile->getPath();

                            //récuperation des favoris.
                            $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$id,
                                                                                                        'iduser'=>$idUser,
                                                                                                        )
                            );
                            if ($bookmark!=null){
                                $bookmark = 1;
                            }

                            else{
                                $bookmark = 0;
                            }

                            //on met tout dans un tableau
                            array_push($nameTab, array(
                                'name' => $name,
                                'id' => $id,
                                'type' => $type,
                                'path' => $path,
                                'bookmark' => $bookmark,
                            ));
                        }
                        $searchCount++;
                    }
                }

                //RECHERCHE POUR L' UTILISATEUR - Tags

                $searchCount=0;
                foreach ($tabSearch as $searchRecherche) {
                    if($searchCount<=4 && $searchRecherche!=''){
                
                        //on recherche tout les tag ayant la valeur
                        $allTag = $em->getRepository('GedBundle:Gedtag')->tagSearch($searchRecherche);

                        $linkTagTab=[];
                        foreach ($allTag as $tag) {
                            $idTags = $tag->getId();

                            $links = $em->getRepository('GedBundle:Linktag')->findByIdtag($idTags);
                            //si il a bien trouver un lien
                            if($links != null){
                                //pour chaque lien recupére Id du fichier corespondant
                                foreach ($links as $link) {
                                    $idFile = $link->getIdfile();

                                    //si linkyTagTab et defini
                                    if($linkTagTab!=null){
                                        //compare le tableau a la valeur de idfile
                                        //on definit un compteur pour n' avoir q'une seule ocurence.
                                        $countTag = 0;
                                        foreach ($linkTagTab as $test) {
                                            //si la valeur est diffente alors ajoute la au tableau.
                                            if(($test['idFile'] != $idFile)&& $countTag==0){

                                                $linkTagTab[]= array('idFile' =>$idFile,);

                                                $countTag++;
                                            }
                                        }                                
                                    }
                                    //sinon le definir
                                    else{
                                        $linkTagTab[]= array('idFile' =>$idFile,);
                                    }                            
                                }
                            }
                        }
                        //pour chaque lien d'id de fichier et de l'id utilisateur aller les chercher et remplir un tableau
                        foreach ($linkTagTab as $tagId) {
                            //si le type est defini
                            if ($fileType!=0){

                                foreach ($fileType as $defType){
                                    $tagFile = $em->getRepository('GedBundle:Gedfiles')->findOneBy(array(
                                                                                                    'id'=>$tagId['idFile'],
                                                                                                    'idowner'=>$idUser,
                                                                                                    'type'=>$defType,
                                                                                                ));
                                    if($tagFile!=null){
                                        // on recupere son id
                                        $id = $tagFile->getId();
                                        $count=0;
                                        if($nameTab!=null){
                                            //pourchaque entre dans le tableau myFileTab
                                            foreach ($nameTab as $myFile) {
                                                //on recupere id pour chaque entré du tableau
                                                $myFileId = $myFile['id'];

                                                //et on la compare a chaque fois avec id de tagFile.
                                                if($myFileId==$id){
                                                    $count++;
                                                }
                                            }
                                        }
                                        if($tagTab!=null){
                                            //pourchaque entre dans le tableau myFileTab
                                            foreach ($tagTab as $myFile) {
                                                //on recupere id pour chaque entré du tableau
                                                $myFileId = $myFile['id'];
                                                //et on la compare a chaque fois avec id de tagFile.
                                                if($myFileId==$id){
                                                    $count++;
                                                }
                                            }
                                        }
                                        if($count==0){
                                            //nom du fichier
                                            $name = $tagFile->getOriginalName();
                                            //son id
                                            $id = $tagFile->getId();
                                            //son type
                                            $type = $tagFile->getType();
                                            //son chemin
                                            $path = $tagFile->getPath();

                                            //récuperation des favoris.
                                            $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$id,
                                                                                                                        'iduser'=>$idUser,
                                                                                                                        )
                                            );
                                            if ($bookmark!=null){
                                                $bookmark = 1;
                                            }

                                            else{
                                                $bookmark = 0;
                                            }

                                            //on met tout dans un tableau
                                            array_push($tagTab, array(
                                                'name' => $name,
                                                'id' => $id,
                                                'type' => $type,
                                                'path' => $path,
                                                'bookmark' => $bookmark,
                                            ));
                                        }
                                    }                                                      
                                }
                            }
                            //sinon
                            if ($fileType==0) {
                                $tagFile = $em->getRepository('GedBundle:Gedfiles')->findOneBy(array(
                                                                                                        'id'=>$tagId['idFile'],
                                                                                                        'idowner'=>$idUser,
                                                                                                    ));
                                if($tagFile!=null){
                                    // on recupere son id
                                    $id = $tagFile->getId();
                                    $count=0;
                                    if($nameTab!=null){
                                        //pourchaque entre dans le tableau myFileTab
                                        foreach ($nameTab as $myFile) {
                                            //on recupere id pour chaque entré du tableau
                                            $myFileId = $myFile['id'];

                                            //et on la compare a chaque fois avec id de tagFile.
                                            if($myFileId==$id){
                                                $count++;
                                            }
                                        }
                                    }
                                    if($tagTab!=null){
                                        //pourchaque entre dans le tableau myFileTab
                                        foreach ($tagTab as $myFile) {
                                            //on recupere id pour chaque entré du tableau
                                            $myFileId = $myFile['id'];
                                            //et on la compare a chaque fois avec id de tagFile.
                                            if($myFileId==$id){
                                                $count++;
                                            }
                                        }
                                    }
                                    if($count==0){
                                        //nom du fichier
                                        $name = $tagFile->getOriginalName();
                                        //son id
                                        $id = $tagFile->getId();
                                        //son type
                                        $type = $tagFile->getType();
                                        //son chemin
                                        $path = $tagFile->getPath();

                                        //récuperation des favoris.
                                        $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$id,
                                                                                                                    'iduser'=>$idUser,
                                                                                                                    )
                                        );
                                        if ($bookmark!=null){
                                            $bookmark = 1;
                                        }

                                        else{
                                            $bookmark = 0;
                                        }

                                        //on met tout dans un tableau
                                        array_push($tagTab, array(
                                            'name' => $name,
                                            'id' => $id,
                                            'type' => $type,
                                            'path' => $path,
                                            'bookmark' => $bookmark,
                                        ));
                                    }
                                }                        
                            }                    
                        }
                    $searchCount++;
                    }
                }

                //RECHERCHE POUR LES GROUPES - Fichiers

                //on recherche dans quels groupe l'utilisateur est
                
                $linkGroups = $em->getRepository('GedBundle:Linkgroup')->findByIduser($idUser);
                
                //si on un resultat
                if($linkGroups!=null){
                    $groupTab = [];
                    //pour chaque resultat, ajout les dans un tableau
                    foreach ($linkGroups as $group) {
                        $idGrp = $group->getIdgroup();
                        array_push($groupTab, $idGrp);
                    }

                    //pour chaque group contenu dans mon tableau
                    foreach ($groupTab as $group) {
                        $searchCount=0;
                        foreach ($tabSearch as $searchRecherche) {
                            if($searchCount<=4 && $searchRecherche!=''){

                                if($fileType!=0){
                                    $groupFiles = $em->getRepository('GedBundle:Gedfiles')->grpNameTypeSearch($searchRecherche, $group,$fileType);
                                    if($groupFiles!=null){
                                        foreach ($groupFiles as $files) {
                                            $idGrpFile = $files->getId();
                                            $count = 0;
                                            
                                            //si mes ficher ne son pas vide
                                            if ($nameTab!=null) {
                                                //pour chaque ficher dans mes ficher 
                                                foreach ($nameTab as $fileTab) {
                                                    //si id de mon ficher est egale a id de ficher de mon groupe
                                                    if( ($fileTab['id'] == $idGrpFile) ){
                                                        $count ++;                                            
                                                    }
                                                }                                    
                                            }
                                            // si aucun Id n'a été trouver dans $nameTab ou si $nameTab nexista pas 
                                            if($count==0){
                                                //verification pour les tag 
                                                //si tagTab existe
                                                if (isset($tagTab)) {
                                                //pour chaque ficher dans mes ficher 
                                                    foreach ($tagTab as $fileTab) {
                                                        //si id de mon ficher est egale a id de ficher de mon groupe
                                                        if( ($fileTab['id'] == $idGrpFile) ){
                                                            $count ++;                                            
                                                        }
                                                    }                                                                        
                                                }
                                            }
                                            if($count==0){

                                                // verification si le grpNameTab n'est pas defini
                                                if($grpNameTab==null){

                                                    // nom du fichier
                                                    $name = $files->getOriginalName();
                                                    //son type
                                                    $type = $files->getType();
                                                    //son chemin
                                                    $path = $files->getPath();

                                                    //récuperation des favoris.

                                                    $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$idGrpFile,
                                                                                                                                'iduser'=>$idUser,
                                                                                                                                )
                                                    );

                                                    if ($bookmark!=null){
                                                        $bookmark = 1;
                                                    }

                                                    else{
                                                        $bookmark = 0;
                                                    }

                                                    //on met tout dans un tableau
                                                    array_push($grpNameTab, array(
                                                        'name' => $name,
                                                        'id' => $idGrpFile,
                                                        'type' => $type,
                                                        'path' => $path,
                                                        'bookmark' => $bookmark,
                                                    ));
                                                }
                                                //si le tableau est deja défini alors le parcourir et verifier
                                                if($grpNameTab!=null){
                                                    
                                                    foreach ($grpNameTab as $fileTab) {
                                                        //si id contenu dans mon tableau n est
                                                        if( ($fileTab['id'] == $idGrpFile) ){
                                                            $count ++;                                            
                                                        }
                                                    }
                                                    //si l id na pas ete trouver alors le rajouté
                                                    if($count==0){

                                                        // nom du fichier
                                                        $name = $files->getOriginalName();
                                                        //son type
                                                        $type = $files->getType();
                                                        //son chemin
                                                        $path = $files->getPath();

                                                        //récuperation des favoris.
                                                        $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$idGrpFile,
                                                                                                                                    'iduser'=>$idUser,
                                                                                                                                    )
                                                        );
                                                        if ($bookmark!=null){
                                                            $bookmark = 1;
                                                        }

                                                        else{
                                                            $bookmark = 0;
                                                        }

                                                        //on met tout dans un tableau
                                                        array_push($grpNameTab, array(
                                                            'name' => $name,
                                                            'id' => $idGrpFile,
                                                            'type' => $type,
                                                            'path' => $path,
                                                            'bookmark' => $bookmark,
                                                        ));                                            
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                else{
                                    $groupFiles = $em->getRepository('GedBundle:Gedfiles')->grpNameSearch($searchRecherche, $group);

                                    if($groupFiles!=null){
                                        foreach ($groupFiles as $files) {
                                            $idGrpFile = $files->getId();
                                            $count = 0;
                                            
                                            //si mes ficher ne son pas vide
                                            if ($nameTab!=null) {
                                                //pour chaque ficher dans mes ficher 
                                                foreach ($nameTab as $fileTab) {
                                                    //si id de mon ficher est egale a id de ficher de mon groupe
                                                    if( ($fileTab['id'] == $idGrpFile) ){
                                                        $count ++;                                            
                                                    }
                                                }                                    
                                            }
                                            // si aucun Id n'a été trouver dans $nameTab ou si $nameTab nexista pas 
                                            if($count==0){
                                                //verification pour les tag 
                                                //si tagTab existe
                                                if (isset($tagTab)) {
                                                //pour chaque ficher dans mes ficher 
                                                    foreach ($tagTab as $fileTab) {
                                                        //si id de mon ficher est egale a id de ficher de mon groupe
                                                        if( ($fileTab['id'] == $idGrpFile) ){
                                                            $count ++;                                            
                                                        }
                                                    }                                                                        
                                                }
                                            }
                                            if($count==0){

                                                // verification si le grpNameTab n'est pas defini
                                                if($grpNameTab==null){

                                                    // nom du fichier
                                                    $name = $files->getOriginalName();
                                                    //son type
                                                    $type = $files->getType();
                                                    //son chemin
                                                    $path = $files->getPath();

                                                    //récuperation des favoris.

                                                    $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$idGrpFile,
                                                                                                                                'iduser'=>$idUser,
                                                                                                                                )
                                                    );

                                                    if ($bookmark!=null){
                                                        $bookmark = 1;
                                                    }

                                                    else{
                                                        $bookmark = 0;
                                                    }

                                                    //on met tout dans un tableau
                                                    array_push($grpNameTab, array(
                                                        'name' => $name,
                                                        'id' => $idGrpFile,
                                                        'type' => $type,
                                                        'path' => $path,
                                                        'bookmark' => $bookmark,
                                                    ));
                                                }
                                                //si le tableau est deja défini alors le parcourir et verifier
                                                if($grpNameTab!=null){
                                                    
                                                    foreach ($grpNameTab as $fileTab) {
                                                        //si id contenu dans mon tableau n est
                                                        if( ($fileTab['id'] == $idGrpFile) ){
                                                            $count ++;                                            
                                                        }
                                                    }
                                                    //si l id na pas ete trouver alors le rajouté
                                                    if($count==0){

                                                        // nom du fichier
                                                        $name = $files->getOriginalName();
                                                        //son type
                                                        $type = $files->getType();
                                                        //son chemin
                                                        $path = $files->getPath();

                                                        //récuperation des favoris.
                                                        $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$idGrpFile,
                                                                                                                                    'iduser'=>$idUser,
                                                                                                                                    )
                                                        );
                                                        if ($bookmark!=null){
                                                            $bookmark = 1;
                                                        }

                                                        else{
                                                            $bookmark = 0;
                                                        }

                                                        //on met tout dans un tableau
                                                        array_push($grpNameTab, array(
                                                            'name' => $name,
                                                            'id' => $idGrpFile,
                                                            'type' => $type,
                                                            'path' => $path,
                                                            'bookmark' => $bookmark,
                                                        ));                                            
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $searchCount++;
                        }
                    }
                }
                //RECHERCHE POUR LES GROUPES - tags

                //on recherche dans quels groupe l'utilisateur est
                $linkGroups = $em->getRepository('GedBundle:Linkgroup')->findByIduser($idUser);
                $grpTagTab = [];
                //si on un resultat
                if($linkGroups!=null){
                    $groupsTab = [];
                    //pour chaque resultat, ajout les dans un tableau
                    foreach ($linkGroups as $group) {
                        // var_dump($groupTab);
                        $idGrp = $group->getIdgroup();
                        array_push($groupsTab, $idGrp);
                    }
                    //on garde donc le tableau des group de l' utilisateur pour une comparaison plus tard
                    $searchCount=0;
                    foreach ($tabSearch as $searchRecherche) {
                        if($searchCount<=4 && $searchRecherche!=''){
                            //on recherhce dans tout les mots clef une corespondance.
                            $tags = $em->getRepository('GedBundle:Gedtag')->tagSearch($searchRecherche);

                            //pour chaque tag trouver
                            foreach ($tags as $tag) {
                                //on recupere l id du tag
                                $idTag = $tag->getId();

                                //on recherche ses differents lien avec les fichers
                                $linksTag = $em->getRepository('GedBundle:Linktag')->findByIdtag($idTag);

                                //si il y a bien des lien pour le tag
                                if($linksTag!=null){

                                    //pour chaque lien trouvé
                                    foreach ($linksTag as $linkTag) {
                                        //on récupere l' id du fichier.
                                        $id = $linkTag->getIdfile();

                                        // puis on recherche le fichier.
                                        //pour chaqu' un  de mes groupes
                                        foreach ($groupsTab as $group) {
                                            if ($fileType!=0){
                                                foreach ($fileType as $defType){
                                                    //recherche du fichier correspondant Id du fichier et Id du groupe
                                                    $grpFileIdSearch = $em->getRepository('GedBundle:Gedfiles')->findOneBy( array(
                                                        'idgroup' => $group,
                                                        'id' =>$id,
                                                        'type'=>$defType,
                                                        )
                                                    );
                                                    //si il trouve un resultat alors asigne lui la valeur.
                                                    if($grpFileIdSearch!=null){
                                                        $grpFileId=$grpFileIdSearch;
                                                    }
                                                }
                                            }
                                            else{
                                                //recherche du fichier correspondant Id du fichier et Id du groupe
                                                $grpFileId = $em->getRepository('GedBundle:Gedfiles')->findOneBy( array(
                                                    'idgroup' => $group,
                                                    'id' =>$id,
                                                    )
                                                );
                                            }
                                            //si la verible nest pas defini.
                                            if(!isset($grpFileId)){
                                                $grpFileId=null;
                                            }
                                            //le resultat n'est pas null
                                            if($grpFileId!=null){
                                                //on initialise un compeur
                                                $count = 0;
                                                //on recupere id du fichier trouvé
                                                $idFile = $grpFileId->getId();

                                                //on a maintennant le fichier correspondant il manque plus a le trier.
                                                
                                                //si nameTab est defini
                                                if($nameTab!=null){
                                                    foreach ($nameTab as $fileTab) {
                                                        //si id contenu dans mon tableau correspon a l' id de mon fichier
                                                        if( ($fileTab['id'] == $idFile) ){
                                                            //incremente le compteur
                                                            $count ++;                                            
                                                        }
                                                    }
                                                }                                        
                                                //si tagTab est defini
                                                if($tagTab!=null){
                                                    foreach ($tagTab as $fileTab) {
                                                        //si id contenu dans mon tableau correspon a l' id de mon fichier
                                                        if( ($fileTab['id'] == $idFile) ){
                                                            //incremente le compteur
                                                            $count ++;                                            
                                                        }
                                                    }
                                                }
                                                //si grpNameTab est defini
                                                if($grpNameTab!=null){
                                                    foreach ($grpNameTab as $fileTab) {
                                                        //si id contenu dans mon tableau correspon a l' id de mon fichier
                                                        if( ($fileTab['id'] == $idFile) ){
                                                            //incremente le compteur
                                                            $count ++;                                            
                                                        }
                                                    }
                                                }
                                                //si grpTagTab est defini
                                                if($grpTagTab!=null){
                                                    foreach ($grpTagTab as $fileTab) {
                                                        //si id contenu dans mon tableau correspon a l' id de mon fichier
                                                        if( ($fileTab['id'] == $idFile) ){
                                                            //incremente le compteur
                                                            $count ++;                                            
                                                        }
                                                    }                                                
                                                }
                                                //si le compeur est a 0
                                                if($count==0){
                                                    //C'est que le ficher n' est present nul part !
                                                    // nom du fichier
                                                    $name = $grpFileId->getOriginalName();
                                                    //son type
                                                    $type = $grpFileId->getType();
                                                    //son chemin
                                                    $path = $grpFileId->getPath();

                                                    //récuperation des favoris.
                                                    $bookmark = $em->getRepository('GedBundle:Linkbookmark')->findOneBy( array( 'idfile'=>$idFile,
                                                                                                                                'iduser'=>$idUser,
                                                                                                                                )
                                                    );
                                                    if ($bookmark!=null){
                                                        $bookmark = 1;
                                                    }

                                                    else{
                                                        $bookmark = 0;
                                                    }

                                                    //on met tout dans un tableau
                                                    array_push($grpTagTab, array(
                                                        'name' => $name,
                                                        'id' => $idFile,
                                                        'type' => $type,
                                                        'path' => $path,
                                                        'bookmark' => $bookmark,
                                                    ));                                                                                        
                                                }
                                            }                                                                     
                                        }
                                    }
                                }
                            }
                        }
                    $searchCount++;
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
        if( ($nameTab==null) && ($tagTab==null) && ($grpNameTab==null) && ($grpTagTab==null) ){
            $this->get('session')->getFlashBag()->set('error', 'Aucun résultat');
        }
        if(strlen($search)==0){
            $this->get('session')->getFlashBag()->set('error', 'Merci de définir votre recherche');
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