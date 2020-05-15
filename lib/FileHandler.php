<?php
class Lib_FileHandler {
    private $directory = '';
    
    public function __construct($directory){
       
        $this->setDir($directory);
    }

    public static function FileSizeConvert($bytes){
        $result = 0;
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                        "UNIT" => "TB",
                        "VALUE" => pow(1024, 4)
                      ),
            1 => array(
                        "UNIT" => "GB",
                        "VALUE" => pow(1024, 3)
                      ),
            2 => array(
                        "UNIT" => "MB",
                        "VALUE" => pow(1024, 2)
                      ),
            3 => array(
                        "UNIT" => "KB",
                        "VALUE" => 1024
                      ),
            4 => array(
                        "UNIT" => "B",
                        "VALUE" => 1
                      ),
        );
    
        foreach($arBytes as $arItem){
            if($bytes >= $arItem["VALUE"]){
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", "." , strval(round($result, 2)))." ".$arItem["UNIT"];
                break;
            }
        }
        return $result;
    }
    
    public function getFileSize($filename){
        $total_size = 0;
        $files = '';
        $files = scandir($filename);
        $cleanPath = rtrim($filename, '/'). '/';
        
        foreach($files as $t) {
            if ($t<>"." && $t<>"..") {
                $currentFile = $cleanPath . $t;
                if (is_dir($currentFile)) {
                    $size = $this->getFileSize($currentFile);
                    $total_size += $size;
                }else {
                    $size = filesize($currentFile);
                    $total_size += $size;
                }
            }
        }
     return $total_size;
    }
    
    public function getDirectoryList($sort = SCANDIR_SORT_ASCENDING){
        $temp = array();
        // get rid of . and ..
        $temp = array_diff(scandir($this->getDir(),$sort), array('..', '.'));

        // reset the array ks-content-maid
        return  array_merge($temp);
    }
    
    public function setDir($dir_name) {
        if (is_dir($dir_name)) {
            $this->directory = $dir_name;
            return TRUE;
        }
        return FALSE;        
    }

    public function getDir() {
        return $this->directory;
    }

    public static function log($filename, $msg){
        return file_put_contents($filename, $msg, FILE_APPEND);
    }

    /**
     * 
     * @param unknown $dirPath -> Path to the folders
     * @param unknown $section -> if it is survey or content. this will be used to be added to the folder as well
     * @param unknown $folder_name -> What is the name of the folder you want to delete or move
     * @param unknown $deletedPath -> where do you want to move the folders to
     * @param unknown $right_now -> current date and time to create a folder to copy or move them to 
     * @param string $purge -> if you want physically delete the folders or not
     * @return boolean
     */
    public static function deleteDir($dirPath, $section, $folder_name, $deletedPath, $right_now, $purge = FALSE) {
        
        // if the passed on path is not a directory
        if (! is_dir($dirPath.$section.'/'.$folder_name)) {
            echo($dirPath.$section.'/'.$folder_name." must be a directory".PHP_EOL);
        }else {
            // if the path doesn't have '/' then add to it
            $dirPath = self::checkForSlash($dirPath.$section.'/'.$folder_name);
            
            // if the path doesn't have '/' then add to it
            $base_deleted_folder = self::checkForSlash($deletedPath.$right_now);
            
            // if the passed on path is not a directory
            if (! is_dir($base_deleted_folder.$section)) {
                echo($base_deleted_folder.$section." must be a directory".PHP_EOL);
            }else{    
            
                // where the files should be moved from
                $source = $dirPath;
                 
                // where the files needs to be moved
                // if the path doesn't have '/' then add to it
                $destination = self::checkForSlash($base_deleted_folder.$section);
    
                // if delete physically is not set then just copy and then delete otherwise physically delete from the source folder
                if(!$purge){ 

                    // if source and target checksum are the same then go ahead and delete the folder
                    exec("cp -R {$source} {$destination}");
//echo "source=$source --- destination="."$destination$folder_name".PHP_EOL;    
                    if(self::recurse_checksum($source, "$destination$folder_name")){
                        exec("rm -rf {$source}");
                    }
                }else{
                    exec("rm -rf {$source}");
                }         
            }
        }
        return TRUE;
    }
    
    /**
     * Build a folder
     * @param String $deletedPath -> Path where the folder suppose to be built
     * @param String $section -> If it is survey or content
     * @param Date $right_now -> today's date and time
     * @return boolean
     */
    public static function buildFolder($path, $folder_name){
        // if the path doesn't have '/' then add to it
        $path = self::checkForSlash($path.'/'.$folder_name);
        $output = TRUE;
        
        // Try to create the root directory - Date time
        if (!mkdir($path)) {
            echo ("Failed to create $path".PHP_EOL);
            $output = FALSE;
        }        
        return $output;
    }    
    
    public static function recurse_checksum($src, $dst) {
        $source_checksum = '';
        $target_checksum = '';
        $dir = opendir($src);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src.'/'.$file) ) {
                    $temp_src = self::checkForSlash($src.'/'.$file);
                    $temp_tar = self::checkForSlash($dst.'/'.$file);
                    self::recurse_checksum($temp_src, $temp_tar);
                }else {
                    // Calculate the source_checksum
                    if ($source_checksum=='') {
                        $source_checksum = self::calChecksum("$src$file");
                    }else{
                        $source_checksum = $source_checksum  xor self::calChecksum("$src$file");
                    }

                    // Calculate the target_checksum
                    if ($target_checksum=='') {
                        $target_checksum = self::calChecksum("$dst/$file");
                    }else{
                        $target_checksum = $target_checksum  xor self::calChecksum("$dst/$file");
                    }
                }
            }
        }
        closedir($dir);
        if ($source_checksum == $target_checksum){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    /**
     * check for / in the path been passed and if not there wil be added. 
     * @param String $path -> path
     * @return string -> The correct path
     */
    public static function checkForSlash($path){
        // if the path doesn't have '/' then add to it
        if (substr($path, strlen($path) - 1, 1) != '/') {
            $path .= '/';
        }
        return $path;
    }
    
    /**
     * Calculate the checksum of a file
     * @param String  $dirPath -> Path to the file
     * @param String $fileName -> File name
     * @return Int the checksum of the files content
     */
    public static function calChecksum($file){
        return sha1_file($file);
    } 
    
    public static function readFile($dirPath, $fileName) {
	    $contents = '';
	    if (file_exists($dirPath.$fileName)) {
	        $handle = fopen($dirPath.$fileName, 'r');
	        $contents = fread($handle, filesize($dirPath.$fileName));
	        fclose($handle);
	    }
	    return $contents;
    }
}
