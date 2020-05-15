<?php
/*
 Read all files and folders
 calculate thier checksums
 Compare the checksums and generate the reports
 
 purge the dublicates
 
 tag the picturs. > in web portel
  
*/
require_once 'bootstrap.php';
$_CONTENT_PATH = $info['content-path'];;

// Shows the header on the command line
echo Functions::parseHeader();
    
$files = Functions::dirToArray($_CONTENT_PATH);

$raw_hashed  = Functions::arrayToHash($files[0], $files[1]);
 
$temp = Functions::findDoublicateFiles($raw_hashed);
$doublicates = $temp['douplicates'];
$singles = $temp['singles'];

print_r($singles);
print_r($doublicates);
die;    
    echo "Duplicates:".count($doublicates).PHP_EOL;
    echo "Total:".$files[1].PHP_EOL;
    










// if we want the list of moved folders look for content_maid_index.log
if ($command->getListDeletedFolders()) {
    $folders = Functions::getFoldersList($info['deleted-path']);
    if (!empty($folders )) {
        echo PHP_EOL.'Deleted log files are:'.PHP_EOL.'================================'.PHP_EOL;
        foreach ($folders as $folder) { 
            echo $folder.PHP_EOL; 
        }
        echo '================================'.PHP_EOL;
    }else{
        echo(PHP_EOL."No deleted folders have been found! ".PHP_EOL.PHP_EOL);
    }
}    

//***********************************************************************************************
// 1 Survey Folder ======================= [ read Survey folders ] =======================
//***********************************************************************************************
$survey = new Lib_FileHandler($command->getContentPath().'survey/');

echo (PHP_EOL.'1. Get all [ Survey ] folder lists and building QA report...'.PHP_EOL);

// Keeps the total size of the folders used reporting purpose
$total_survey_file_size = 0;

// The list of [Survey] folders in the passed on directory from command line
$survey_list = $survey->getDirectoryList();

// Number of folders in the passed on directory from command line
$stats ['survey']['t_folders']  = count($survey_list);

// Keeps the error message when trying to determin the content_id and version. In case if the folder name doesn't have #-# structure it should
// let us know and take it our of the folder array for futher investigation
$temp_error_message = '';

foreach ($survey_list as $key => $value) {

    $size                   = $survey->getFileSize($command->getContentPath().'survey/'.$value);
    $total_survey_file_size = $total_survey_file_size + $size;
    
    // fill in the QA report array
    $temp = explode("-", $value);
    
    // If the folder name structure was fine ( id-version )
    if (count($temp) == 2) {

        $survey_id = trim($temp[0]);
        $version   = trim($temp[1]);

        $QA_REPORT['survey'][$survey_id][$version]['type']                                   = 'survey';
        $QA_REPORT['survey'][$survey_id][$version]['id']                                     = $survey_id;
        $QA_REPORT['survey'][$survey_id][$version]['version']                                = $version;
        $QA_REPORT['survey'][$survey_id][$version]['size']                                   = $size;
        $QA_REPORT['survey'][$survey_id][$version]['found_in_asset_folder']                  = 'yes';
        $QA_REPORT['survey'][$survey_id][$version]['found_in_content_table']                 = 'no';
        $QA_REPORT['survey'][$survey_id][$version]['found_in_survey_table']                  = 'no';
        $QA_REPORT['survey'][$survey_id][$version]['content_container_id']                   = 0;
        $QA_REPORT['survey'][$survey_id][$version]['deleted']                                = '';
        $QA_REPORT['survey'][$survey_id][$version]['enrollment']                             = 0;
        $QA_REPORT['survey'][$survey_id][$version]['status']                                 = '';
        $QA_REPORT['survey'][$survey_id][$version]['title']                                 = '';
        
        $QA_REPORT['survey'][$survey_id][$version]['faculty']                                = 0;
        $QA_REPORT['survey'][$survey_id][$version]['course_wrapper_id']                      = 0;
        
    }else{
        unset($survey_list[$key]);
        $temp_error_message .= $temp_error_message . "Folder {$value} in [Survey] doesnt follow the normal naming structure". PHP_EOL;
    }

    Functions::show_status($verbose, $progress, $key+1, count($survey_list));
}

// Total folder size in the passed on directory from command line
$stats ['survey']['t_folder_size'] = $total_survey_file_size;

echo ("Done! {$stats ['survey']['t_folders']} Folders.").PHP_EOL;

// print the error message
echo ($temp_error_message . PHP_EOL);

//***********************************************************************************************
// 2 Content Folder ======================= [ read Content folders ] =======================
//***********************************************************************************************

$content = new Lib_FileHandler($command->getContentPath().'content/');

// Keeps the error message when trying to determin the content_id and version. In case if the folder name doesn't have #-# structure it should
// let us know and take it our of the folder array for futher investigation
$temp_error_message = '';

// Keeps the total size of the folders used reporting purpose
$total_content_file_size = 0;

// The list of [Content] folders in the passed on directory from command line
$content_list = $content->getDirectoryList();

// Number of folders in the passed on directory from command line
$stats ['content']['t_folders'] = count($content_list);

echo (PHP_EOL.'2. Get all [ Content ] folder lists and building QA report...'.PHP_EOL);
foreach ($content_list as $key => $value) {

    $size                    = $content->getFileSize($command->getContentPath().'content/'.$value);
    $total_content_file_size = $total_content_file_size + $size;
    
    // fill in the QA report array
    $temp = explode("-", $value);

    // If the folder name structure was fine ( id-version )
    if (count($temp) == 2) {

        $content_id = trim($temp[0]);
        $version    = trim($temp[1]);

        $QA_REPORT['content'][$content_id][$version]['type']                              = 'content';
        $QA_REPORT['content'][$content_id][$version]['id']                                = $content_id;
        $QA_REPORT['content'][$content_id][$version]['version']                           = $version;
        $QA_REPORT['content'][$content_id][$version]['size']                              = $size;
        $QA_REPORT['content'][$content_id][$version]['found_in_asset_folder']             = 'yes';
        $QA_REPORT['content'][$content_id][$version]['found_in_content_table']            = 'no';
        $QA_REPORT['content'][$content_id][$version]['found_in_survey_table']             = 'no';
        $QA_REPORT['content'][$content_id][$version]['content_container_id']              = 0;
        $QA_REPORT['content'][$content_id][$version]['deleted']                           = '';
        $QA_REPORT['content'][$content_id][$version]['enrollment']                        = 0;
        $QA_REPORT['content'][$content_id][$version]['status']                            = '';
        $QA_REPORT['content'][$content_id][$version]['title']                             = '';
        
        $QA_REPORT['content'][$content_id][$version]['faculty']                           = 0;
        $QA_REPORT['content'][$content_id][$version]['course_wrapper_id']                 = 0;
        
    }else{
        unset($content_list[$key]);
        $temp_error_message .= $temp_error_message . "Folder {$value} in [Content] doesnt follow the normal naming structure". PHP_EOL;
    }
    Functions::show_status($verbose, $progress, $key+1, count($content_list));
}

// Total folder size in the passed on directory from command line
$stats ['content']['t_folder_size'] = $total_content_file_size;

echo ("Done! {$stats ['content']['t_folders']} Folders.").PHP_EOL;

// print the error message
echo ($temp_error_message . PHP_EOL);

//*************************************************************************************************************************************
// 3 content all records =====================[ Read all content records from content table ]=====================
//*************************************************************************************************************************************
echo (PHP_EOL.'3. Read all content records from content table ');

// All content records from content table
$db_content = Functions::getAllContentRecords($info);

// total records returned from content table
$total_records = count($db_content);

// total records returned from content table
$stats ['content']['t_contents'] = $total_records;
echo ('===> [ '.count($db_content).' ] Records.').PHP_EOL;

foreach ($db_content as $key => $value) {
    
    // 3.1 ======================= [ Build the QA report array ] =======================

    // fill in the qa report array
    $content_id = trim($value['id']);
    $version    = trim($value['version']);
    
    // if content_id existed in the QA array
    if (isset($QA_REPORT['content'][$content_id])) {
    
        // if version existed in the QA array
        if (isset($QA_REPORT['content'][$content_id][$version])) {
            $QA_REPORT['content'][$content_id][$version]['title']                                  = trim($value['title']);
            $QA_REPORT['content'][$content_id][$version]['found_in_content_table']                 = 'yes';
            $QA_REPORT['content'][$content_id][$version]['content_container_id']                   = $value['content_container_id'];
            
            $wrapper_faculty = Functions::getCourseWrapperFaculty($info, $value['content_container_id']);
            if (count($wrapper_faculty)>1) {
                echo ('more than one record for course and faculty for content_container_id='.$value['content_container_id'].PHP_EOL);
                print_r($wrapper_faculty);
                die;
            }elseif(count($wrapper_faculty)==1){
                $QA_REPORT['content'][$content_id][$version]['faculty']                                 = $wrapper_faculty[0]['faculty'];
                $QA_REPORT['content'][$content_id][$version]['course_wrapper_id']                       = $wrapper_faculty[0]['course_wrapper'];
            }    
                        
        }else{
            $QA_REPORT['content'][$content_id][$version]['title']                                  = trim($value['title']);
            $QA_REPORT['content'][$content_id][$version]['found_in_content_table']                 = 'yes';
            $QA_REPORT['content'][$content_id][$version]['content_container_id']                   = $value['content_container_id'];
            
            $QA_REPORT['content'][$content_id][$version]['type']                                   = 'content';
            $QA_REPORT['content'][$content_id][$version]['id']                                     = $content_id;
            $QA_REPORT['content'][$content_id][$version]['version']                                = $version;
            $QA_REPORT['content'][$content_id][$version]['size']                                   = 0;
            $QA_REPORT['content'][$content_id][$version]['found_in_asset_folder']                  = 'no';
            $QA_REPORT['content'][$content_id][$version]['found_in_content_table']                 = 'yes';
            $QA_REPORT['content'][$content_id][$version]['found_in_survey_table']                  = 'no';
            $QA_REPORT['content'][$content_id][$version]['deleted']                                = '';
            $QA_REPORT['content'][$content_id][$version]['enrollment']                             = 0;
            $QA_REPORT['content'][$content_id][$version]['status']                                 = '';
        
            $QA_REPORT['content'][$content_id][$version]['faculty']                                = 0;
            $QA_REPORT['content'][$content_id][$version]['course_wrapper_id']                      = 0;
        }
        
    // create one record    
    }else {
        $QA_REPORT['content'][$content_id][$version]['title']                             = trim($value['title']);
        $QA_REPORT['content'][$content_id][$version]['found_in_content_table']            = 'yes';
        $QA_REPORT['content'][$content_id][$version]['content_container_id']              = $value['content_container_id'];
        
        $QA_REPORT['content'][$content_id][$version]['type']                              = 'content';
        $QA_REPORT['content'][$content_id][$version]['id']                                = $content_id;
        $QA_REPORT['content'][$content_id][$version]['version']                           = $version;
        $QA_REPORT['content'][$content_id][$version]['size']                              = 0;
        $QA_REPORT['content'][$content_id][$version]['found_in_asset_folder']             = 'no';
        $QA_REPORT['content'][$content_id][$version]['found_in_content_table']            = 'yes';
        $QA_REPORT['content'][$content_id][$version]['found_in_survey_table']             = 'no';
        $QA_REPORT['content'][$content_id][$version]['deleted']                           = '';
        $QA_REPORT['content'][$content_id][$version]['enrollment']                        = 0;
        $QA_REPORT['content'][$content_id][$version]['status']                            = '';
        $QA_REPORT['content'][$content_id][$version]['faculty']                           = 0;
        $QA_REPORT['content'][$content_id][$version]['course_wrapper_id']                 = 0;
        
        
        $wrapper_faculty = Functions::getCourseWrapperFaculty($info, $value['content_container_id']);
        if (count($wrapper_faculty)>1) {
            $QA_REPORT['content'][$content_id][$version]['faculty']                                 = 0;
            $QA_REPORT['content'][$content_id][$version]['course_wrapper_id']                       = 0;
            echo ('more than one record for course and faculty for content_container_id='.$value['content_container_id'].PHP_EOL);
            print_r($wrapper_faculty);
            die;
        }elseif(count($wrapper_faculty)==1){
            $QA_REPORT['content'][$content_id][$version]['faculty']                                 = $wrapper_faculty[0]['faculty'];
            $QA_REPORT['content'][$content_id][$version]['course_wrapper_id']                       = $wrapper_faculty[0]['course_wrapper'];
            
        // most probably live event
        }elseif(count($wrapper_faculty)==0){
            $QA_REPORT['content'][$content_id][$version]['faculty']                           = 0;
            $QA_REPORT['content'][$content_id][$version]['course_wrapper_id']                 = 0;
        }
    }
    Functions::show_status($verbose, $progress, $key+1, $total_records);
}

//*************************************************************************************************************************************
// 3.1  Content  =====================[ Find the faculty and course wrapper id for content ]=====================
//*************************************************************************************************************************************
echo (PHP_EOL.'3.1 Find the faculty and course wrapper id for content ');

// total records returned from content table
$total_records = count($QA_REPORT['content']);

// total records returned from content table
$stats ['content']['t_contents'] = $total_records;
echo ('===> [ '.count($QA_REPORT['content']).' ] Records.').PHP_EOL;
$counter = 0;

foreach ($QA_REPORT['content'] as $content_id => $value) {
    
    foreach ($value as $cvalue) {
        if ($cvalue['content_container_id']>0) {

            $wrapper_faculty = Functions::getCourseWrapperFaculty($info, $cvalue['content_container_id']);
                if (count($wrapper_faculty)>1) {
                    echo ('more than one record for course and faculty for content_container_id='.$cvalue['content_container_id'].PHP_EOL);
                    print_r($wrapper_faculty);
                    die;
                }elseif(count($wrapper_faculty)==1){
                    $QA_REPORT['content'][$content_id][$cvalue['version']]['faculty']            = $wrapper_faculty[0]['faculty'];
                    $QA_REPORT['content'][$content_id][$cvalue['version']]['course_wrapper_id']  = $wrapper_faculty[0]['course_wrapper'];
                }
            }
        }
    Functions::show_status($verbose, $progress, ++$counter, $total_records);
}

//**********************************************************************************************************************************************************
// 3.2 =====================[ Read all enrollments for content records from content_transcript table ]=====================
//**********************************************************************************************************************************************************

echo (PHP_EOL.'3.2 Finding the enrollments for each content record ...');

// total records returned from content table
$total_records = count($QA_REPORT['content']);

// total records returned from content table
$stats ['content']['t_contents'] = $total_records;
echo ('===> [ '.count($QA_REPORT['content']).' ] Records.').PHP_EOL;
$counter = 0;

foreach ($QA_REPORT['content'] as $content_id => $value) {

    foreach ($value as $cvalue) {

        if ($cvalue['content_container_id']>0) {
            $content_enrollments_1                = Functions::getEnrollmentsFromCourseEnrolment($info, $cvalue['content_container_id']);
            $enrollment_count_1                   = count($content_enrollments_1);
            
            $content_enrollments_2                = Functions::getEnrollmentsFromContentEnrollment($info, $cvalue['content_container_id']);
            $enrollment_count_2                   = count($content_enrollments_2);
            
            $total_count = $enrollment_count_1 + $enrollment_count_2;
            $db_content[$key]['enrollment_count'] = $enrollment_count_1 + $enrollment_count_2;

            // fill in the qa report array
            if ($total_count >0) {
                $QA_REPORT['content'][$content_id][$cvalue['version']]['enrollment'] = $total_count;
            }else {
                $QA_REPORT['content'][$content_id][$cvalue['version']]['enrollment'] = 0;
            }
        }
        Functions::show_status($verbose, $progress, ++$counter, $total_records );
    }
}

//*******************************************************************************************************************************
// 4 Survey db.survey all records =====================[ Read all survey records from survey table ]=====================
//*******************************************************************************************************************************
echo (PHP_EOL.'4. Read all survey records from survey table ');

// All content records from content table
$db_survey = Functions::getAllSurveyRecords($info);

// total records returned from survey table
$total_records = count($db_survey);

// total records returned from survey table
$stats ['survey']['t_contents'] = $total_records;
echo ('===> [ '.count($db_survey).' ] Records.').PHP_EOL;

foreach ($db_survey as $key => $value) {

    // fill in the qa report array
    $survey_id            = trim($value['id']);
    $version              = trim($value['version']);
    $content_container_id = $value['content_container_id'];
    
    // if survey_id existed in the QA array
    if (isset($QA_REPORT['survey'][$survey_id])) {

        // if version existed in the QA array
        if (isset($version, $QA_REPORT['survey'][$survey_id][$version])) {
            $QA_REPORT['survey'][$survey_id][$version]['title']                  = '';
            $QA_REPORT['survey'][$survey_id][$version]['found_in_survey_table']  = 'yes';
            $QA_REPORT['survey'][$survey_id][$version]['content_container_id']   = $content_container_id;
        
            $wrapper_faculty = Functions::getCourseWrapperFaculty($info, $content_container_id);
            if (count($wrapper_faculty)>1) {
                echo ('more than one record for course and faculty for content_container_id='.$value['content_container_id'].PHP_EOL);
                print_r($wrapper_faculty);
                die;
            }elseif(count($wrapper_faculty)==1){
                $QA_REPORT['survey'][$survey_id][$version]['faculty']                     = $wrapper_faculty[0]['faculty'];
                $QA_REPORT['survey'][$survey_id][$version]['course_wrapper_id']           = $wrapper_faculty[0]['course_wrapper'];
            }
        }else{
            $QA_REPORT['survey'][$survey_id][$version]['title']                             = '';
            
            $QA_REPORT['survey'][$survey_id][$version]['type']                              = 'survey';
            $QA_REPORT['survey'][$survey_id][$version]['id']                                = $survey_id;
            $QA_REPORT['survey'][$survey_id][$version]['version']                           = $version;
            $QA_REPORT['survey'][$survey_id][$version]['size']                              = 0;
            $QA_REPORT['survey'][$survey_id][$version]['found_in_asset_folder']             = 'no';
            $QA_REPORT['survey'][$survey_id][$version]['found_in_content_table']            = 'no';
            $QA_REPORT['survey'][$survey_id][$version]['found_in_survey_table']             = 'yes';
            $QA_REPORT['survey'][$survey_id][$version]['content_container_id']              = $value['content_container_id'];
            $QA_REPORT['survey'][$survey_id][$version]['deleted']                           = '';
            $QA_REPORT['survey'][$survey_id][$version]['enrollment']                        = 0;
            $QA_REPORT['survey'][$survey_id][$version]['status']                            = '';

            $QA_REPORT['survey'][$survey_id][$version]['faculty']                           = 0;
            $QA_REPORT['survey'][$survey_id][$version]['course_wrapper_id']                 = 0;            
        }

    }else {
        $QA_REPORT['survey'][$survey_id][$version]['title']                             = '';
        $QA_REPORT['survey'][$survey_id][$version]['type']                              = 'survey';
        $QA_REPORT['survey'][$survey_id][$version]['id']                                = $survey_id;
        $QA_REPORT['survey'][$survey_id][$version]['version']                           = $version;        
        
        $QA_REPORT['survey'][$survey_id][$version]['size']                              = 0;
        $QA_REPORT['survey'][$survey_id][$version]['found_in_asset_folder']             = 'no';
        $QA_REPORT['survey'][$survey_id][$version]['found_in_content_table']            = 'no';
        $QA_REPORT['survey'][$survey_id][$version]['found_in_survey_table']             = 'yes';
        $QA_REPORT['survey'][$survey_id][$version]['content_container_id']              = $value['content_container_id'];
        $QA_REPORT['survey'][$survey_id][$version]['deleted']                           = '';
        $QA_REPORT['survey'][$survey_id][$version]['enrollment']                        = 0;
        $QA_REPORT['survey'][$survey_id][$version]['status']                            = '';
        
        $wrapper_faculty = Functions::getCourseWrapperFaculty($info, $value['content_container_id']);
        if (count($wrapper_faculty)>1) {
            $QA_REPORT['survey'][$survey_id][$version]['faculty']                       = 0;
            $QA_REPORT['survey'][$survey_id][$version]['course_wrapper_id']             = 0;
            echo ('more than one record for course and faculty for content_container_id = '.$value['content_container_id'].PHP_EOL);
            print_r($wrapper_faculty);
            die;
        }elseif(count($wrapper_faculty)==1){
            $QA_REPORT['survey'][$survey_id][$version]['faculty']                       = $wrapper_faculty[0]['faculty'];
            $QA_REPORT['survey'][$survey_id][$version]['course_wrapper_id']             = $wrapper_faculty[0]['course_wrapper'];
        
            // most probably live event
        }elseif(count($wrapper_faculty)==0){
            $QA_REPORT['survey'][$survey_id][$version]['faculty']                       = 0;
            $QA_REPORT['survey'][$survey_id][$version]['course_wrapper_id']             = 0;
        }
    }
    Functions::show_status($verbose, $progress, $key+1, $total_records);
}

//*************************************************************************************************************************************
// 4.1  Survey  =====================[ Find the faculty and course wrapper id for survey ]=====================
//*************************************************************************************************************************************
echo (PHP_EOL.'4.1 Find the faculty and course wrapper id for survey ');

// total records returned from content table
$total_records = count($QA_REPORT['survey']);

// total records returned from content table
$stats ['survey']['t_contents'] = $total_records;
echo ('===> [ '.count($QA_REPORT['survey']).' ] Records.').PHP_EOL;
$counter = 0;

foreach ($QA_REPORT['survey'] as $survey_id => $value) {

    foreach ($value as $cvalue) {
        if ($cvalue['content_container_id']>0) {

            $wrapper_faculty = Functions::getCourseWrapperFaculty($info, $cvalue['content_container_id']);
            if (count($wrapper_faculty)>1) {
                echo ('more than one record for course and faculty for content_container_id='.$cvalue['content_container_id'].PHP_EOL);
                print_r($wrapper_faculty);
                die;
            }elseif(count($wrapper_faculty)==1){
                $QA_REPORT['survey'][$survey_id][$cvalue['version']]['faculty']            = $wrapper_faculty[0]['faculty'];
                $QA_REPORT['survey'][$survey_id][$cvalue['version']]['course_wrapper_id']  = $wrapper_faculty[0]['course_wrapper'];
            }
        }
    }
    Functions::show_status($verbose, $progress, ++$counter, $total_records);
}

//**********************************************************************************************************************************************************
// 4.2 =====================[ Read all enrollments for survey records  ]=====================
//**********************************************************************************************************************************************************

echo (PHP_EOL.'4.2 Finding the enrollments for each survey record ...');

// total records returned from survey report
$total_records = count($QA_REPORT['survey']);

// total records returned from survey report
$stats ['survey']['t_contents'] = $total_records;
echo ('===> [ '.count($QA_REPORT['survey']).' ] Records.').PHP_EOL;
$counter = 0;

foreach ($QA_REPORT['survey'] as $content_id => $value) {

    foreach ($value as $cvalue) {

        if ($cvalue['content_container_id']>0) {
            $content_enrollments_1 = Functions::getEnrollmentsFromCourseEnrolment($info, $cvalue['content_container_id']);
            $enrollment_count_1    = count($content_enrollments_1);
            
            $content_enrollments_2 = Functions::getEnrollmentsFromContentEnrollment($info, $cvalue['content_container_id']);
            $enrollment_count_2    = count($content_enrollments_2);

            $total_count = $enrollment_count_1 + $enrollment_count_2;
            $db_content[$key]['enrollment_count'] = $total_count;
            
            // fill in the qa report array
            if ($total_count>0) {
                $QA_REPORT['survey'][$content_id][$cvalue['version']]['enrollment'] = $total_count;
            }else {
                $QA_REPORT['survey'][$content_id][$cvalue['version']]['enrollment'] = 0;
            }
        }
        Functions::show_status($verbose, $progress, ++$counter, $total_records );
    }
}

$moved = ''; // log the moved folders
// 8 =====================[ Delete Surveys ]=====================
echo (PHP_EOL.PHP_EOL.'5. Deleting the survey folders...').PHP_EOL;
$key = 0;
$total_deleted_size = $total_deleted = 0;
if(!Lib_FileHandler::buildFolder($command->getDeletedPath(), $right_now)){
    echo ($command->getDeletedPath().$right_now." Can not be created so survey folders cant be deleted!".PHP_EOL);
    
}elseif(!Lib_FileHandler::buildFolder($command->getDeletedPath().'/'.$right_now, 'survey')){
    echo ($command->getDeletedPath().$right_now.'survey'." Can not be created so survey folders cant be deleted!".PHP_EOL);
}else{

    foreach ($QA_REPORT['survey'] as $id => $data) {
        foreach ($data as $version => $info) {
            // If only exist in the asset folder and nowhere in db there log it and delete it.
            if( $info['found_in_asset_folder']=='yes' &&
                $info['found_in_survey_table']=='no' &&
                $info['content_container_id']==0 &&
                $info['enrollment']==0){
                $folder_name = $info['id'].'-'.$info['version'];

                // if this is no dry-run and you just want to move the files
                if ($command->getDryRun()==FALSE && !$command->getPurge()) {
                    Lib_FileHandler::deleteDir($command->getContentPath(),'survey', $folder_name.'/', $command->getDeletedPath(), $right_now);
                    
                // if this is no dry-run and you want to physically delete the files
                }elseif($command->getDryRun()==FALSE && $command->getPurge()){
                    
                    Lib_FileHandler::deleteDir($command->getContentPath(),'survey', $folder_name.'/', $command->getDeletedPath(), $right_now, $command->getPurge());
                }
                
                $QA_REPORT['survey'][$id][$version]['deleted'] = 'yes';
                $total_deleted_size                            = $total_deleted_size + $info['size'];
                $total_deleted                                 = $total_deleted +1;
                $moved .= 'survey|'.$command->getContentPath().'survey/|'.$folder_name.'/'.PHP_EOL;
            }
            
            if( ($info['found_in_asset_folder']=='no' && $info['found_in_survey_table']=='yes') ||
                ($info['found_in_asset_folder']=='no' && $info['content_container_id']<>'0') ||
                ($info['found_in_asset_folder']=='no' && $info['enrollment']<>'0')){
                // orphan record
                $QA_REPORT['survey'][$id][$version]['status'] = 'orphan';
            }
        }
        ++$key;
        Functions::show_status($verbose, $progress, $key+1, count($QA_REPORT['survey']));
    }
}

$stats ['survey']['t_folder_deleted_size'] = $total_deleted_size;
$stats ['survey']['t_folder_deleted']      = $total_deleted;

// 9 =====================[ Delete Content ]=====================
echo (PHP_EOL.PHP_EOL.'6. Deleting the content folders...').PHP_EOL;
$key = 0;
$total_deleted_size = $total_deleted = 0;

if(!Lib_FileHandler::buildFolder($command->getDeletedPath().'/'.$right_now, 'content')){
    echo ($command->getDeletedPath().$right_now.'content'." Can not be created so content folders cant be deleted!".PHP_EOL);
}else{
    foreach ($QA_REPORT['content'] as $id => $data) {
        foreach ($data as $version => $info) {
            // If only exist in the asset folder and nowhere in db there log it and delete it.
            if( $info['found_in_asset_folder']=='yes' && 
                $info['found_in_content_table']=='no' && 
                $info['content_container_id']=='0' && 
                $info['enrollment']=='0'){

                $folder_name =$info['id'].'-'.$info['version']; 

                // if this is no dry-run and you just want to move the files
                if ($command->getDryRun()==FALSE && !$command->getPurge()) {
                    Lib_FileHandler::deleteDir($command->getContentPath(),'content', $folder_name.'/', $command->getDeletedPath(), $right_now);                    
                    
                // if this is no dry-run and you want to physically delete the files
                }elseif($command->getDryRun()==FALSE && $command->getPurge()){
                    
                    Lib_FileHandler::deleteDir($command->getContentPath(),'content', $folder_name.'/', $command->getDeletedPath(), $right_now, $command->getPurge());
                }
                
                $QA_REPORT['content'][$id][$version]['deleted'] = 'yes';
                $total_deleted_size = $total_deleted_size + $info['size'];
                $total_deleted = $total_deleted +1;
                $moved .= 'content|'.$command->getContentPath().'content/|'.$folder_name.'/'.PHP_EOL;
                
            }
            
            if( ($info['found_in_asset_folder']=='no' && $info['found_in_content_table']=='yes') ||
                ($info['found_in_asset_folder']=='no' && $info['content_container_id']=='0') ||
                ($info['found_in_asset_folder']=='no' && $info['enrollment']=='0')){
                // orphan record
                $QA_REPORT['content'][$id][$version]['status'] = 'orphan';
            }
            
        }
        ++$key;
        Functions::show_status($verbose, $progress, $key+1, count($QA_REPORT['content']));
    }
}

$stats ['content']['t_folder_deleted_size'] = $total_deleted_size;
$stats ['content']['t_folder_deleted']      = $total_deleted;

echo Functions::parseStats($stats, 'survey');
echo Functions::parseStats($stats, 'content');

Functions::createCSVFile($QA_REPORT, $report_path, $report_name);
