<?php
class Command{
	private $command = array();
	
	private $_HELP           = FALSE;                               // Shows the help
	private $_VERSION        = '1.0';                               // Version
	private $_VERBOSE        = FALSE;                               // Puts the output on the screen
	private $_DRYRUN         = FALSE;                               // Flag to run a dry run test
	private $_RENAME         = FALSE;                               // Rename the files
	private $_CONTENT_PATH   = '/home/idsmaster/Desktop/PICS/';  // The location to the files
	private $_REPORT_PATH    = '/home/idsmaster/Desktop/pictest/';  // The location to the temp files 
	private $_META_DATA      = 'meta_data.csv';                     // Meta Data filename
	private $_NEW            = '';                                  // path and folder name for the new location to copy the files to
	private $_PURGE          = FALSE;                               // Delete the files physically and not copy somewhere else
	
	/**
	 * Gets the array of arguments and set the Command variables
	 * @param $commands -> array of arguments
	 */
	public function __construct($commands) {
		$this->setCommands($commands);
	}
	
	/**
	 * Returns the value of the requested Command variable 
	 * @return String
	 */
	public function getCommand(){
		return $this->command;
	}
	
	/**
	 * Sets the Command variables 
	 * @param $commands -> array of arguments
	 */
	private function setCommand($command){
		$this->command = $command;
	}
	
	/**
	 * Sets the Command values  
	 *
	 */
	public function setCommands($com){
		foreach ($com as $k=>$v) {
			if($k>0){
				$temp = array();
				$temp = explode(":", $v);
				switch (trim($temp[0])) {
					case '-help':
					case '-h':
					    $this->setHelp();
					    break;
					
					case '--verbose':
					case '-v':
					    $this->setVerbose();
					    break;
					
					case '--dry-run':
					case '-d':
						$this->setDryRun();
					    break;
					    
					case '--rename':
					case '-r':
					    $this->setRename();
					    break;
					    
//=========================================================
				    case '--new':
				        $this->checkArgument($temp, 'new');
				        break;
				        
				    case '--content-path':
				        $this->checkArgument($temp, 'content-path');
				        break;
				        
				    case '--report-path':
				        $this->checkArgument($temp, 'report-path');
				        break;
				        
			        case '--purge':
			        case '-P':    
			            $this->setPurge();
			            break;

//=========================================================					
					default:
						    $this->setHelp();
					break;
				}
			}	
		}
	}

	private function checkArgument($temp, $msg_source) {
	    if(count($temp)==1){ // only key exist and not an argument - Look for warning message
			die(Functions::getSysMsgSource($msg_source));
		}else{
		    if(trim($temp[1])==''){
		        // if the argument is empty - Look for the error message
				die(Functions::getSysMsgSource($msg_source, 'error'));	
			}else{
				switch ($msg_source) {
				    case 'new':
				        $this->setNew(trim($temp[1]));
				        break;
				        
				    case 'content-path':
				        $this->setContentPath(trim($temp[1]));
				        break;
				        
					case 'report-path':
					    $this->setReportPath(trim($temp[1]));
					    break;
					    
					case 'purge':
	                    $this->setPurge(trim($temp[1]));
	                    break;

				}
			}	
		}
	}	
	
//------------------------------------ GENERAL -------------------------------------------------------	

// ------- Help ---------------	
	/**
	 * Sets the Help flag
	 * @param flag-> Boolean (True/False)
	 *
	 */	
	private function setHelp($flag=TRUE){ $this->_HELP = $flag;}
	
	/**
	 * Returns the Help value
	 * 
	 * @return Boolean
	 */
	public function getHelp(){ return $this->_HELP;}
	
// ------- Verbose ---------------
	/**
	 * Sets the Verbose flag
	 * @param flag-> Boolean (True/False)
	 *
	 */	
	private function setVerbose($flag=TRUE){ $this->_VERBOSE = $flag;}
	
	/**
	 * Returns the Verbose value
	 * 
	 * @return Boolean
	 */
	public function getVerbose(){ return $this->_VERBOSE;}

// ------- Dry-Run ---------------
  /**
	* Sets the Dryrun flag
	* @param flag-> Boolean (True/False)
	*
	*/
    private function setDryRun($flag=TRUE){ $this->_DRYRUN = $flag;}
	  
  /**
    * Returns the Dryrun value
	*
	* @return Boolean
	*/
	public function getDryRun(){ return $this->_DRYRUN;}

// ------- Purge ---------------
  /**
	* Sets the Purge flag
	* @param flag-> Boolean (True/False)
	*
	*/
	private function setPurge($flag=TRUE){ $this->_PURGE = $flag;}
	  
   /**
	 * Returns the Purge value
	 *
	 * @return Boolean
	 */
	public function getPurge(){ return $this->_PURGE;}
	
//------- Rename ---------------
   /**
	 * Sets the Rename
	 * @param rename-> Boolean
	 *
	 */
	private function setRename($rename=TRUE){ $this->_RENAME = $rename;}
	  
  /**
	* Returns the Rename value
	*
	* @return String
	*/
	public function getRename(){ return $this->_RENAME;}
	
//------- Meta Data ---------------
	/**
	 * Returns the Meta Data file name
	 *
	 * @return String
	 */
	public function getMetaData(){ return $this->_META_DATA;}
	
//------- Version ---------------
	
  /**
	* Returns the Version value
	*
	* @return String
	*/
    public function getVersion(){ return $this->_VERSION;}
	  
// ------- New Path ---------------
    /**
     * Sets the New Path
     * @param new-> String
     *
     */
    private function setNew($new){
        if (substr($new, -1)=='/'){
            $new = substr($new, 0, -1);
        }
        
        $this->_NEW = $new;
    }
    
    /**
     * Returns the New value
     *
     * @return String
     */
    public function getNew(){
        if (substr($this->_NEW, -1)=='/'){
            return substr($this->_NEW, 0, -1);
        }
        
        return $this->_NEW;
    }
    
// ------- Content Path ---------------
  /**
   * Sets the Content Path 
   * @param content_path-> String
   *
   */
    private function setContentPath($content_path){
        if (substr($content_path, -1)=='/'){
            $content_path = substr($content_path, 0, -1);
        }
        
        $this->_CONTENT_PATH = $content_path;
    }
	  
  /**
   * Returns the Content Path value
   *
   * @return String
   */
    public function getContentPath(){
        if (substr($this->_CONTENT_PATH, -1)=='/'){
            return substr($this->_CONTENT_PATH, 0, -1);
        }
        
        return $this->_CONTENT_PATH; 
    }
   
// ------- Report Path ---------------
   /**
    * Sets the report Path
    * @param path-> String
    *
    */
    private function setReportPath($path){
        if (substr($path, -1)<>'/'){
            $path .='/';
        } 
        $this->_REPORT_PATH = $path;
    }
    

    /**
     * Returns the Report Path value
     *
     * @return String
     */
    public function getReportPath(){return $this->_REPORT_PATH; }
}