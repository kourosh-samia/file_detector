<?php
/*
 Read all files and folders
 calculate thier checksums
 Compare the checksums and generate the reports
 
 purge the dublicates
 
 tag the picturs. > in web portel
  
*/
global $info ;

require_once '../bootstrap.php';
$_REPORT_PATH  = $info['report-path'];
$_META_DATA    = $info['meta-data'];





// Shows the header on the command line
echo Functions::parseHeader($info);

// reads all of the files and finds the paths and names and puts them in an array 
$files = Functions::dirToArray($info['content-path']);

// Create the meta-data file and write the files and folders in json format in it`
//$data = Functions::dataToFile($_REPORT_PATH, $_META_DATA, $files);
$data = $files;

// Read the meta-data file, calculate hash, extensions
$raw_hashed  = Functions::arrayToHash($data[0], $data[1],$info['verbose']);

//Based on the hash of the files from previous function, everything gets sorted in to array of hash->path+filenames
$temp = Functions::findDuplicatesFiles($raw_hashed);

// Call proper actions
$stats_after = Functions::dispatch($info, $temp);

Functions::outputStats($temp['stats'], $stats_after);
