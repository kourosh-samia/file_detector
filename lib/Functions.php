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
	public static function parseHeader($info){
	    $output = "-----------------------------------------------".PHP_EOL;
	    $output .= " Author: Kourosh Samia - OCT 2017              ".PHP_EOL;
	    $output .= " File duplicate finder Version ".$info['version']."             ".PHP_EOL;
	    $output .= "-----------------------------------------------".PHP_EOL;
	    return $output;
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
	public static function fodrmatOutput($data, $space, $filler_char=' ', $right_left_both=STR_PAD_RIGHT, $return_char = FALSE){
		$temp = str_pad($data, $space, $filler_char, $right_left_both);
		if($return_char){
			return $temp.PHP_EOL;
		}else{
			return $temp;
		}
	}
	
	public static function outputStats($before, $after){
	    echo PHP_EOL.PHP_EOL.'Stats:'.PHP_EOL.'======================================='.PHP_EOL;
	    echo '             Files        Sizes'.PHP_EOL;
	    echo "            {$before['total_files']}      {$before['total_sizes']}".PHP_EOL;
	    echo "Singlets    {$before['total_singles_files']}       {$before['total_singles_size']} (".self::filesize_formatted($before['total_singles_size']).")".PHP_EOL;
	    echo "Duplicates  {$before['total_duplicates_files']}     {$before['total_duplicates_size']} (".self::filesize_formatted($before['total_duplicates_size']).")".PHP_EOL;
	    echo '---------------------------------------'.PHP_EOL;
	    echo "Purged      {$after['total_purged_files']}      {$after['total_purged_file_sizes']} (".self::filesize_formatted($after['total_purged_file_sizes']).")".PHP_EOL;
	    echo '======================================='.PHP_EOL;
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

	public static function dispatch($info, $files) {
	    $stats = [];
	    $duplicates = $files['duplicates']; 
	    $dryrun = $info['dryrun'];
	    $purge  = $info['purge'];
//	    $new    = $info['new'];
//	    $rename = $info['rename'];
	    
	    if ($purge) {
	        echo '> Purgging...'.PHP_EOL;
	        $stats = self::purge($duplicates, $dryrun);
	    }
	    return $stats;
	    
	    
	    
	    
	    
	    
	    
	    
	    
	    
	    
	    
// 	    if ($new) {
// 	        if (!$dryrun) {
// 	            // create distination folder
// 	            if ($rename) {
// 	                //for singles
// 	                // create rename file name
// 	                self::copy();
// 	                if ($purge) {
// 	                    self::purge();
// 	                }
	                
// 	                //for duplicates
// 	                // create rename file name
// 	                self::copy();
// 	                if ($purge) {
// 	                    self::purge();
// 	                }
	                
// 	            }else{
// 	                //for singles
// 	                self::copy();
// 	                if ($purge) {
// 	                    self::purge();
// 	                }
	                
// 	                //for duplicates
// 	                self::copy();
// 	                if ($purge) {
// 	                    self::purge();
// 	                }
// 	            }
	            
// 	        }else{
// 	            // calculate stats for copy and purged files
// 	            self::purge();
// 	        }
	        
// 	    }else{
// 	        if (!$dryrun) {
// 	            self::purge();
// 	            // get purged stats
// 	        }else{
//                 // get purged stats	            
// 	        }
	            
// 	    }
	    
	}

	/**
	 * Purge the duplicate files and returns the status of number of deleted file and size of them
	 * @param array $duplicates
	 * @param integer $dryrun
	 * @return array
	 */
	public static function purge($duplicates, $dryrun) {
	    $stats['total_purged_files']      = 0;
	    $stats['total_purged_file_sizes'] = 0;
	    
	    if ($dryrun) {
	        foreach ($duplicates as $hash => $files) {
	            if(count($files)>1){
	                unset($files[0]);
                    foreach ($files as $file) {
                        $stats['total_purged_files']      = $stats['total_purged_files'] + 1;
                        $stats['total_purged_file_sizes'] = $stats['total_purged_file_sizes'] + $file['size'];
                    }    	                
	            }
	        }
	    }else{
	        foreach ($duplicates as $hash => $files) {
	            if(count($files)>1){

	                unset($files[0]);
	                foreach ($files as $file) {
	                
	                    $stats['total_purged_files']      = $stats['total_purged_files'] + 1;
	                    $stats['total_purged_file_sizes'] = $stats['total_purged_file_sizes'] + $file['size'];
	                    try {
	                        unlink($file['dirname'].'/'.$file['basename']);
	                    } catch (Exception $e) {
	                        echo 'Caught exception: ',  $e->getMessage(), "\n";
	                    }
	                }
	            }
	        }
	    }
	    return $stats;
	}
	
	/**
	 * Find the duplicates and create a uniqu array
	 * @param string array $hashed_records
	 * @return array -> douplicates, singles
	 */
	public static function findDuplicatesFiles(array $hashed_records) {
	    echo ('> Finding Duplicates...').PHP_EOL;
	    $stats['total_files'] = 0;
	    $stats['total_sizes'] = 0;
	    $stats['total_singles_files'] = 0;
	    $stats['total_duplicates_files'] = 0;
	    $stats['total_duplicates_size'] = 0;
	    $stats['total_singles_size'] = 0;
	    $douplicates = [];
	    $singles = [];
	    $temp = [];
	    foreach ($hashed_records as $file => $hash){
	        $stats['total_files'] = $stats['total_files'] + 1;
	        $fileInfo = self::getFileInfo($file);
	        $stats['total_sizes'] = $stats['total_sizes'] + $fileInfo['size']; 
            $temp[$hash][] = $fileInfo;
	        unset($hashed_records[$file]);
	    }
	    
	    foreach ($temp as $hash => $file){
	        if (count($file)>1){
	            $stats['total_duplicates_files'] = $stats['total_duplicates_files'] + count($file);
	            $douplicates[$hash] = $file;
	        }else{
	            $stats['total_singles_files'] = $stats['total_singles_files'] + count($file);
	            $singles[$hash] = $file;
	        }
	    }
	    
	    foreach ($douplicates as $key => $value){
	        foreach ($value as $k => $v){
	            $stats['total_duplicates_size'] = $stats['total_duplicates_size'] + $v['size'];
	        }
	    }
	    
	    foreach ($singles as $key => $value){
	        foreach ($value as $k => $v){
	            $stats['total_singles_size'] = $stats['total_singles_size'] + $v['size'];
	        }
	    }
	    
	    return ['duplicates' => $douplicates,
	            'singles'    => $singles,
	            'stats'      => $stats,
	           ];
	}
	
	/**
	 * Converst the size to a nicly formartted units
	 * @param int $size
	 * @return string
	 */
	public static function filesize_formatted($size){
	    $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	    $power = $size > 0 ? floor(log($size, 1024)) : 0;
	    return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
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
	public static function arrayToHash(array $files, $row_count, $verbose){
	    echo ('> Reading all files...').PHP_EOL;
	    $total_files = 0;
	    $total_sizes = 0;
	    $result = array();
	    $current_row = 1;
	    $total = $row_count;
	    foreach ($files as $folder => $filenames) {
    	    foreach ($filenames as $key => $filename) {
    	        $result[$folder.DIRECTORY_SEPARATOR.$filename] = self::calHash($folder, $filename);
    	        ($verbose)?self::show_status($current_row, $total):'';
//    	        self::show_status($current_row, $total);
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
}
