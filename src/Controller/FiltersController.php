<?php

namespace WPierre\Scafo\ScafoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use WPierre\Scafo\ScafoBundle\Entity\Filter;
use WPierre\Scafo\ScafoBundle\Classes\ExamplesFilters;

class FiltersController extends Controller
{
    public function listAction($instance)
    {
        
        $datas = Array();
        $datas['instance'] = $this->get('doctrine')->getRepository('WpierreScafoScafoBundle:ConfigInstance')->findOneById($instance);
        $datas['filters'] = $this->get('doctrine')->getManager()->getRepository('WpierreScafoScafoBundle:Filter')->getByInstanceOrdered($instance);
        return $this->render('WpierreScafoScafoBundle:Filters:list.html.twig', $datas);
    }
    
    public function createAction($id_instance)
    {
        $request = Request::createFromGlobals();
        $datas = Array();
        $instance = $this->get('doctrine')->getRepository('WpierreScafoScafoBundle:ConfigInstance')->findOneById($id_instance);
        //Récupération des filtres classés par ordre pour gérer une liste
        $filtres = $this->get('doctrine')->getManager()->getRepository('WpierreScafoScafoBundle:Filter')->getByInstanceOrdered($instance);
        $liste_filtres = array();
        $compteur = 0;
        $max_id = 0;
        foreach ($filtres as $filtre){
            //echo $filtre->getTitle()."<br />";
                if ($compteur == 0){
                    $liste_filtres[$filtre->getOrderNumber()] = "(Premier) ".$filtre->getTitle();
                } else {
                    $liste_filtres[$filtre->getOrderNumber()] = $filtre->getTitle();
                }
            $compteur++;
            if ($max_id < $filtre->getOrderNumber()){
                $max_id = $filtre->getOrderNumber();
            }
        }
        $liste_filtres['last'] = "Dernière position";
        //Création du formulaire
        $filter = new Filter();
        $filter->setInstance($instance);
        $form = $this->createFormBuilder($filter)
                ->setMethod('POST')
                ->add('title','text',array('label'=>'Titre'))
                ->add('orderNumber','choice',array('label'=>'Déplacer le filtre à la position','data'=>'last','choices'=>$liste_filtres))
                ->add('conditionType','choice',array('label'=>'Type de condition','data'=>1, 'expanded'=>true,'choices'=>array(1=>'Recherche par mots individuels',2=>'Syntaxe Google',3=>'Expression régulière')))
                ->add('conditionText','text',array('label'=>'Texte à rechercher'))
                ->add('path','text',array('label'=>'Chemin de stockage'))
                ->add('filenameFormater','text',array('label'=>'Masque du nom de fichier'))
                ->add('DocDateFormater','text',array('label'=>'Texte d\'extraction de date'))
                ->add('save','submit',array('label'=>'Enregistrer'))
                ->getForm();
        
        
        $form->handleRequest($request);
        
        if ($form->isValid()){

            $filter->setOrderNumber($this->get('doctrine')->getManager()->getRepository('WpierreScafoScafoBundle:Filter')->makeRoomForNewFilterPosition($form->getData()->getOrderNumber(),$instance->getId()));
            $em = $this->getDoctrine()->getManager();
            $em->persist($filter);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Le filtre "'.$filter->getTitle().'" a bien été créé');
            return $this->redirect($this->generateUrl('wpierre_scafo_scafo_filters',array('instance'=>$instance->getId())));
        }
        $datas['instance'] = $instance;
        $datas['form'] = $form->createView();
        return $this->render('WpierreScafoScafoBundle:Filters:create.html.twig', $datas);
    }
    
    public function editAction($id_filtre)
    {
        $request = Request::createFromGlobals();
        //echo "le filtre trouvé a l id :".$id_filtre;
        $datas = Array();
        
        $datas['filter'] = $this->get('doctrine')->getManager()->getRepository('WpierreScafoScafoBundle:Filter')->findOneById($id_filtre);
        //sauvegarde du filtre dans une autre variable pour comparer l'ordernumber à la soumission
        $order_original = $datas['filter']->getOrderNumber();
        $datas['instance'] = $datas['filter']->getInstance();
        
        
        
        //Récupération des filtres classés par ordre pour gérer une liste
        $filtres = $this->get('doctrine')->getManager()->getRepository('WpierreScafoScafoBundle:Filter')->getByInstanceOrdered($datas['instance']->getId());
        $liste_filtres = array();
        $compteur = 0;
        $max_id = 0;
        foreach ($filtres as $filtre){
            //echo $filtre->getTitle()."<br />";
            if ($filtre == $datas['filter']){
                $liste_filtres[$filtre->getOrderNumber()] = "Position actuelle";
            } else {
                if ($compteur == 0){
                    $liste_filtres[$filtre->getOrderNumber()] = "(Premier) ".$filtre->getTitle();
                } else {
                    $liste_filtres[$filtre->getOrderNumber()] = $filtre->getTitle();
                }
            }
            $compteur++;
            if ($max_id < $filtre->getOrderNumber()){
                $max_id = $filtre->getOrderNumber();
            }
        }
        $liste_filtres['last'] = "Dernière position";
        
       
        //Création du formulaire
        $form = $this->createFormBuilder($datas['filter'])
                ->setMethod('POST')
                ->add('title','text',array('label'=>'Titre'))
                ->add('orderNumber','choice',array('label'=>'Déplacer le filtre à la position','choices'=>$liste_filtres))
                ->add('conditionType','choice',array('label'=>'Type de condition','expanded'=>true,'choices'=>array(1=>'Recherche par mots individuels',2=>'Syntaxe Google',3=>'Expression régulière')))
                ->add('conditionText','text',array('label'=>'Texte à rechercher'))
                ->add('path','text',array('label'=>'Chemin de stockage'))
                ->add('filenameFormater','text',array('label'=>'Masque du nom de fichier'))
                ->add('DocDateFormater','text',array('label'=>'Texte d\'extraction de date'))
                ->add('save','submit',array('label'=>'Enregistrer'))
                ->add('cancel','submit',array('label'=>'Annuler'))
                ->getForm();
        
        
        $form->handleRequest($request);
        
        if ($form->isValid()){
        	if ($form->get('save')->isClicked()){
	            //echo "le nouveau order number est : *".$form->getData()->getOrderNumber()."*";
	            //echo "l'ancien order number est : *".$order_original."*";
	            //Si la position du filtre a changé, on lui fait de la place
	            if ($form->getData()->getOrderNumber() != $order_original){
	                $this->get('doctrine')->getManager()->getRepository('WpierreScafoScafoBundle:Filter')->makeRoomForNewFilterPosition($form->getData()->getOrderNumber(),$datas['instance']->getId());
	            }
	            $em = $this->getDoctrine()->getManager();
	            $em->persist($datas['filter']);
	            $em->flush();
	            $this->get('session')->getFlashBag()->add('success', 'Les changements ont été enregistrés');
        	}
        	return $this->redirect($this->generateUrl('wpierre_scafo_scafo_filters',array("instance"=>$datas['instance']->getId())));
        }
        $datas['form'] = $form->createView();
        return $this->render('WpierreScafoScafoBundle:Filters:edit.html.twig', $datas);
    }
    
    public function deleteAction($id_filtre)
    {
        $request = Request::createFromGlobals();
        //echo "le filtre trouvé a l id :".$id_filtre;
        $datas = Array();
        
        $datas['filter'] = $this->get('doctrine')->getManager()->getRepository('WpierreScafoScafoBundle:Filter')->findOneById($id_filtre);
        $datas['instance'] = $datas['filter']->getInstance();
        //Création du formulaire
        $form = $this->createFormBuilder($datas['filter'])
                ->setMethod('POST')
                ->add('delete','submit',array('label'=>'Supprimer le filtre'))
                ->add('cancel','submit',array('label'=>'Annuler'))
                ->getForm();
        $form->handleRequest($request);
        
        if ($form->isValid()){
        	if ($form->get('delete')->isClicked()){
	            $em = $this->getDoctrine()->getManager();
	            $em->remove($datas['filter']);
	            $em->flush();
	            $this->get('session')->getFlashBag()->add('success', 'Les changements ont été enregistrés');
        	}
            return $this->redirect($this->generateUrl('wpierre_scafo_scafo_filters',array('instance'=>$datas['instance']->getId())));
        }
        $datas['form'] = $form->createView();
        return $this->render('WpierreScafoScafoBundle:Filters:delete.html.twig', $datas);

    }
    
    public function importAction($id_instance)
    {
    	$request = Request::createFromGlobals();
    	//echo "le filtre trouvé a l id :".$id_filtre;
    	$datas = Array();
    
    	$datas['instance'] = $this->get('doctrine')->getRepository('WpierreScafoScafoBundle:ConfigInstance')->findOneById($id_instance);
    
    
    
    	$examplesFilters = new ExamplesFilters();
        
        $filtres = $examplesFilters->getFilters();
        

        $liste_filtres = Array();
        foreach($filtres as $key=>$filtre){
        	$liste_filtres[$key] = $filtre['libelle'];
        }
        
        //Création du formulaire
        $form = $this->createFormBuilder()
                ->setMethod('POST')
                ->add('to_import', 'choice',array(
                			"label" => "Filtres à importer",
                			"expanded" => true,
                			"multiple" => true,
                			"choices" => $liste_filtres,
                			"mapped" => false		
                		)
                )
                ->add('save','submit',array('label'=>'Enregistrer'))
                ->add('cancel','submit',array('label'=>'Annuler'))
                ->getForm();
    	
    
    	$form->handleRequest($request);
    
    	if ($form->isValid()){
    		if ($form->get('save')->isClicked()){
    			
    			$em = $this->getDoctrine()->getManager();
    			
    			$imports_a_faire = $form->get('to_import')->getData();
    			var_dump($imports_a_faire);
    			
    			foreach($imports_a_faire as $import){
    				$filter = $examplesFilters->getFilterById($import);
    				$filter->setInstance($datas['instance']);
    				$filter->setOrderNumber($this->get('doctrine')->getManager()->getRepository('WpierreScafoScafoBundle:Filter')->makeRoomForNewFilterPosition("last",$datas['instance']->getId()));
    				$em->persist($filter);
    				$em->flush();
    				$this->get('session')->getFlashBag()->add('success', 'Le filtre "'.$filter->getTitle().'" a bien été importé.');
    			}
    			
    		}
    		return $this->redirect($this->generateUrl('wpierre_scafo_scafo_filters',array("instance"=>$datas['instance']->getId())));
    	}
    	$datas['form'] = $form->createView();
    	return $this->render('WpierreScafoScafoBundle:Filters:import.html.twig', $datas);
    }
    
}
