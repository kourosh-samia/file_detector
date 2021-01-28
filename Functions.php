<?php
class Functions {
	private $config;
	static private $max_exist_status = 0;
	static private $result = array();
	static private $row_count = 0;
	
	public function __construct(){
	    $this->setConfig(Error_messages::getEMessages());
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

  /**
	* This function will try to create a file. If the file exist, 
	* it will return the content along with the file size. Otherwise, 
	* It will create an empty file and return an empty string.
	* 
	* @param string $path -> Path to the file
	* @param string $file_name
	* @param string $data -> array of data
	* @return string
	*/
	public static function dataToFile($path, $file_name, $data){
	    $mode = 'w+';
	    $contents = [];
        try {
            $handle = fopen($path.$file_name, $mode);
            fwrite($handle, json_encode($data));
            fclose($handle);
            $handle = fopen($path.$file_name, 'r+');
            
            if(filesize($path.$file_name) > 0){
                $contents = fread($handle, filesize($path.$file_name));
            }
            fclose($handle);
            
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        
        return json_decode($contents, true);
	}
	
//==============================================================================

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
	        $fileInfo = self::getFileInfo($file);
            $temp[$hash][] = $fileInfo;
	        unset($hashed_records[$file]);
//print_r($temp);die;
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
	                self::$result[$dir][]  = $value;
	                self::$row_count = self::$row_count + 1;
	                
	                
	                
// 	                self::$result[$dir][$value]['filename']  = $value;
// 	                self::$result[$dir][$value]['hash']      = self::calHash($dir, $value);
	                
// 	                $fileInfo = self::getFileInfo($dir, $value);
// 	                self::$result[$dir][$value]['extension'] = $fileInfo['extension'];
// 	                print_r(self::$result);
	                
	                
	                
	            }
	        }
	    }
	    $output = array(self::$result, self::$row_count);
	    return $output;
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
	 * Finds the path info to a folder+ filename
	 * @param String $file -> path+filename
	 * @return string
	 */
	public static function getFileInfo($file) {
	    $file_info = [];
	    $pathInfo = pathinfo($file);
	    foreach ($pathInfo as $k=>$v) {
	        $file_info[$k]=$v;
	    }
	    $file_info['size'] = filesize($file);
    
	    return $file_info;
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
//==============================================================================
	
}
