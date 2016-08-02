<?php

namespace CS\GedBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use CS\GedBundle\Entity\Gedfiles;
use CS\GedBundle\Form\GedfilesType;
use CS\GedBundle\Entity\Gedtag;
use CS\GedBundle\Entity\Linktag;
use CS\GedBundle\Entity\Category;
use CS\GedBundle\Entity\Souscategory;
use DateTime;

//controller gérant les paramètres d'un fichier (tags/catégories/sous-catégories)
class ParametersController extends Controller
{
    /**
     * @Route("/parameters/{id_file}", name="ged_parameters_file")
     */
    // fonction d'affichage de la page de paramètres
    public function ParametersAction(Request $request, $id)
    {
        //récupération de l'entity manager et de l'utilisateur courant
		$em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $file = $em->getRepository('GedBundle:Gedfiles')->findOneById($id);
        if ($user->getId()==$file->getIdowner())
        {
            //recupération des categories et sous categories 
            $category= $em->getRepository('GedBundle:Category')->findAll();
            $souscategory=$em->getRepository('GedBundle:Souscategory')->findAll();
            
            $linktags = $em->getRepository('GedBundle:Linktag')->findByIdfile($id);
            //compte des tags du fichier
            foreach ($linktags as $linktag)
            {
                $tag=$em->getRepository('GedBundle:Gedtag')->findOneById($linktag->getIdtag());
                $name=$tag->getName();
                $tabtag[]=array(
                    'name'=>$name,
                    'idlinktag'=>$linktag->getId(),
                    );
            }
            if (empty($tabtag)) {
                $tabtag=1;
            }
            if(empty($category))
            {
                $category=0;
            }
           if(empty($souscategory))
            {
                $souscategory=0;
            }
            //recupération du fichier et des tags
            $file = $em->getRepository('GedBundle:Gedfiles')->findOneById($id);
            $filename=$file->getOriginalname();
            $linktags = $em->getRepository('GedBundle:Linktag')->findByIdfile($id);
            //compte des tags du fichier
            //ajout de categories (recupération des champs contenant les noms)
            $addcat= $request->request->get('addcat');
            $addsscat= $request->request->get('addsscat');
            
            //ajout de categorie
            if(!empty($addcat) && $addcat != 0)
            {
                $newcategory=$em->getRepository('GedBundle:Gedfiles')->findOneById($id);
                $newcategory->setIdcategory($addcat);
                $em->persist($newcategory);
                $em->flush();
            }
            //ajout de sous categorie
            if(!empty($addsscat) && $addsscat != 0)
            {
                $newsscategory=$em->getRepository('GedBundle:Gedfiles')->findOneById($id);
                $newsscategory->setIdsouscategory($addsscat);
                $em->persist($newsscategory);
                $em->flush();
            }

            //fonction d'upload
            $gedfiles = new Gedfiles();
            $form = $this->createForm(GedfilesType::class, $gedfiles);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                
                $file = $gedfiles->getPath();
                
                $type = $file->guessExtension();

                $fileName = md5(uniqid()).'.'.$file->guessExtension();

                $pathDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads';

                if($type==null){
                    $type = 'txt';
                }
                
                $file->move($pathDir, $fileName);

                $gedfiles->setType($type);
                $gedfiles->setPath($fileName);
                $gedfiles->setIdowner($user->getId());
                $gedfiles->setIdCategory(1);
                $gedfiles->setDate( new DateTime());

                $em->persist($gedfiles);
                $em->flush();

                $this->get('session')->getFlashBag()->set('success', 'Fichier envoyé');

                return $this->redirectToRoute('ged_homepage');
                }

            return $this->render('GedBundle::parameters.html.twig', array(
                'form' => $form->createView(),
                'user'=>$user,
                'id'=>$id,
                'categories'=>$category,
                'souscategories'=>$souscategory,
                'tabtag'=>$tabtag,
                'filename'=>$filename,
            ));
        }
        else
        {
            $url = $this -> generateUrl('ged_homepage');
            $response = new RedirectResponse($url);
            return $response;
        }
    }
    //fonction de suppression de tags (ajax)
    public function removeTagAction (Request $request)
    {
        //récupération de l'entity manager et de l'utilisateur courant
        $em=$this->getDoctrine()->getManager();
        //recuperation de l'id tag
        $idtag=$request->request->get('idtag');
        //suppression du lien tag/fichier
        $linktag=$em->getRepository('GedBundle:Linktag')->findOneById($idtag);
        
        $em->remove($linktag);
        $em->flush();
    }
    //fonction d'ajout de tag (ajax)
    public function addTagAction (Request $request)
    {
        //récupération de l'entity manager et de l'utilisateur courant
        $em=$this->getDoctrine()->getManager();
        $user=$this->getUser();
        //recuperation de l'id file et du nom du tag
        $id = $request->request->get('idfile');
        $addtag=$request->request->get('content');
        $created= 0;
        $done = 0;
        //ajout de tags
        $linktags = $em->getRepository('GedBundle:Linktag')->findByIdfile($id);
        //compte des tags du fichier
        foreach ($linktags as $linktag)
        {
            $tag=$em->getRepository('GedBundle:Gedtag')->findOneById($linktag->getIdtag());
            $name=$tag->getName();
            $tabtag[]=array(
                'name'=>$name,
                'idlinktag'=>$linktag->getId(),
                );
        }

        if(empty($tabtag))
        {
            $tabtag=0;
        }
        //s'il existe moins de 3 tags et que l'entrée et correcte
        if(count($tabtag)<3 && !empty($addtag))
        {
            //on recupère les tags du fichier
            $existingtags=$em->getRepository('GedBundle:Gedtag')->findAll();
            foreach ($existingtags as $existingtag)
            {
                //si le tag existe déja sur ce fichier
                if($addtag == $existingtag->getName())
                {
                    $link=$em->getRepository('GedBundle:Linktag')->findOneByIdtag($existingtag->getId());
                    if( !empty($link) && $link->getIdfile() == $id )
                    {
                        echo "le tag a déjà été assigné à ce fichier";
                        $created=1;
                    }
                    else
                    {
                        //sinon on crée le tag
                        $newlinktag = new Linktag();
                        $newlinktag ->setIdfile($id);
                        $newlinktag->setIdtag($existingtag->getId());

                        $em->persist($newlinktag);
                        $em->flush();
                        $created=1;
                    }
                }
            }
            //si le tag n'existe pas du tout on le crée et on fait aussi le lien tag/fichier
            if( $created == 0 )
            {
                $newtag = new Gedtag;
                $newtag->setName($addtag);
                $em->persist($newtag);
                $em->flush();
                
                $newlinktag = new Linktag;
                $newlinktag ->setIdfile($id);
                $newlinktag->setIdtag($newtag->getId());
                $em->persist($newlinktag);
                $em->flush();
            }
        }
        elseif(count($tabtag) >= 3 && !empty($addtag))
        {
            //si le nombre de tags est supérieur ou egal a 3 et que l'entrée est correcte
            //on recupere les tags existants 
            $existingtags=$em->getRepository('GedBundle:Gedtag')->findAll();
            foreach ($existingtags as $existingtag)
            {
                //on remplace le premier tag du fichier par le nouveau tag si le tag existe déjà en bdd on recrée juste le lien tag/file
                if($done == 0)
                {
                    if($addtag == $existingtag->getName())
                    {
                        $replacelinktag=$em->getRepository('GedBundle:Linktag')->findOneByIdfile($id); 
                        $em->remove($replacelinktag);
                        $em->flush();

                        $replacelinktag = new Linktag;
                        $replacelinktag->setIdfile($id);
                        $replacelinktag->setIdtag($newtag->getId());
                        $em->persist($replacelinktag);
                        $em->flush();
                        $done = 1;
                    }
                }
            }
            // si le tag n'existait pas on le crée totalement (tag et lien)
            if($done == 0)
            {
                $newtag = new Gedtag;
                $newtag->setName($addtag);
                $em->persist($newtag);
                $em->flush();
                $replacelinktag=$em->getRepository('GedBundle:Linktag')->findOneByIdfile($id); 
                $em->remove($replacelinktag);
                $em->flush();

                $replacelinktag = new Linktag;
                $replacelinktag->setIdfile($id);
                $replacelinktag->setIdtag($newtag->getId());
                $em->persist($replacelinktag);
                $em->flush();
                $done = 1;
            }
        }

        //si l'entrée n'est pas correcte        
        if(!empty($addtag))
        {
            $tabtag = [];
            $tag=$em->getRepository('GedBundle:Gedtag')->findOneByName($addtag);
            $linktag=$em->getRepository('GedBundle:Linktag')->findOneByIdtag($tag->getId());
            $tabtag=array(
                'name'=>$tag->getName(),
                'idtag'=>$linktag->getId()
                );
        }
        else
        {
            $tabtag=1;
        }
        //envoi de reponse pour l'ajax
        $response = new JsonResponse();
        return $response->setData(array('tabtag' => $tabtag));

    }

    //autocompletion tag (ajax)
    public function autocompletionAction(Request $request)
    {
        $em=$this->getDoctrine()->getManager();
        $user =$this->getUser()->getId();

        $searchRecherche=$request->request->get('recherche');

        //RECHERCHE POUR L' UTILISATEUR - Fichiers

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

        if(!isset($tagTab)){
            $tagTab=[];
        }
        
        if(!isset($grpTagTab)){
            $grpTagTab=[];
        }

        $response = new JsonResponse();
        
        return $response->setData(array('tagTab' => $tagTab, 'grpTagTab' => $grpTagTab,));
    }
}
