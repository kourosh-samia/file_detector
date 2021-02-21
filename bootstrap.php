<?php
error_reporting(E_ALL);
//error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');
require_once 'lib/FileHandler.php';
require_once 'lib/Command.php';
require_once 'lib/Functions.php';
    
$command = new Command($argv);

// Load the Help manual on the console
if ($command->getHelp()) { die(file_get_contents('../dispatch/fd.man').PHP_EOL);}

$info = [
        'content-path'         => $command->getContentPath(),
        'report-path'          => $command->getReportPath(),
        'meta-data'            => $command->getMetaData(),
        'duplicates'           => $command->getDuplicates(),
        'dryrun'               => $command->getDryRun(),
        'verbose'              => $command->getVerbose(),
        'progress'             => $command->getProgress(),
        'version'              => $command->getVersion(),
        'purge'                => $command->getPurge()
];
    
//print_r($info);