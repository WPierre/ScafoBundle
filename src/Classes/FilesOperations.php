<?php

namespace WPierre\Scafo\ScafoBundle\Classes;


/**
 * Description of getFolders
 *
 * @author Ashygan
 */
class FilesOperations {
    
    
    public static function getFiles($folder, $extension = "*"){
        $files = glob($folder."/".'*.'.$extension);
        usort($files, function($a, $b) {
            return filemtime($a) < filemtime($b);
        });
        return $files;
    }
    
    public static function getFilesSortedByName($folder, $extension = "*"){
        $files = glob($folder."/".'*.'.$extension);
        usort($files, function($a, $b) {
            return strcmp($a, $b);
        });
        return $files;
    }

    
    
    public static function getFilesCount($folder, $extension = "*"){
    	$files = glob($folder."/".'*.'.$extension);
        return count($files);
    }
    public static function getAllFilesCount($folder){
        $files = glob($folder."/".'*');
        return count($files);
    }
    public static function getFolders($folder){
        $files = glob($folder.'/*',GLOB_ONLYDIR);
        usort($files, function($a, $b) {
            return filemtime($a) < filemtime($b);
        });
        return $files;
    }
    
    /**
     * Retourne le chemin complet demandé à partir du root système
     * @param String $type Le type de répertoire demandé (input, work, output)
     * @param String $folder Le chemin complémentaire à partir du type demandé
     * @param boolean $create_path True si le répertoire demandé doit être créé
     */
    public static function getFolder($config_instance,$type, $folder, $create_path = false){
        $destination = $config_instance->getConfig($type.'_folder').'/'.$folder;

        if (!is_dir($destination)){
            if ($create_path){
                mkdir($destination, 0777, true);
            } else {
                throw new \Exception("Le répertoire ".$destination." n'existe pas");
            }
        }
        return $destination;
    }

    public static function getFileNameWithoutExtension($file){
        $infos = pathinfo($file);
        return $infos['filename'];
    }
    public static function getFileExtension($file){
        $infos = pathinfo($file);
        return $infos['extension'];
    }
    /**
     * Removes a directory and its content
     * @param string $dir The directory to remove
     * @return boolean
     */
    public static function delTree($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
          (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    } 
    
}
