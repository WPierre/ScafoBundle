<?php

namespace Wpierre\Scafo\ScafoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
            Wpierre\Scafo\ScafoBundle\Classes\BatchOperations,
            Wpierre\Scafo\ScafoBundle\Classes\FilesOperations,
            Symfony\Component\HttpFoundation\JsonResponse,
			Symfony\Component\HttpFoundation\Request,
			Wpierre\Scafo\ScafoBundle\Entity\ConfigInstance;
use Wpierre\Scafo\ScafoBundle\Entity\Parameter;

class InstancesController extends Controller
{
    public function indexAction($id_instance)
    {
        $datas = Array();
        $datas['instance'] = $this->get('doctrine')->getRepository('WpierreScafoScafoBundle:ConfigInstance')->findOneById($id_instance);
        return $this->render('WpierreScafoScafoBundle:Instances:index.html.twig', $datas);
    }
    
    public function runAction($action,$id_instance){
        $datas = Array();
        $datas['action'] = $action;
        $datas['instance'] = $this->get('doctrine')->getRepository('WpierreScafoScafoBundle:ConfigInstance')->findOneById($id_instance);
        $batchOperations = new BatchOperations($datas['instance']->getId(), $this);
        //TODO : Enlever le debug
        $batchOperations->setDebug(true);
        $datas['output'] = str_replace("\n", "<br />", $batchOperations->executeCommand($action));
        return $this->render('WpierreScafoScafoBundle:Instances:run.html.twig', $datas);

    }
    
    public function getRemainingFilesAction($id_instance){
        $response = new JsonResponse();
        $datas = array();
        $instance = $this->get('doctrine')->getRepository('WpierreScafoScafoBundle:ConfigInstance')->findOneById($id_instance);
        $datas['processFolderBy1'] = FilesOperations::getFilesCount(FilesOperations::getFolder($instance,'input','By_1',true));
        $datas['processFolderBy2'] = FilesOperations::getFilesCount(FilesOperations::getFolder($instance,'input','By_2',true));
        $datas['processFolderBy3'] = FilesOperations::getFilesCount(FilesOperations::getFolder($instance,'input','By_3',true));
        $datas['processFolderBy4'] = FilesOperations::getFilesCount(FilesOperations::getFolder($instance,'input','By_4',true));
        $datas['processFolderBySeparator'] = FilesOperations::getFilesCount(FilesOperations::getFolder($instance,'input','By_Separator',true));
        $datas['RefilterPDF'] = FilesOperations::getFilesCount(FilesOperations::getFolder($instance,'input','PDF_To_Refilter',true));
        $datas['PicturesToCBZ'] = FilesOperations::getAllFilesCount(FilesOperations::getFolder($instance,'input','Pictures_To_CBZ',true));
        
        $response->setData($datas);
        return $response;
    }
    
    public function editAction($id_instance){
    	$request = Request::createFromGlobals();
    	$datas = Array();
    	$datas['instance'] = $this->get('doctrine')->getRepository('WpierreScafoScafoBundle:ConfigInstance')->findOneById($id_instance);
    	$form = $this->createFormBuilder($datas['instance'])
    			->setMethod('POST')
                ->add('instanceName','text',array('label'=>'Nom de l\'instance'))
                ->add('input_folder','text',array('label'=>'Répertoire d\'entrée des documents','mapped'=>false))
                ->add('work_folder','text',array('label'=>'Répertoire temporaire de travail','mapped'=>false))
                ->add('output_folder','text',array('label'=>'Répertoire de sortie des documents','mapped'=>false))
                ->add('save','submit',array('label'=>'Enregistrer'))
                ->getForm();
    	
        //Traitement des champs non mappés
        if ($form->get('input_folder')->getData() == null){
        	$form->get('input_folder')->setData($datas['instance']->getConfig('input_folder'));
        }
        if ($form->get('work_folder')->getData() == null){
        	$form->get('work_folder')->setData($datas['instance']->getConfig('work_folder'));
        }
        if ($form->get('output_folder')->getData() == null){
        	$form->get('output_folder')->setData($datas['instance']->getConfig('output_folder'));
        }
        
        $form->handleRequest($request);
        $datas['form'] = $form->createView();
        if ($form->isValid()){
        
        	//Vérification des droits d'écritures dans les répertoires de l'instance
        	$error = false;
        	if (!is_writable($form->get('input_folder')->getData())){
        		$error=true;
        		$this->get('session')->getFlashBag()->add('error', 'Le répertoire '.$form->get('input_folder')->getData().' n\'existe pas ou n\'est pas accessible en écriture.');
        	}
        	if (!is_writable($form->get('work_folder')->getData())){
        		$error=true;
        		$this->get('session')->getFlashBag()->add('error', 'Le répertoire '.$form->get('work_folder')->getData().' n\'existe pas ou n\'est pas accessible en écriture.');
        	}
        	if (!is_writable($form->get('output_folder')->getData())){
        		$error=true;
        		$this->get('session')->getFlashBag()->add('error', 'Le répertoire '.$form->get('output_folder')->getData().' n\'existe pas ou n\'est pas accessible en écriture.');
        	}

        	if ($error){
        		return $this->render('WpierreScafoScafoBundle:Instances:edit.html.twig', $datas);
        	}
        	
        	$em = $this->getDoctrine()->getManager();
        	$em->persist($datas['instance']);
        	$em->flush();
        	
        	foreach($datas['instance']->getParameters() as $parameter){
        		$parameter->setValue($form->get($parameter->getParamName())->getData());
        		$em->persist($parameter);
        		$em->flush();
        	}
        	
        	
        	$this->get('session')->getFlashBag()->add('success', 'L\'instance '.$datas['instance']->getInstanceName().' a bien été modifiée');
        	return $this->redirect($this->generateUrl('wpierre_scafo_scafo_homepage',array()));
        }
        
    	return $this->render('WpierreScafoScafoBundle:Instances:edit.html.twig', $datas);
    }
    
    public function createAction(){
    	$request = Request::createFromGlobals();
    	$datas = Array();
    	$datas['instance'] = new ConfigInstance();
    	$form = $this->createFormBuilder($datas['instance'])
	    	->setMethod('POST')
	    	->add('instanceName','text',array('label'=>'Nom de l\'instance'))
	    	->add('input_folder','text',array('label'=>'Répertoire d\'entrée des documents','mapped'=>false))
	    	->add('work_folder','text',array('label'=>'Répertoire temporaire de travail','mapped'=>false))
	    	->add('output_folder','text',array('label'=>'Répertoire de sortie des documents','mapped'=>false))
	    	->add('save','submit',array('label'=>'Enregistrer'))
	    	->getForm();
    	 
    	//Traitement des champs non mappés
    	if ($form->get('input_folder')->getData() == null){
    		$form->get('input_folder')->setData($datas['instance']->getConfig('input_folder'));
    	}
    	if ($form->get('work_folder')->getData() == null){
    		$form->get('work_folder')->setData($datas['instance']->getConfig('work_folder'));
    	}
    	if ($form->get('output_folder')->getData() == null){
    		$form->get('output_folder')->setData($datas['instance']->getConfig('output_folder'));
    	}
    
    	$form->handleRequest($request);
    	$datas['form'] = $form->createView();
    	if ($form->isValid()){
    
    		 
    		$em = $this->getDoctrine()->getManager();
    		$em->persist($datas['instance']);
    		$em->flush();

    		$parametres = array('input_folder','work_folder','output_folder');
    		foreach($parametres as $parametre){
    			$param = new Parameter();
    			$param->setParamName($parametre);
    			$param->setValue($form->get($parametre)->getData());
    			$param->setInstance($datas['instance']);
    			$em->persist($param);
    			$em->flush();
    		}
    		 
    		 
    		$this->get('session')->getFlashBag()->add('success', 'L\'instance '.$datas['instance']->getInstanceName().' a bien été créée');
    		return $this->redirect($this->generateUrl('wpierre_scafo_scafo_homepage',array()));
    	}
    
    	return $this->render('WpierreScafoScafoBundle:Instances:create.html.twig', $datas);
    }
    
    public function deleteAction($id_instance){
    	$request = Request::createFromGlobals();
    	$datas = Array();
    	$datas['instance'] = $this->get('doctrine')->getRepository('WpierreScafoScafoBundle:ConfigInstance')->findOneById($id_instance);
    	$form = $this->createFormBuilder($datas['instance'])
    	->setMethod('POST')
    	->add('delete','submit',array('label'=>'Supprimer'))
    	->add('cancel','submit',array('label'=>'Annuler'))
    	->getForm();
    
    	$form->handleRequest($request);
    	$datas['form'] = $form->createView();
    	if ($form->isValid()){
    
    		if ($form->get('delete')->isClicked()){
    			$this->get('session')->getFlashBag()->add('success', 'L\'instance '.$datas['instance']->getInstanceName().' a bien été supprimée');
	    		$em = $this->getDoctrine()->getManager();
	    		$em->remove($datas['instance']);
	    		$em->flush();
    		}	    		
    		 
    		
    		return $this->redirect($this->generateUrl('wpierre_scafo_scafo_homepage',array()));
    	}
    
    	return $this->render('WpierreScafoScafoBundle:Instances:delete.html.twig', $datas);
    }
}
