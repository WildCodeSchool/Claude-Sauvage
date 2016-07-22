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

class ParametersController extends Controller
{
    /**
     * @Route("/parameters/{id_file}", name="ged_parameters_file")
     */
    public function ParametersAction(Request $request, $id)
    {
		$em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $category= $em->getRepository('GedBundle:Category')->findAll();
        $souscategory=$em->getRepository('GedBundle:Souscategory')->findAll();
        
        if(empty($category))
        {
            $category=0;
        }
       if(empty($souscategory))
        {
            $souscategory=0;
        }

        $file = $em->getRepository('GedBundle:Gedfiles')->findOneById($id);
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


        //ajout de categories
        $addcat= $request->request->get('addcat');
        $addsscat= $request->request->get('addsscat');
        
        if(!empty($addcat) && $addcat != 0)
        {
            $newcategory=$em->getRepository('GedBundle:Gedfiles')->findOneById($id);
            $newcategory->setIdcategory($addcat);
            $em->persist($newcategory);
            $em->flush();
        }
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
        ));
    }

    public function removeTagAction (Request $request)
    {
        var_dump($request);
        $em=$this->getDoctrine()->getManager();

        $idtag=$request->request->get('idtag');
        
        $linktag=$em->getRepository('GedBundle:Linktag')->findOneById($idtag);
        
        $em->remove($linktag);
        $em->flush();
    }
    public function addTagAction (Request $request)
    {
        var_dump($request);
        $em=$this->getDoctrine()->getManager();
        $addtag=$request->request->get('content');
        $user=$this->getUser();
        $id = $request->request->get('id');
        $created= 0;
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
        if(count($tabtag)<3 && !empty($addtag))
        {
            $existingtags=$em->getRepository('GedBundle:Gedtag')->findAll();
            foreach ($existingtags as $existingtag)
            {
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
                        $newlinktag = new Linktag;
                        $newlinktag ->setIdfile($id);
                        $newlinktag->setIdtag($existingtag->getId());
                        $em->persist($newlinktag);
                        $em->flush();
                        $created=1;
                    }
                }
            }
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
            $test=$addtag;
        }
        elseif(count($tabtag) >= 3 && !empty($addtag))
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
            $test=$addtag;
        }
        else
        {
            $test=1;
        }
        var_dump($addtag);
        $response = new JsonResponse();
        return $response->setData(array('test' => $test));

    }
}
