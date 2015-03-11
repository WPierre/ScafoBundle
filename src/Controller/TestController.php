<?php

namespace WPierre\Scafo\ScafoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\HttpKernel;
use WPierre\Scafo\ScafoBundle\Entity\ConfigInstance;
use WPierre\Scafo\ScafoBundle\Entity\Parameter;

class TestController extends Controller
{
	/**
	 * Variable qui contient les messages successifs
	 * @var string
	 */
	private $message = '';
	
    public function indexAction()
    {
    	$this->message = '';
        $datas = Array();
        
        //Vérification de la version de Symfony (>= 2.6)
        $this->addMessage("info", "Version de Symfony", "Merci de vérifier que Symfony est bien en version 2.6");
        //Vérification de la présence du package WhiteOctoberTCPDF (WhiteOctober\TCPDFBundle\WhiteOctoberTCPDFBundle())
        
        if (class_exists('\WhiteOctober\TCPDFBundle\WhiteOctoberTCPDFBundle')){
        	$this->addMessage("success", "WhiteOctoberPDFBundle", "Le package WhiteOctoberPDFBundle est présent");
        } else {
        	$this->addMessage("danger", "WhiteOctoberPDFBundle", "Le package WhiteOctoberPDFBundle n'est pas installé dans votre Symfony. Merci d'ajouter le package en lançant la commande 'php composer.phar require \"whiteoctober/tcpdf-bundle\": \"dev-master\"");
        }        
        
        //Vérification si tesseract est bien installé
        $retour = null;
        exec('tesseract -v 2>&1',$retour);
        $retour = implode(" ", $retour);
        //$retour = system('ls -l ');

        $motif = '/(tesseract [0-9]+\.[0-9]+)/i';
        if (preg_match($motif, $retour)){
        	$this->addMessage("success", "Tesseract", "Tesseract est bien installé sur le système.");
        } else {
        	$this->addMessage("danger", "Tesseract", "Tesseract n'est pas installé sur le système. Merci de l'installer avec la commande 'sudo apt-get install libtesseract3 tesseract-ocr tesseract-ocr-eng tesseract-ocr-equ tesseract-ocr-fra tesseract-ocr-osd' sous Debian/Ubuntu");
        }
        
        //Vérification de la présence d'imagick pour tesseract
        $retour = null;
        exec('convert -version 2>&1 ',$retour);
        $retour = implode(" ", $retour);
        //$retour = system('ls -l ');
        
        $motif = '/(imagemagick [0-9]+\.[0-9]+)/i';
        if (preg_match($motif, $retour)){
        	$this->addMessage("success", "ImageMagick", "ImageMagick est bien installé sur le système.");
        } else {
        	$this->addMessage("danger", "ImageMagick", "ImageMagick ne semble pas être installé sur votre système. Le processus PHP n'arrive pas à exécuter le programme 'convert'. Merci de l'installer avec la commande 'sudo apt-get install imagemagick' sous Debian/Ubuntu");
        }
        
        //En dernier, test de la présence d'une instance et création si nécessaire
        $datas['instances'] = $this->get('doctrine')->getRepository('WpierreScafoScafoBundle:ConfigInstance')->findAll();
        if (count($datas['instances']) > 0 ){
        	$this->addMessage("success", "Configuration d'une instance par défaut", "Il y a déjà une instance de paramétrée. Aucun changement appliqué");
        } else {
        	//Création d'une instance par défaut
        	$datas['instance'] = new ConfigInstance();
        	$datas['instance']->setInstanceName("Default");
        	//Définition et création des répertoires pour l'instance par défaut
        	//Input
        	$path = $this->get('kernel')->getRootDir()."/Default_repo";
        	if (!file_exists($path)){
        		mkdir($path);
        	}
        	$param1 = new Parameter();
        	$param1->setInstance($datas['instance']);
        	$param1->setParamName("input_folder");
        	$path = $this->get('kernel')->getRootDir()."/Default_repo/Input";
            if (!file_exists($path)){
        		mkdir($path);
        	}
        	$param1->setValue($path);
        	//Temp
        	$param2 = new Parameter();
        	$param2->setInstance($datas['instance']);
        	$param2->setParamName("work_folder");
        	$path = $this->get('kernel')->getRootDir()."/Default_repo/Temp";
            if (!file_exists($path)){
        		mkdir($path);
        	}
        	$param2->setValue($path);
        	//Output
        	$param3 = new Parameter();
        	$param3->setInstance($datas['instance']);
        	$param3->setParamName("output_folder");
        	$path = $this->get('kernel')->getRootDir()."/Default_repo/Output";
            if (!file_exists($path)){
        		mkdir($path);
        	}
        	$param3->setValue($path);

        	//On sauvegarde le tout
        	$em = $this->getDoctrine()->getManager();
        	$em->persist($datas['instance']);
        	$em->persist($param1);
        	$em->persist($param2);
        	$em->persist($param3);
        	$em->flush();
        	
        	$this->addMessage("success", "Configuration d'une instance par défaut", "Une instance a été créée. Vous pouvez y accéder en passant par l'accueil de l'application.");
        	
        	$this->message .= '<a href="/Test/" class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-repeat"></span> Relancer le test</a>';
        	$this->message .= ' <a href="/" class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-repeat"></span> Retour à l\'accueil</a>';
        }
        
        //Enfin, on teste le processus complet avec une image sortie du bundle
        
        $datas['instance'] = null;
        $datas['messages'] = $this->message;
        return $this->render('WpierreScafoScafoBundle:Test:index.html.twig', $datas);
    }
    
    /**
     * Fonction outil pour ajouter un panel à la liste des messages
     * @param string $statut (primary, success, info, warning, danger)
     * @param string $title Le titre du panel
     * @param string $message
     */
    private function addMessage($statut, $title, $message){
    	$this->message .= '<div class="panel panel-'.$statut.'">
    							<div class="panel-heading">
									<h3 class="panel-title">'.$title.'</h3>
								</div>
								<div class="panel-body">
									'.$message.'
								</div>    				
    						</div>'."\n";
    }
}

