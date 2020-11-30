<?php
error_reporting(E_ALL);
//error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');
require_once 'lib/FileHandler.php';
require_once 'Command.php';
require_once 'Functions.php';
$info = [];
    
$command = new Command($argv);

// Load the Help manual on the console
if ($command->getHelp()) { die(file_get_contents('help.txt').PHP_EOL);}

$info = [
        'content-path'         => $command->getContentPath(),
        'report-path'          => $command->getReportPath(),
        'dryrun'               => $command->getDryRun(),
        'verbose'              => $command->getVerbose(),
        'progress'             => $command->getProgress(),
        'version_flag'         => $command->getVersionFlag(),
        'version'              => $command->getVersion()
];
    
print_r($info);