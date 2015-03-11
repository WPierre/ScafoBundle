<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WPierre\Scafo\ScafoBundle\Entity;

use     WPierre\Scafo\ScafoBundle\Classes\FilesOperations;

/**
 * Non-Entity class to manage document operations
 *
 * @author Ashygan
 */
class Document {
    //put your code here
    
    /**
     * Contient le container pour appeler le service tcpdf
     * @var container 
     */
    private $container;
    
    /**
     * Contains the current Instance Config
     * @var configInstance
     */
    private $config_instance;
    
    /**
     * Contains all the source images 
     * @var array (of files paths)
     */
    private $sourceImages = array();
    
    /**
     * Contains the source text for the document
     * @var array (of string)
     */
    private $sourceText;
    
    /**
     * Contains the source pdf to be processed
     * @var string (file path) 
     */
    private $sourcePdf;
    
    /**
     * Contains the operation required on the document (ex : images to PDF, PDF to re-filter, etc.)
     * @var string 
     */
    private $operation;
    
    /**
     * The filter that matches the document
     * @var Filter 
     */
    private $filter;
    
    /**
     * L'objet PDF pour la sortie (si sortie PDF)
     * @var tcpdf 
     */
    private $pdfObj;
    
    /**
     * The date of the document (ie : The day the bill was sent)
     * @var \Datetime 
     */
    private $documentDate;
    
    /**
     * The fodler the file will be written to
     * @var string (path) 
     */
    private $outputFolder;
    
    /**
     * The pattern used to determine the output file's name
     * @var string 
     */
    private $filenameFormatter;
    
    /**
     * The output file's name
     * @var string 
     */
    private $outputFileName;
    
    /**
     * The constructor
     * @param String $operation
     */
    public function __construct($container, $operation,$config_instance){
        $this->container = $container;
        $this->operation = $operation;
        $this->config_instance = $config_instance;
        $this->documentDate = new \DateTime(); //defaults the date to now
        $this->filenameFormatter = "Doc_".time()."_%date%";
        $this->outputFolder = FilesOperations::getFolder($this->config_instance,"output", "Unindexed", true);
        switch($this->operation){
            case 'imagesToPdf':
                $this->initializeImagesToPDF();
                break;
            case 'PdfToRefilter':
                break;
            default:
                throw new \Exception("Document::__contruct => Operation ".$this->operation." is unknown.");
        }
    }
    
    /**
     * Initialize the document for a image to PDF conversion
     */
    public function initializeImagesToPDF(){
        $this->pdfObj = $this->container->get("white_october.tcpdf")->create();
        $this->pdfObj->setPrintHeader(false);
        $this->pdfObj->setPrintFooter(false);
        $this->pdfObj->SetMargins(0, 0, 0);
        $this->pdfObj->SetAutoPageBreak(False);
        $this->pdfObj->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $this->pdfObj->SetFont('helvetica', '', 0.01);
        $this->pdfObj->setTextRenderingMode($stroke=0, false, false);
        
    }
    /**
     * Add a new image to be processed
     * @param string $image path to the image
     */
    public function addSourceImage($image){
        if (is_file($image)){
            $this->sourceImages[] = $image;
        } else {
            throw new \Exception("The requested image (".$image.") to be added doesn't exist");
        }
    }
    
    /**
     * Adds some text to be processed
     * @param string $text
     */
    public function addSourceText($text){
        $this->sourceText[] .= $text;
    }
    
    /**
     * Returns full text
     */
    public function getFullText(){
        return implode("\n",$this->sourceText);
    }
    
    public function setSourcePdf($file){
        if (is_file($file)){
            $this->sourcePdf = $file;
        } else {
            throw new \Exception("The requested pdf (".$file.") to be added doesn't exist");
        }
    }
    
    /**
     * Sets the text to be processed
     * @param string $text
     */
    public function setSourceText($text){
        $this->sourceText = array($text);
    }
    
    /**
     * Generates the document according to the operation
     * @throws \Exception
     */
    public function generateDocument(){
        switch($this->operation){
            case 'imagesToPdf':
                return $this->generateImagesToPDFDocument();
                break;
            case 'PdfToRefilter':
                return $this->refilterPdf();
                break;
            default:
                throw new \Exception("Document::__contruct => Operation ".$this->operation." is unknown in context generateDocument.");
        }
    }
    
    public function refilterPdf(){
        $log = "";
        $this->filter = $this->container->get('doctrine')->getManager()->getRepository('WpierreScafoScafoBundle:Filter')->getGoodFilter($this->getFullText());
            
        //initializing the output_path
        //$output_path = $this->getFolder("output", "Unindexed");

        if ($this->filter == null){
                $log .= "\n<b>Il n'y a aucun filtre qui a été retenu</b>\n";
            } else {
                $log .= "\n<b>Le filtre retenu est : ".$this->filter->getTitle()."</b>\n";
            $this->setDocumentDateByFilter();
            $this->outputFolder = FilesOperations::getFolder($this->config_instance,"output",$this->filter->getPath(), true);
            $this->filenameFormatter = $this->filter->getFilenameFormater();
        }

        //Formatting the date extracted from the document
        $doc_date = $this->documentDate->format('Y-m-d');
        $filename = str_replace("%date%",$doc_date,$this->filenameFormatter).".pdf";
        //write the PDF file and the corresponding merged text file
        $this->outputFileName = $this->generateFreeFileName($this->outputFolder.'/'.$filename);
        $log .= "Le fichier de destination est : <b>".$this->outputFileName."</b>\n";
        //move the PDF file to it's new destination
        rename($this->sourcePdf, $this->outputFileName);
        return $log;
    }
    
    public function generateImagesToPDFDocument(){
        $log = "";
        if (count($this->sourceImages) != count($this->sourceText)){
            throw new \Exception("Document::generateImagesToPDFDocument => Number of images (".count($this->sourceImages).") isn't the same as the number of texts (".count($this->source_text).")");
        } else {
            //add all the pages
            for ($i=0; $i<count($this->sourceImages); $i++){
                $this->pdfObj->AddPage();
                $this->pdfObj->Write(0, $this->sourceText[$i], '', 0, '', true, 0, false, false, 0);
                $this->pdfObj->Image($this->sourceImages[$i],00,00,210,297);
            }
            
            $this->filter = $this->container->get('doctrine')->getManager()->getRepository('WpierreScafoScafoBundle:Filter')->getGoodFilter($this->getFullText());
            
            //initializing the output_path
            //$output_path = $this->getFolder("output", "Unindexed");

            if ($this->filter == null){
                $log .= "\n<b>Il n'y a aucun filtre qui a été retenu</b>\n";
            } else {
                $log .= "\n<b>Le filtre retenu est : ".$this->filter->getTitle()."</b>\n";
                $this->setDocumentDateByFilter();
                $this->outputFolder = FilesOperations::getFolder($this->config_instance,"output",$this->filter->getPath(), true);
                $this->filenameFormatter = $this->filter->getFilenameFormater();
            }

            //Formatting the date extracted from the document
            $doc_date = $this->documentDate->format('Y-m-d');

            $filename = str_replace("%date%",$doc_date,$this->filenameFormatter).".pdf";
            //write the PDF file and the corresponding merged text file
            $this->outputFileName = $this->generateFreeFileName($this->outputFolder.'/'.$filename);
            $log .= "Le fichier de destination est : <b>".$this->outputFileName."</b>\n";
            
            $this->pdfObj->Output($this->outputFileName, 'F');
            //file_put_contents($folder.'/'.'output.txt', $text);
            return $log;
        }
    }
    
    private function setDocumentDateByFilter(){
        $filter_format = $this->filter->getDocDateFormater();
        /*
         * formats de dates cherchés par ordre de priorité
         * %date_texte% ex : 02 juillet 2014
         * %date_slash% ex: 02/07/2014
         * %date_tiret% ex: 02/07/2014
         * 
         * %date parcourt toutes ces solutions. Si aucune date n'est trouvée avec la première solution, il passe à la deuxième.
         */
        
        if(strpos($filter_format,"%date_texte%") !== false){
            $this->searchDateTexte();
        } else if(strpos($filter_format,"%date_slash%") !== false){
            $this->searchDateSlash();
        } else if(strpos($filter_format,"%date_tiret%") !== false){
            $this->searchDateTiret();
        } else if(strpos($filter_format,"%date%") !== false){
            if (!$this->searchDateTexte()){
                if (!$this->searchDateSlash()){
                    $this->searchDateTiret();
                }
            }
        }
    }

    public function searchDateSlash(){
        $filter_format = $this->filter->getDocDateFormater();
        $date_format = "eur";
        //get the pattern according to the format
        switch($date_format){
                case "eur" :
                        $pattern = '([0-9]{2})\/([0-9]{2})\/([0-9]{4}|[0-9]{2})';
                        break;
                case "usa" :
                        $pattern = '([0-9]{2})\/([0-9]{2})\/([0-9]{4})';
                        break;
                case "jap" :
                        $pattern = '([0-9]{4})\/([0-9]{2})\/([0-9]{2})';
                        break;
                default :
                        $pattern = '([0-9]{2})\/([0-9]{2})\/([0-9]{4})';
                        break;
        }
        $filter_pattern = '@'.str_replace(array("%date%","%date_slash%"), $pattern, $filter_format).'@i';
        if (preg_match($filter_pattern,$this->getFullText(),$matches) == 1){
                //correction année si deux chiffres
                
                switch($date_format){
                        case "eur" :
                                if (strlen($matches[3]) == 2){$matches[3] = "20".$matches[3];}
                                $this->documentDate->setDate($matches[3], $matches[2], $matches[1]);
                                break;
                        case "usa" :
                                if (strlen($matches[3]) == 2){$matches[3] = "20".$matches[3];}
                                $this->documentDate->setDate($matches[3], $matches[1], $matches[2]);
                                break;
                        case "jap" :
                                if (strlen($matches[1]) == 2){$matches[1] = "20".$matches[1];}
                                $this->documentDate->setDate($matches[1], $matches[2], $matches[3]);
                                break;
                        default :
                                if (strlen($matches[3]) == 2){$matches[3] = "20".$matches[3];}
                                $this->documentDate->setDate($matches[3], $matches[2], $matches[1]);
                                break;
                }
                return true;
        }
        return false;
    }

    public function searchDateTiret(){
        $filter_format = $this->filter->getDocDateFormater();
        $date_format = "eur";
        //get the pattern according to the format
        switch($date_format){
                case "eur" :
                        $pattern = '([0-9]{2})\-([0-9]{2})\-([0-9]{4}|[0-9]{2})';
                        break;
                case "usa" :
                        $pattern = '([0-9]{2})\-([0-9]{2})\-([0-9]{4})';
                        break;
                case "jap" :
                        $pattern = '([0-9]{4})\-([0-9]{2})\-([0-9]{2})';
                        break;
                default :
                        $pattern = '([0-9]{2})\-([0-9]{2})\-([0-9]{4})';
                        break;
        }
        $filter_pattern = '@'.str_replace(array("%date%","%date_tiret%"), $pattern, $filter_format).'@i';
        if (preg_match($filter_pattern,$this->getFullText(),$matches) == 1){
                switch($date_format){
                        case "eur" :
                                if (strlen($matches[3]) == 2){$matches[3] = "20".$matches[3];}
                                $this->documentDate->setDate($matches[3], $matches[2], $matches[1]);
                                break;
                        case "usa" :
                                if (strlen($matches[3]) == 2){$matches[3] = "20".$matches[3];}
                                $this->documentDate->setDate($matches[3], $matches[1], $matches[2]);
                                break;
                        case "jap" :
                                if (strlen($matches[1]) == 2){$matches[1] = "20".$matches[1];}
                                $this->documentDate->setDate($matches[1], $matches[2], $matches[3]);
                                break;
                        default :
                                if (strlen($matches[3]) == 2){$matches[3] = "20".$matches[3];}
                                $this->documentDate->setDate($matches[3], $matches[2], $matches[1]);
                                break;
                }
                return true;
        }
        return false;
    }

    public function searchDateTexte(){
        $filter_format = $this->filter->getDocDateFormater();
        $pattern = "([0-9]{1,2}) (janvier|f.{1,2}vrier|mars|avril|mai|juin|juillet|ao.{1,2}t|septembre|octobre|novembre|d.{1,2}cembre) ([0-9]{4}|[0-9]{2})";
        $filter_pattern = '@'.str_replace(array("%date%","%date_texte%"), $pattern, $filter_format).'@i';
        
        if(preg_match($filter_pattern,$this->getFullText(),$matches) == 1){
            var_dump($matches);
            $jour = $matches[1];
            $annee = $matches[3];
            $mois = strtolower($matches[2]);
            switch($mois){
                case('janvier'):
                        $mois = "01";
                        break;
                case('mars'):
                        $mois = "03";
                        break;
                case('avril'):
                        $mois = "04";
                        break;
                case('mai'):
                        $mois = "05";
                        break;
                case('juin'):
                        $mois = "06";
                        break;
                case('juillet'):
                        $mois = "07";
                        break;
                case('septembre'):
                        $mois = "09";
                        break;
                case('octobre'):
                        $mois = "10";
                        break;
                case('novembre'):
                        $mois = "11";
                        break;		
                default:
                        if (substr($mois,0,1) == "f"){$mois = "02";}
                        if (substr($mois,0,1) == "a"){$mois = "08";}
                        if (substr($mois,0,1) == "d"){$mois = "12";}
           }
           $this->documentDate->setDate($annee, $mois, $jour);
           return true;
        }
        return false;
    }
    
    /**
     * Génère un nom de fichier qui ne soit pas déjà pris. Si test.pdf est pris, alors on essaie test.0.pdf, puis test.1.pdf, etc.
     * @param string $path Le chemin du fichier
     * @return string Le chemin du fichier corrigé
     * @throws Exception
     */
    public function generateFreeFileName($path){
        if (is_file($path)){
          $compteur = 0;
          $file_infos = pathinfo($path);
            while (is_file($file_infos['dirname'].'/'.$file_infos['filename'].'.'.$compteur.".".$file_infos['extension'])){
                $compteur++;
                if ($compteur> 10000){
                    throw new Exception ("Impossible de trouver un nombre pour le fichier qui ne soit pas déjà utilisé. Arrêt du traitement");
                }
            }
            return $file_infos['dirname'].'/'.$file_infos['filename'].'.'.$compteur.".".$file_infos['extension'];
        } else {
            return $path;
        }
    }
    
    /**
     * Renvoie le nom de sortie du fichier
     * @return string
     */
    public function getOutputFilename(){
        return $this->outputFileName;
    }
    
    /**
     * Renvoie le nom de sortie du fichier debug pour le document
     * @return string
     */
    public function getDebugOutputFilename(){
        $file_infos = pathinfo($this->outputFileName);
        return $file_infos['dirname'].'/'.$file_infos['filename'].".debug.txt";
    }
}
