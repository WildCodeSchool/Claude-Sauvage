<?php

namespace CS\GedBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use CS\GedBundle\Entity\Gedfiles;
use CS\GedBundle\Form\GedfilesType;
use AppBundle\Entity\User;
use CS\GedBundle\Entity\Linkbookmark;
use CS\GedBundle\Entity\Category;
use CS\GedBundle\Entity\Souscategory;
use CS\GedBundle\Entity\Linktag;
use CS\GedBundle\Entity\Gedtag;
use DateTime;

class BookmarkController extends Controller
{
    /**
     * @Route("/bookmark", name="ged_bookmark")
     */
    public function bookmarkAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser()->getId();

        $fav = $request->request->get('fav');

        $verifFav = $em->getRepository('GedBundle:Linkbookmark')->findOneBy(array(
        																		'idfile'=>$fav,
        																		'iduser'=>$user,
        																		)
        																	);


       if ($verifFav==null){
	        $newfav= new Linkbookmark();
	        $newfav->setIdfile($fav);
	        $newfav->setIduser($user);

	        $em->persist($newfav);
	        $em->flush();
        }
        
        else{
        	$em->remove($verifFav);
        	$em->flush();
        }
        
        $response = new JsonResponse();

        return $response->setData(array('response' => $verifFav));
    }
    public function bookmarkListAction(Request $request)
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

        //on recupere tous les fichiers mis en fav par l'user
        $listfavs=$em->getRepository('GedBundle:Linkbookmark')->findBy(  
            array('iduser' => $iduser), // Critere
            array('id' => 'desc')       // Tri
        );

        foreach ($listfavs as $onefav ) {
            //on assigne à fav la ligne du fichier dans gedfiles
            $idfav=$onefav->getIdfile();    
            $fav = $em->getRepository('GedBundle:Gedfiles')->findOneById($idfav);

            //trouver le nom du fichier
            $path=$fav->getPath();

            //trouver le type du fichier
            $type=$fav->getType();
            $date=$fav->getDate();
            $name=$fav->getOriginalName();

            $category=$em->getRepository('GedBundle:Category')->findOneById($fav->getIdcategory())->getName();

            //recuperationc des membres du groupe par fichier.
            $groupMembers = $em->getRepository('GedBundle:Linkgroup')->findByIdgroup($fav->getIdgroup());
            //pour chaque menbres dans le groupe, on recupere le nom.
            $tabInfoGroup=[];
            foreach ($groupMembers as $groupMember) {
                $groupMemberId = $groupMember->getIduser();
                $groupMemberInfo = $em->getRepository('AppBundle:User')->findOneById($groupMemberId);
                $groupMemberName = $groupMemberInfo->getUsername();

                $tabInfoGroup[] = array(
                        'groupMemberName'=>$groupMemberName,
                );              
            }

            //on compte les commentaires liés a un fichier.
            $comments =$em->getRepository('GedBundle:Gedcom')->findByIdfile($idfav);
            if (empty($comments)){
                    $nbCom = 0;
                }
            else {
                $nbCom = count($comments);
            }

            //on recupere tous les tags correspondants au fichier
            $linktag = $em->getRepository('GedBundle:Linktag')->findByIdfile($idfav);
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

            if (empty($tabInfoGroup)){
                $tabInfoGroup = 1;
            }

            $tabfav[]=array(
                "idfile"=>$idfav,
                "tagnames"=>$tagnames,
                "path"=>$path,
                "type"=>$type,
                "category"=>$category,
                "date"=>$date,
                "name"=>$name,
                'comments'=>$nbCom,
                'groupMemberName'=>$tabInfoGroup,
                );
        }
        if(empty($tabfav))
        {
            $tabfav=1;
        }

        return $this->render('GedBundle::bookmark.html.twig',array(
                                                                    'categories' => $categories,
                                                                    'categoryTab'=> $categoryTab,
                                                                    'form' => $form->createView(),
                                                                    'user'=>$user,
                                                                    'tabfav'=>$tabfav,
                                                                ));
    }
}