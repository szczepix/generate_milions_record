<?php

const DEBUG = TRUE;

const DB_NAME = "praktykanci";
const DB_HOST = "localhost";
const DB_USER = "user-praktykanci";
const DB_PASS = "praktykanci";

$timeGenerateLetter = null;
$timeGenerateAge = null;


// ???????????????
$fileCSV = "";
$startLoop = null;
$stopLoop = null;

function debugMode()
{
    if (DEBUG)
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        echo "DEBUG: TRUE\n";
    }
}

function chcekMemory()
{
    
}

function chcekTask()
{
    
}

function checkMountRamDisk()
{
    
}

function generateRangomString($length)
{
    global $timeGenerateLetter;
    $startTime = microtime(true);

    // test 0 - one milion = 1.3495995998383
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $shuffle = str_shuffle($characters);
    $randomText = substr($shuffle, 0, $length);
    $result = ucfirst($randomText);

    $endTime = microtime(true);
    $timeGenerateLetter += ($endTime - $startTime);

    return $result;
}

function generateRangomAge()
{
    global $timeGenerateAge;
    $startTime = microtime(true);

    $result = mt_rand(1, 99);

    $endTime = microtime(true);
    $timeGenerateAge += ($endTime - $startTime);

    return $result;
}

function cleanDataTable()
{
    
}

function createDataFile()
{
    
}

function insertSingleDataFile()
{
    
}

function insertAllDataFiles()
{
    
}

echo "<pre>";

debugMode();

for($i=0;$i<100;$i++)
{
    echo generateRangomString(15) . "\n";
    echo generateRangomAge() . "\n";
}

echo "timeGenerateLetter: " . $timeGenerateLetter . "\n";
echo "timeGenerateAge: " . $timeGenerateAge . "\n";