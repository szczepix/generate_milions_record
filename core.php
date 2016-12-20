<?php

const DEBUG = true;

const DB_NAME = "praktykanci";
const DB_HOST = "localhost";
const DB_USER = "user-praktykanci";
const DB_PASS = "praktykanci";

$fileRecordsParam = array(
    'file_name'      => "file",
    'file_extension' => ".csv",
);

$elapsedTimes = array(
    'script_running'  => 0,
    'generate_letter' => 0,
    'generate_age'    => 0,
    'sql_bulk'        => 0,
    'file_create'     => 0,
);

function settingDebugMode()
{
    if (DEBUG)
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        echo "<pre>";
        echo "DEBUG: true\n\n";
    }
    else
    {
        echo "<pre>";
    }
}

function getMemoryUsage()
{
    return memory_get_peak_usage(true) / 1024 / 1024; // MiB
}

function getTaskId()
{
    return isset($_GET["task"]) ? $_GET["task"] : -1;
}

function getBulkId()
{
    return isset($_GET["bulk"]) ? $_GET["bulk"] : 0;
}

function checkMountRamDisk()
{
    // TODO: find and write linux cmd check mount
}

function printDebugInfo($taskId, $elapsedTimes, $round = 4)
{

    $scriptRunningTimeStr  = number_format($elapsedTimes['script_running'], $round, '.', ' ');
    $generateLetterTimeStr = number_format($elapsedTimes['generate_letter'], $round, '.', ' ');
    $generateAgeTimeStr    = number_format($elapsedTimes['generate_age'], $round, '.', ' ');
    $sqlBulkTimeStr        = number_format($elapsedTimes['sql_bulk'], $round, '.', ' ');
    $fileCreateTime        = $elapsedTimes['file_create'] - $elapsedTimes['generate_letter'] - $elapsedTimes['generate_age'];
    $fileCreateTimeStr     = number_format($fileCreateTime, $round, '.', ' ');
    if ($elapsedTimes['script_running'] > 0)
    {
        $sqlTimePercent  = ($elapsedTimes['sql_bulk'] * 100) / $elapsedTimes['script_running'];
        $randTimePercent = ((
                $elapsedTimes['generate_letter'] + $elapsedTimes['generate_age']
                ) * 100) / $elapsedTimes['script_running'];
    }
    else
    {
        $sqlTimePercent  = 0;
        $randTimePercent = 0;
    }
    $sqlTimePercentStr  = number_format($sqlTimePercent, 4, '.', ' ');
    $randTimePercentStr = number_format($randTimePercent, 4, '.', ' ');
    echo <<<EOT
<table border="1" cellpadding="5" style="text-align:center">
    <tr>
        <th>task</th>
        <th>TimeAll</th>
        <th>TimeLetter</th>
        <th>TimeAge</th>
        <th>TimeSql</th>
        <th>TimeFile</th>
        <th>SQL / RAND</th>
    </tr>
    <tr>
        <th>$taskId</th>
        <th>$scriptRunningTimeStr</th>
        <th>$generateLetterTimeStr</th>
        <th>$generateAgeTimeStr</th>
        <th>$sqlBulkTimeStr</th>
        <th>$fileCreateTimeStr</th>
        <th>$sqlTimePercentStr % / $randTimePercentStr %</th>
    </tr>
</table>
EOT;
}

function getRandomString(&$timeGenerateLetter, $length = 15)
{
    $startTime          = microtime(true);
    $characters         = 'abcdefghijklmnopqrstuvwxyz';
    $shuffle            = str_shuffle($characters);
    $randomText         = substr($shuffle, 0, $length);
    $result             = ucfirst($randomText);
    $timeGenerateLetter += (microtime(true) - $startTime);
    return $result;
}

function getRandomAge(&$timeGenerateAge)
{
    $startTime       = microtime(true);
    $result          = mt_rand(1, 99);
    $timeGenerateAge += (microtime(true) - $startTime);
    return $result;
}

function recreateTableStructure($dbh)
{
    $sqlTableTruncate = "DROP TABLE public.users";
    $sqlTableCreate   = "CREATE TABLE public.users
                           (
                              user_id serial, 
                              first_name character varying(15), 
                              last_name character varying(15), 
                              user_age smallint,
                              CONSTRAINT user_id_key PRIMARY KEY (user_id)
                           ) 
                           WITH (OIDS = FALSE)";
    $sqlTableOwner    = 'ALTER TABLE public.users 
                          OWNER TO "user-praktykanci"';
    $dbh->query($sqlTableTruncate);
    $dbh->query($sqlTableCreate);
    $dbh->query($sqlTableOwner);
    echo '<span style="color:red;">Table "users" drop and created again!</span>';
}

function createFilesRecords($taskId, &$elapsedTimes, $fileRecordsParam, $rows = 1250000)
{
    try
    {
        $startTime   = microtime(true);
        $fileCSV     = $fileRecordsParam['file_name'] . $taskId . $fileRecordsParam['file_extension'];
        $fileHandler = fopen('/tmp/ram/' . $fileCSV, 'w');
        if ($fileHandler != false)
        {
            if (DEBUG)
            {
                echo "Memory used (before) fwrite: " . getMemoryUsage() . " MiB\n";
            }
            $userIdOffset = ($taskId * $rows) + 1;
            for ($i = 0; $i < $rows; $i++)
            {

                fwrite($fileHandler, implode(',', array(
                            $userIdOffset + $i,
                            getRandomString($elapsedTimes['generate_letter']),
                            getRandomString($elapsedTimes['generate_letter']),
                            getRandomAge($elapsedTimes['generate_age']),
                        )) . "\n");
            }
            fclose($fileHandler);
            if (DEBUG)
            {
                echo "Memory used (after) fwrite: " . getMemoryUsage() . " MiB\n";
            }
        }
        else
        {
            echo "File open error";
        }
        $elapsedTimes['file_create'] += (microtime(true) - $startTime);
    }
    catch (Exception $ex)
    {
        echo "File Error: " . $ex->getMessage();
    }
}

function createOneFileRecords($taskId, &$elapsedTimes, $fileRecordsParam, $rows = 1250000)
{
    try
    {
        $startTime   = microtime(true);
        $fileCSV     = $fileRecordsParam['file_name'] . $fileRecordsParam['file_extension'];
            if (DEBUG)
            {
                echo "Memory used (before) fwrite: " . getMemoryUsage() . " MiB\n";
            }
            $userIdOffset = ($taskId * $rows) + 1;
            for ($i = 0; $i < $rows; $i++)
            {
                file_put_contents('/tmp/ram/'.$fileCSV, implode(',', array(
                            $userIdOffset + $i,
                            getRandomString($elapsedTimes['generate_letter']),
                            getRandomString($elapsedTimes['generate_letter']),
                            getRandomAge($elapsedTimes['generate_age']),
                        )) . "\n" , FILE_APPEND);
            }
            if (DEBUG)
            {
                echo "Memory used (after) fwrite: " . getMemoryUsage() . " MiB\n";
            }
        $elapsedTimes['file_create'] += (microtime(true) - $startTime);
    }
    catch (Exception $ex)
    {
        echo "File Error: " . $ex->getMessage();
    }
}

function insertFilesRecords($dbh, $task, &$elapsedTimes, $fileRecordsParam)
{
    $startTime = microtime(true);
    $fileCSV   = $fileRecordsParam['file_name'] . $task . $fileRecordsParam['file_extension'];
    var_dump($fileCSV);
    $sqlBulk   = "COPY users (user_id, first_name, last_name, user_age)
    FROM '/tmp/ram/$fileCSV'
    DELIMITER ','";

    try
    {
        $dbh->query($sqlBulk);
    }
    catch (PDOException $e)
    {
        echo 'PDO error: ' . $e->getMessage() . "\n\n";
    }
    $elapsedTimes['sql_bulk'] += (microtime(true) - $startTime);
}

function insertOneFileRecords($dbh, &$elapsedTimes, $fileRecordsParam)
{
    $startTime = microtime(true);
    $fileCSV   = $fileRecordsParam['file_name'] . $fileRecordsParam['file_extension'];
    $sqlBulk   = "COPY users (user_id, first_name, last_name, user_age)
    FROM '/tmp/ram/$fileCSV'
    DELIMITER ','";

    try
    {
        $dbh->query($sqlBulk);
    }
    catch (PDOException $e)
    {
        echo 'PDO error: ' . $e->getMessage() . "\n\n";
    }
    $elapsedTimes['sql_bulk'] += (microtime(true) - $startTime);
}

function insertAllFileRecords($dbh, &$elapsedTimes, $fileRecordsParam, $fileNumber = 4)
{
    $startTime = microtime(true);
    $sqlBulk   = "";
    for ($i = 1; $i <= $fileNumber; $i++)
    {
        $fileCSV = $fileRecordsParam['file_name'] . $i . $fileRecordsParam['file_extension'];
        $sqlBulk .= "COPY users (user_id, first_name, last_name, user_age)
                    FROM '/tmp/ram/$fileCSV'
                    DELIMITER ',';";
    }
    try
    {
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, 1);
        $stmt = $dbh->prepare($sqlBulk);
        $stmt->execute();
    }
    catch (PDOException $e)
    {
        echo 'PDO error: ' . $e->getMessage() . "\n\n";
    }
    $elapsedTimes['sql_bulk'] += (microtime(true) - $startTime);
}

function getDatabaseHandler($dbName, $dbHost, $dbUser, $dbPass)
{
    $dbh = new PDO("pgsql:dbname=$dbName;host=$dbHost", $dbUser, $dbPass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}

#
# IMPORTANT! before run this script exec once in cmd bottom command
# mkdir /tmp/ram
# mount -t tmpfs -o size=512m tmpfs /tmp/ram
#
############################################################
############################################################
##                                                        ##
##                      START SCRIPT                      ##
##                                                        ##
############################################################
############################################################

echo "<pre>";

$start = microtime(true);

settingDebugMode();

$taskId = getTaskId();

$bulkId = getBulkId();

$dbh = getDatabaseHandler(DB_NAME, DB_HOST, DB_USER, DB_PASS);

if ($taskId == 0)
{
//    recreateTableStructure($dbh);
//    $fileCSV   = $fileRecordsParam['file_name'] . $fileRecordsParam['file_extension'];
//    file_put_contents("/tmp/ram/".$fileCSV, "");
}

if ($taskId > 0)
{
//    createFilesRecords($taskId, $elapsedTimes, $fileRecordsParam);
//    createOneFileRecords($taskId, $elapsedTimes, $fileRecordsParam);
    if ($bulkId == 0)
    {
        echo '<span style="color:red;">insertData is disable</span>';
    }
    elseif ($bulk == 1)
    {
//        insertFilesRecords($dbh, $taskId, $elapsedTimes, $fileRecordsParam);
    }
}

if ($bulkId == 2)
{
//    insertAllFileRecords($dbh, $elapsedTimes, $fileRecordsParam);
}
elseif ($bulkId == 3)
{
//    insertOneFileRecords($dbh, $elapsedTimes, $fileRecordsParam);
}

$elapsedTimes['script_running'] = microtime(true) - $start;
printDebugInfo($taskId, $elapsedTimes);
