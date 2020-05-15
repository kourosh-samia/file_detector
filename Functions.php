<?php
class Functions {
	private $config;
	static private $max_exist_status = 0;
	static private $result = array();
	static private $row_count = 0;
	
	public function __construct(){
		$this->setConfig(Config::getConfig());
	}
	
	private function setConfig($config){
		$this->config = $config;
		return $this;
	}
	
	public function getConfig(){
		return $this->config;
	}
	
//----------------------------------- APP Messages --------------------------------------------------		

	/**
	 * Returns the system messages 
	 * @param $topic -> String name of the topic you want to get the message about
	 * @param $type  -> String The type of the message you want to get (error, warning,...)
	 * @return String The requested message
	 */
	public static function getSysMsgSource($topic, $type='warning'){
		$self = new Functions();
		$temp = $self->getConfig();
		return ($temp['system-messages'][$topic][$type].PHP_EOL); 
	}
	
//----------------------------------- GENERAL --------------------------------------------------
	/**
	 * Creates the empty report files in path folder 
	 * @param Array $logs -> the list of log files that final reports should be written into
	 * @param String $path -> the path where the qa report will be written in to
	 */
	public static function cleanLogs(array $logs, $path) {
	    foreach ($logs as $key => $log) {
            $file = $path.'/'.$log.'.log';
	        $handle = fopen($file, "w+");
 	        if ($handle != FALSE) {
 	            ftruncate($handle, 0);
 	            fclose($handle);
	        }
	    }
	}
	
	/**
	 * @author Kourosh Samia
	 * @return string
	 */
	public static function parseHeader(){
	    $output = "-----------------------------------------------".PHP_EOL;
	    $output .= " Author: Kourosh Samia - OCT 2017              ".PHP_EOL;
	    $output .= " File duplicate finder Version 1.0             ".PHP_EOL;
	    $output .= "-----------------------------------------------".PHP_EOL;
	    return $output;
	} 
	
	public static function parseStats($info, $test){
	    $output = PHP_EOL.PHP_EOL."-----------------------------------------------".PHP_EOL;
	    $output .= " Total Folders: "    .$info[$test]['t_folders'].PHP_EOL;
	    $output .= " Total {$test} Records: ".$info[$test]['t_contents']." in LMS Database".PHP_EOL;
	    $output .= " Total Size of Folders: "    .$info[$test]['t_folder_size'].' Bytes - '.Lib_FileHandler::FileSizeConvert($info[$test]['t_folder_size']).PHP_EOL;
	    $output .= " Total Folders Deleted: ".$info[$test]['t_folder_deleted']." From HDD ".PHP_EOL;
	    $output .= " Total Size of Deleted Folders: ".$info[$test]['t_folder_deleted_size'].' Bytes'.' - '.Lib_FileHandler::FileSizeConvert($info[$test]['t_folder_deleted_size']).PHP_EOL;
	    $output .= "-----------------------------------------------".PHP_EOL;
	
	    return $output;
	}
	
	/**
	 * calculate the maximum length of an array of string and returns the longest one back
	 * @param array $data -> array of strings
	 * @return Int
	 */
	public static function getMaxLength(array $data){
		$max_length = 0;
		$max_string = '';
		foreach ($data as $key=>$value) {
			if(strlen($value)>$max_length){
				$max_length=strlen($value);
				$max_string =$value;	
			}
		}
		return array('length'=>$max_length,
					 'data'=>$max_string);
	}
	
	/**
	 * format the output and add proper character to the end of string
	 * @param $data -> String input string
	 * @param $space -> Int number of charachters should be added
	 * @param $filler_char -> String What charachter should be used as filler
	 * @param $right_left_both -> STR_PAD_RIGHT , STR_PAD_LEFT, STR_PAD_BOTH
	 * @param $return_char -> Boolean if return char should be added or not
	 * 
	 * @return String
	 */
	public static function formatOutput($data, $space, $filler_char=' ', $right_left_both=STR_PAD_RIGHT, $return_char = FALSE){
		$temp = str_pad($data, $space, $filler_char, $right_left_both);
		if($return_char){
			return $temp.PHP_EOL;
		}else{
			return $temp;
		}
	}
	
	public static function createCSVFile($data, $path, $file_name){	    
	    $fp = fopen($path.$file_name, 'w');
	    $header ='type, id, version, faculty_id, course_wrapper_id, file size, exist in asset folder, exist in content table, exist in survey table, content_container_id, enrollment, deleted, status, title'.PHP_EOL;
	    fwrite($fp, $header);

	    foreach ( $data as $type=>$info ) {
	        foreach ( $info as $content=>$infoo ) {
	            foreach ( $infoo as $version=>$infooo ) {
	                $val = $infooo['type'].','.
	   	                   $infooo['id'].','.
	   	                   $infooo['version'].','.
	   	                   $infooo['faculty'].','.
	   	                   $infooo['course_wrapper_id'].','.
	   	                   $infooo['size'].','.
	   	                   $infooo['found_in_asset_folder'].','.
	                       $infooo['found_in_content_table'].','.
	                       $infooo['found_in_survey_table'].','.
	                       $infooo['content_container_id'].','.
	                       $infooo['enrollment'].','.
	                       $infooo['deleted'].','.
	                       $infooo['status'].','.
	                       $infooo['title'].'|'.PHP_EOL;
	                 
	                $result = fwrite($fp, $val);
	                if ($result === false) {
	                    die ("Error in write file in {$path}{$file_name}");
	                }	                
	           }
	       }
	    }
	    echo ("File has been created at {$path}{$file_name}".PHP_EOL);
	    fclose($fp);   
	}

	public static function openFile($path, $file_name, $mode = 'r+'){
	    $contents = '';
	    if (file_exists($path.$file_name)) {
	        $handle = fopen($path.$file_name, $mode);
	        $contents = fread($handle, filesize($path.$file_name));
	        fclose($handle);
	    } else {
	        $handle = fopen($path.$file_name, 'w+');
	        fclose($handle);
	    }
	    return $contents;
	}
	
	/**
	 * Returns the list of folders
	 * @param String $path -> Path to the folder
	 * @return multitype:Array -> List of folders
	 */
	public static function getFoldersList($path){
	    
	    $folders_list = array();
	    if (!is_dir($path)) {
	        die("Requested Path {$path} doesn't exist or can't be opned. Please check it! ".PHP_EOL);
        }else{
            // if the path doesn't have '/' then add to it
            if (substr($path, strlen($path) - 1, 1) != '/') {
                $path .= '/';
            }            
            $folders = glob($path.'*', GLOB_MARK);
            foreach ($folders as $folder) {
                array_push($folders_list, $folder);
            }            
        }
        
        return $folders_list;	        
	}
	
	
	
	
	
	
	
    /**
     * Find the duplicates and create a uniqu array
     * @param string array $hashed_records
     * @return array -> douplicates, singles
     */
	public static function findDoublicateFiles(array $hashed_records) {
	    $douplicates = [];
	    $singles = [];
	    $temp = [];
	    foreach ($hashed_records as $file => $hash){
	        $temp[$hash][] = $file;
	        unset($hashed_records[$file]);
	    }
	    
	    foreach ($temp as $hash => $file){
	        if (count($file)>1){
	           $douplicates[$hash] = $file;
	        }else{
	            $singles[$hash] = $file;
	        }
	    }
	     
	    return ['douplicates'=>$douplicates,
	            'singles'=>$singles
	           ];
	}
	
	/**
	 * finds all directories and folders and put them in an array
	 * @param String $dir -> Path to parent folder
	 * @return array $output -> Array(files, total number of records)
	 */
	public static function dirToArray($dir) {
	    $cdir = scandir($dir);
	    foreach ($cdir as $key => $value){
	        if (!in_array($value,array(".",".."))){
	            if (is_dir($dir . DIRECTORY_SEPARATOR . $value)){
	                self::dirToArray($dir . DIRECTORY_SEPARATOR . $value);
	            }else{
	                self::$result[$dir][] = $value;
	                self::$row_count = self::$row_count + 1;
	            }
	        }
	    }
	    $output = array(self::$result, self::$row_count);
	    return $output;
	}
	
	/**
	 * Calculates the hash of the array of folder and filenames 
	 * @param array $files -> folder=>filename
	 */
	public static function arrayToHash(array $files, $row_count){
	    $result = array();
	    $current_row = 1;
	    $total = $row_count;
	    foreach ($files as $folder => $filenames) {
    	    foreach ($filenames as $key => $filename) {
    	        $result[$folder.DIRECTORY_SEPARATOR.$filename] = self::calHash($folder, $filename);
    	        self::show_status($current_row, $total);
    	        $current_row = $current_row + 1;    	        
       	    }
	    }
	    return $result;
	}
	
	/**
	 * Calculate the hash of teh given file
	 * @param String $dir -> Folder
	 * @param String $filename -> filename
	 * @return string
	 */
	public static function calHash($dir, $filename) {
        $hash = md5_file($dir. DIRECTORY_SEPARATOR . $filename);   
	    return $hash;
	}

	/**
	 * Show a bar graph for commandline CLIs
	 *
	 * @param Array $info -> you will fine progress and verbosity status there to show or not show
	 * @param Int $done -> how much has been done, current status
	 * @param Int $total -> total of the records or items needs to be done
	 * @param Int $size -> How long do you want the progress bar to be
	 */
	public static function show_status($done, $total, $size=100) {
        static $start_time;
         
        // if we go over our bound, just ignore it
        if($done > $total) return;
         
        if(empty($start_time)) $start_time=time();
        $now = time();
         
        $perc=(double)($done/$total);
         
        $bar=floor($perc*$size);
         
        $status_bar="\r[";
        $status_bar.=str_repeat("=", $bar);
        if($bar<$size){
            $status_bar.=">";
            $status_bar.=str_repeat(" ", $size-$bar);
        } else {
            $status_bar.="=";
        }
         
        $disp=number_format($perc*100, 0);
         
        $status_bar.="] $disp%  $done/$total";
         
        $rate = ($now-$start_time)/$done;
        $left = $total - $done;
        $eta = round($rate * $left, 2);
         
        $elapsed = $now - $start_time;
         
        $status_bar.= " remaining: ".number_format($eta)." sec.  elapsed: ".number_format($elapsed)." sec.";
         
        echo "$status_bar  ";
         
        flush();
         
        // when done, send a newline
        if($done == $total) {
            echo "\n";
        }
	}
	
}