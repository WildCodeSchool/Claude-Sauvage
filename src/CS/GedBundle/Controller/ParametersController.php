<?php

namespace CS\GedBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
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
}
