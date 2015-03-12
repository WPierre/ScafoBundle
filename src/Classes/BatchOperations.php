<?php
//Il devrait y avoir les opérations contenues par la commande pour permettre un traitement depuis les deux interfaces (cli et apache)
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WPierre\Scafo\ScafoBundle\Classes;

use WPierre\Scafo\ScafoBundle\Entity\Filter,
    WPierre\Scafo\ScafoBundle\Repository\FilterRepository,
    WPierre\Scafo\ScafoBundle\Entity\Document,
    WPierre\Scafo\ScafoBundle\Classes\FilesOperations,
    Smalot\PdfParser\Parser,
    ZipArchive;
    

/**
 * Description of ImportManager
 *
 * @author Ashygan
 */
class BatchOperations {
    
    /**
     * @var ConfigInstance L'instance de configuration sur laquelle l'exécution tourne 
     */
    private $config_instance;
    
    /**
     * Variable de debug. Conserve les fichiers OCR pour tester les filtres
     * @var boolean 
     */
    private $debug = false;
    
    /**
     *
     * @var OutputInterface L'interface de sortie 
     */
    private $output;
    
    /**
     * Contient le container de Symfony
     * @var Container 
     */
    private $container;
    
    public function __construct($id_instance, $container){
        //storing output so we can access from other methods
        $this->output = "";
        $this->container = $container;
        
        $logger = $this->container->get('logger');
    	$logger->info('Starting the index task...');
        
        $this->config_instance = $this->container->get('doctrine')->getRepository('WPierreScafoScafoBundle:ConfigInstance')->findOneById($id_instance);
        
    }
    
    
    
    public function executeCommand($command){
        switch($command){
            case ("processFolderBy1");
                $this->processFolderByNumber(1);
                break;
            case ("processFolderBy2");
                $this->processFolderByNumber(2);
                break;
            case ("processFolderBy3");
                $this->processFolderByNumber(3);
                break;
            case ("processFolderBy4");
                $this->processFolderByNumber(4);
                break;
            case ("processFolderBySeparator");
                $this->processFolderBySeparator();
                break;
            case ("processRefilterPDF");
                $this->processRefilterPDF();
                break;
            case ("processPicturesToCBZ");
                $this->processPicturesToCBZ();
                break;
            default:
                echo $command;
                $this->output .= 'Commande inconnue !\n';
        }
        //Run the final conversion
        $this->batchConvertToPDF();

        return $this->output;
    }
    /**
     * Execute the command
     * The environment option is automatically handled.
     */
    public function executeFullRun()
    {
                //Process files by number
        for ($i=1; $i<5; $i++){
            $this->output .= "On regarde s'il y a quelque chose à parser dans le répertoire By_".$i."...\n";
            $this->processFolderByNumber($i);
        }
        
        $this->output .= "On regarde s'il y a quelque chose à parser dans le répertoire By_Separator...\n";
        $this->processFolderBySeparator();
        
        $this->output .= "On regarde s'il y a quelque chose à parser dans le répertoire PDF_To_Refilter...\n";
        $this->processRefilterPDF();
        
        //process files other methods (TODO)
        
        //Running the PDF conversion task
        $this->batchConvertToPDF();
        
        $logger->info('Ending the index task \o/');
        return $this->output;
    }
    
    private function batchConvertToPDF(){
        //getting all the folders we have to work on
        $folders = FilesOperations::getFolders(FilesOperations::getFolder($this->config_instance,'work','Temp',true));

        $this->output .= "Starting the batch PDF conversion. ".count($folders)." folders to process.\n";
        //parsing he folders
        foreach ($folders as $folder){
            //treating the imagesToPdf actions
            if (strpos($folder, "imagesToPdf_") !== false){
                $this->output .= "Starting the PDF conversion for folder : ".$folder."\n";

                //sorting the files for the upcoming PDF conversion
                $files = $this->sortFilesForPDFConversion($folder);
                //var_dump($files);
                //testing the state files for knowing if we can process the folder
                if (strpos($files['state_marker'],"error.bad") !== false){
                    //There was an error in the folder treatment, folder is marked as bad
                    $this->output .= "Folder marked as in error. Skipping folder...\n";
                } elseif (strpos($files['state_marker'],"ready.ok") !== false) {
                    //ok marker is set, running the conversion

                    //There are alone files, something went wrong
                    if (count($files['leftovers']) > 0){
                        $this->output .= "Folder has leftovers. Skipping folder....\n";
                    } elseif (count($files['images']) != count($files['texts'])){
                        $this->output .= "Number of text files and number of images is different. Skipping folder...\n";
                    } else {
                        //everything is ok, start conversion !

                        //creating the pdf object
                        //$pdfObj = $this->container->get("white_october.tcpdf")->create();
                        $document = new Document($this->container, 'imagesToPdf', $this->config_instance);

                        for ($i=0; $i<count($files['texts']); $i++){
                            $document->addSourceText(file_get_contents($files['texts'][$i]));
                            $document->addSourceImage($files['images'][$i]);
                        }

                        $this->output .= $document->generateDocument();
                        if ($this->debug){
                            file_put_contents($document->getDebugOutputFilename(),$document->getFullText());
                        }

                        //delete the temporary folder
                        FilesOperations::delTree($folder);
                    }
                } else {
                    //There is no marker, something went wrong
                    $this->output .= "Folder doesn't have a state file (error.bad/ready.ok). Skipping folder...\n";
                }
            } elseif (strpos($folder, "PdfToRefilter_") !== false){
                $this->output .= "Starting the PDF refilter process for : ".$folder."\n";
                list($pdf_file) = FilesOperations::getFiles($folder, "pdf");
                list($txt_file) = FilesOperations::getFiles($folder, "txt");
                
                $document = new Document($this->container, 'PdfToRefilter', $this->config_instance);
                $document->setSourceText(file_get_contents($txt_file));
                $document->setSourcePdf($pdf_file);
                
                $this->output .= $document->generateDocument();
                if ($this->debug){
                    rename($txt_file,$document->getDebugOutputFilename());
                }
                FilesOperations::delTree($folder);
            }elseif (strpos($folder, "imagesToCBZ_") !== false){
                $this->output .= "Starting the CBZ conversion process for : ".$folder."\n";
                $output = FilesOperations::getFolder($this->config_instance,"output", "ImagesToCBZ", true);
                list($jpg_folder) = FilesOperations::getFolders($folder);
                $cbz_filename = basename($jpg_folder);
                $this->output .= "Traitement du répertoire ".$cbz_filename."\n";
                //création de l'archive zip
                $zip = new ZipArchive();
                
                $filename = $output."/".$cbz_filename.".cbz";

                if (!is_writable(dirname($filename))){
                    $this->output .="Impossible d'écrire à l'emplacement ".dirname($filename);
                }
                
                if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
                    
                    exit("Impossible d'ouvrir le fichier <$filename>\n");
                }
                
                $compteur = 0;
                //get files
                $files = FilesOperations::getFilesSortedByName($jpg_folder);
                
                foreach($files as $file){
                    if (strtolower(FilesOperations::getFileExtension($file) == "jpg") || strtolower(FilesOperations::getFileExtension($file) == "jpeg")){
                        //rename($folder."/".$jpg_folder."/".$file,$folder."/".$jpg_folder."/".str_pad($compteur,5,STR_PAD_LEFT)."-".$file);
                        if (!file_exists($file)){
                            $this->output .="erreur : fichier $file non existant";
                        }
                        if (!$zip->addFile($file,str_pad($compteur,5,"0",STR_PAD_LEFT)."-".basename($file))){
                            $this->output .= 'Erreur à l\'ajout du fichier '.$file;
                        }
                        //echo $file." ==> "."/".str_pad($compteur,5,"0",STR_PAD_LEFT)."-".basename($file)."<br />";
                        $compteur++;
                    }
                }
                if (!$zip->close()){
                    $this->output .= "erreur à l'enregistrement";
                }
                
                //delete the temporary folder
                //FilesOperations::delTree($folder);
            }else {
                $this->output .= "Couldn't find a module to process folder ".$folder."\n";
            }
        }    
        
    }
    
    /**
     * Process all the PDFs to refilter
     */
    private function processRefilterPDF(){
        $input_folder = FilesOperations::getFolder($this->config_instance,'input','PDF_To_Refilter', true);
        //getting the files
        $files = FilesOperations::getFiles($input_folder);
        
        foreach($files as $file){
            $this->output .= "\n";
            $this->output .= 'On traite le fichier '.$file."\n";
            $temp_folder = FilesOperations::getFolder($this->config_instance,'work','Temp',true)."/PdfToRefilter_".$this->getPlainMicrotime();
            mkdir($temp_folder);
            $pdfParser = new Parser();
            //$pdfParser = $this->container->get('smalot.pdfparser');
            
            $pdfDoc = $pdfParser->parseFile($file);
            $content = $pdfDoc->getText();
            
            //$this->output .= 'Le texte extrait du PDF a une longueur de : '.strlen($content)."\n";
            file_put_contents($temp_folder.'/'.  FilesOperations::getFileNameWithoutExtension($file).'.txt', $content);
            //file_put_contents($input_folder.'/'.  FilesOperations::getFileNameWithoutExtension($file).'.txt', $content);
            rename($file,$temp_folder.'/'.basename($file));
        }
        
    }
    
    private function processFolderByNumber($number){
        $input_folder = FilesOperations::getFolder($this->config_instance,'input','By_'.$number, true);
        
        //getting the files
        $files = FilesOperations::getFiles($input_folder);
        //checking if the number of files to be processed is correct
        if (count($files)%$number !== 0){
            throw new \Exception('Il y a un fichier manquant ou en trop dans le répertoire '.$input_folder.'. Vérifiez les fichiers du répertoire.'."\n");
        }
        
        $counter = 0;
        //starting the file processing and OCR
        foreach($files as $file){
            $this->output .= 'On traite le fichier '.$file."\n";
            //if new batch, creating a temporary folder to store the files
            if ($counter == 0){
                $temp_folder = FilesOperations::getFolder($this->config_instance,'work','Temp',true)."/imagesToPdf_".$this->getPlainMicrotime();
                mkdir($temp_folder);
            }
            /** TODO : make OCR here and store result txt file in temp folder*/
            //we OCRize the file and put it in a text file in the same folder
            $ocr = $this->container->get('scafo.ocrservice');
	    $ocr_text = $ocr->OCRizePage($file);
            //$this->output .= 'Le texte OCR a une longueur de : '.strlen($ocr_text)."\n";
            file_put_contents($temp_folder.'/'.  FilesOperations::getFileNameWithoutExtension($file).'.txt', $ocr_text);
            
            rename($file,$temp_folder.'/'.basename($file));
            $counter++;
            //If we processed as much files as requested, then reset the counter for the next batch
            if ($counter==$number){
                $this->output .= "Terminé un batch de ".$number." fichiers dans le répertoire ".$temp_folder."\n";
                //we put a marker file that allows the pdf conversion of the batch
                if (count(FilesOperations::getFiles($temp_folder,"txt")) == (count(FilesOperations::getFiles($temp_folder))/2)){
                    touch($temp_folder.'/'.'ready.ok');
                } else {
                    touch($temp_folder.'/'.'error.bad');
                }
                $counter=0;
            }
        }

    }

    private function processPicturesToCBZ(){
        $input_folder = FilesOperations::getFolder($this->config_instance,'input','Pictures_To_CBZ', true);
        
        //Getting the folders to process
        $folders = FilesOperations::getFolders($input_folder);
        var_dump($folders);
        foreach($folders as $folder){
            $this->output .= 'On traite le répertoire '.$folder."\n";
            $files = FilesOperations::getFiles($input_folder."/".$folder, "jpg");
            if (count($files)< 0){
                throw new \Exception('Il y n\'y a aucun fichier jpg dans le répertoire '.$folder.'. Merci de n\'utiliser que des fichiers jpg.'."\n");
            }
            $temp_folder = FilesOperations::getFolder($this->config_instance,'work','Temp',true)."/imagesToCBZ_".$this->getPlainMicrotime();
            mkdir($temp_folder);
            rename($folder,$temp_folder."/".basename($folder));
            touch($temp_folder.'/'.'ready.ok');
            
        } 
    }
    
    private function processFolderBySeparator(){
        $input_folder = FilesOperations::getFolder($this->config_instance,'input','By_Separator', true);
        //creating the folder if it doesn't exist
        if (!is_dir($input_folder)){
                mkdir($input_folder,0777, true);
        }
        //getting the files
        $files = FilesOperations::getFiles($input_folder);
        $temp_folder = null;
        $counter = 0;
        //starting the file processing and OCR
        foreach($files as $file){
            $this->output .= 'On traite le fichier '.$file."\n";
            //if new batch, creating a temporary folder to store the files
            if ($temp_folder == null){
                $temp_folder = FilesOperations::getFolder($this->config_instance,'work','Temp',true)."/imagesToPdf_".$this->getPlainMicrotime();
                mkdir($temp_folder);
            }

            //we OCRize the file and put it in a text file in the same folder
            $ocr = $this->container->get('scafo.ocrservice');
	    $ocr_text = $ocr->OCRizePage($file);
            //echo "***".$ocr_text."***";
            $this->output .= 'Le texte OCR a une longueur de : '.strlen($ocr_text)."\n";
            //if current document is a separator, then end the batch
            if (strtolower($ocr_text) == "fin de\ndocument"){
                $this->output .= "Terminé un batch de ".$counter." fichiers dans le répertoire ".$temp_folder."\n";
                touch($temp_folder.'/'.'ready.ok');
                $temp_folder = null;
                $counter=0;
                unlink($file);
            } else {
                file_put_contents($temp_folder.'/'.  FilesOperations::getFileNameWithoutExtension($file).'.txt', $ocr_text);
                rename($file,$temp_folder.'/'.basename($file));
                $counter++;
            }
        }
        if ($temp_folder != null){
            touch($temp_folder.'/'.'ready.ok');
        }

    }
    
    private function sortFilesForPDFConversion($folder){
        $files = array();
        $files['images'] = array();
        $files['texts'] = array();
        $files['leftovers'] = array();
        $files['state_marker'] = null;
        
        //getting all the files
        $found_files = FilesOperations::getFiles($folder);
        //getting all the text files so we know all the images we have to process
        $text_files = FilesOperations::getFiles($folder,"txt");
        foreach ($text_files as $text_file){
            $image_file = null;
            $files['texts'][] = $text_file;
            $image_file = $this->getCorrespondingImageFile($text_file);
            if ($image_file == null){
                throw new \Exception('ImageManagerCommand::sortFilesForPDFConversion => Error while retrieving the corresponding image file for '.$text_file);
            } else {
                $files['images'][] = $image_file;
            }
        }
        
        //Add the state marker file to the array
        foreach($found_files as $state_file){
            if (strpos($state_file,'ready.ok') !== false || strpos($state_file,'error.bad') !== false){
                $files['state_marker'] = $state_file;
            }
        }
        
        //we're off to clean the found files array from the really found files
        foreach($found_files as $current_file){
            if ($this->recursive_array_search($current_file,$files) === false){
                $files['leftovers'][] = $current_file;
            }
        }
        
        return $files;
    }
    
    /**
     * Retourne le nom du fichier image correspondant au fichier texte passé en paramètre
     * @param string $file Le fichier texte pour lequel on cherche l'image correspondante (chemin complet requis)
     * @return string
     */
    private function getCorrespondingImageFile($file){
        //$this->output .= 'Searching corresponding image file for file : '.$file;
        $infos = pathinfo($file);
        $files = FilesOperations::getFiles($infos['dirname']);
        $basename = $infos['filename'];
        //$this->output .= 'Folder name : '.$infos['dirname'].' / Basename : '.$basename;
        foreach($files as $current_file){
            //if current_file isn't the source txt file and contains the file basename, then we return current_file
            //$this->output .= 'Comparing to file : '.$current_file;
            if ($current_file != $file && strpos($current_file, $basename) !== false){
                return $current_file;
            }
        }
        
    }
    
    /**
     * Retourne true si la valeur cherchée se trouve quelque part dans le tableau, même s'il est multidimensionnel
     * @param String $needle La valeur à chercher
     * @param type $haystack Le tableau dans lequel il faut chercher
     * @return boolean
     */
    function recursive_array_search($needle,$haystack) {
        foreach($haystack as $key=>$value) {
            $current_key=$key;
            if($needle===$value OR (is_array($value) && $this->recursive_array_search($needle,$value) !== false)) {
                return $current_key;
            }
        }
        return false;
    }
    
    public function setDebug($debug){
        if ($debug === true){
           $this->debug = true; 
        } else if ($debug === false){
            $this->debug === false;
        } else {
            throw new Exception ('BatchOperations::setDebug : Valeur autre que booléen entrée : '.$debug);
        }
    }
    
    public function getPlainMicrotime(){
        return str_replace(" ", "", microtime());
    }
}
