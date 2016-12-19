<?php

const DEBUG = true;

const DB_NAME = "praktykanci";
const DB_HOST = "localhost";
const DB_USER = "user-praktykanci";
const DB_PASS = "praktykanci";

$task = -1;
$bulk = 0;
$fileName = "file";
$fileExtension = ".csv";
$timeGenerateLetter = 0;
$timeGenerateAge = 0;
$timeSqlBulk = 0;
$timeFileCreate = 0;
$timeScriptRunning = 0;

function debugMode()
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

function checkTask()
{
    global $task;
    if (isset($_GET["task"]))
    {
        $task = $_GET["task"];
    }
}

function checkBulk()
{
    global $bulk;
    if (isset($_GET["bulk"]))
    {
        $bulk = $_GET["bulk"];
    }
}

function checkMountRamDisk()
{
    // TODO: find and write linux cmd check mount
}

function showVarTable($round = 4)
{
    global $task, $timeScriptRunning, $timeGenerateLetter,
    $timeGenerateAge, $timeSqlBulk, $timeFileCreate;
    echo '<table border="1" cellpadding="5" style="text-align:center"><tr>
              <th>task</th>
              <th>TimeAll</th>
              <th>TimeLetter</th>
              <th>TimeAge</th>
              <th>TimeSql</th>
              <th>TimeFile</th>
              <th>SQL / RAND</th></tr>';
    echo "<td>" . $task . "</td>";
    echo "<td>" . number_format($timeScriptRunning, $round, '.', ' ') . " s</td>";
    echo "<td>" . number_format($timeGenerateLetter, $round, '.', ' ') . " s</td>";
    echo "<td>" . number_format($timeGenerateAge, $round, '.', ' ') . " s</td>";
    echo "<td>" . number_format($timeSqlBulk, $round, '.', ' ') . " s</td>";
    $timeFile = $timeFileCreate - $timeGenerateLetter - $timeGenerateAge;
    echo "<td>" . number_format($timeFile, $round, '.', ' ') . " s</td>";
    $percentRand = (($timeGenerateLetter + $timeGenerateAge) * 100) / $timeScriptRunning;
    $percentSql = ($timeSqlBulk * 100) / $timeScriptRunning;
    echo "<td>" . number_format($percentSql, 4, '.', ' ') . "% / "
    . number_format($percentRand, 4, '.', ' ') . "%</td>";
    echo "</tr></table>";
}

function generateRangomString($length)
{
    global $timeGenerateLetter;
    $startTime = microtime(true);
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $shuffle = str_shuffle($characters);
    $randomText = substr($shuffle, 0, $length);
    $result = ucfirst($randomText);
    $timeGenerateLetter += (microtime(true) - $startTime);
    return $result;
}

function generateRangomAge()
{
    global $timeGenerateAge;
    $startTime = microtime(true);
    $result = mt_rand(1, 99);
    $timeGenerateAge += (microtime(true) - $startTime);
    return $result;
}

function cleanDataTable($dbh)
{
    $sqlTableTruncate = "DROP TABLE public.users";
    $sqlTableCreate = "CREATE TABLE public.users
                           (
                              user_id serial, 
                              first_name character varying(15), 
                              last_name character varying(15), 
                              user_age smallint,
                              CONSTRAINT user_id_key PRIMARY KEY (user_id)
                           ) 
                           WITH (OIDS = FALSE)";
    $sqlTableOwner = 'ALTER TABLE public.users 
                          OWNER TO "user-praktykanci"';
    $dbh->query($sqlTableTruncate);
    $dbh->query($sqlTableCreate);
    $dbh->query($sqlTableOwner);
    echo '<span style="color:red;">Table "users" drop and created again!</span>';
}

function createDataFile($task, $rows = 1250000)
{
    try
    {
        global $fileName, $fileExtension, $timeFileCreate;
        $startTime = microtime(true);
        $fileCSV = $fileName . $task . $fileExtension;
        $fileHandler = fopen('/tmp/ram/' . $fileCSV, 'w');
        if ($fileHandler != false)
        {
            if (DEBUG)
            {
                echo "Memory used (before) fwrite: " . getMemoryUsage() . " MiB\n";
            }
            for ($i = ($task - 1) * $rows; $i < $task * $rows; $i++)
            {
                fwrite($fileHandler, (( $i + 1 . ","
                        . generateRangomString(15) . ","
                        . generateRangomString(15) . ","
                        . generateRangomAge()) . "\n"));
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
        $timeFileCreate += (microtime(true) - $startTime);
    }
    catch (Exception $ex)
    {
        echo "File Error: " . $ex->getMessage();
    }
}

function insertSingleDataFile($dbh, $task)
{
    global $fileName, $fileExtension, $timeSqlBulk;
    $startTime = microtime(true);
    $fileCSV = $fileName . $task . $fileExtension;
    $sqlBulk = "COPY users (user_id, first_name, last_name, user_age)
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
    $timeSqlBulk += (microtime(true) - $startTime);
}

function insertAllDataFiles($dbh)
{
    global $fileName, $fileExtension, $timeSqlBulk;
    $startTime = microtime(true);
    $fileCSV = $fileName . 1 . $fileExtension;
    $sqlBulk = "COPY users (user_id, first_name, last_name, user_age)
    FROM '/tmp/ram/$fileCSV'
    DELIMITER ',';";
    $fileCSV = $fileName . 2 . $fileExtension;
    $sqlBulk .= "COPY users (user_id, first_name, last_name, user_age)
    FROM '/tmp/ram/$fileCSV'
    DELIMITER ',';";
    $fileCSV = $fileName . 3 . $fileExtension;
    $sqlBulk .= "COPY users (user_id, first_name, last_name, user_age)
    FROM '/tmp/ram/$fileCSV'
    DELIMITER ',';";
    $fileCSV = $fileName . 4 . $fileExtension;
    $sqlBulk .= "COPY users (user_id, first_name, last_name, user_age)
    FROM '/tmp/ram/$fileCSV'
    DELIMITER ',';";
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
    $timeSqlBulk += (microtime(true) - $startTime);
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

debugMode();

checkTask();

checkBulk();

$dbh = getDatabaseHandler(DB_NAME, DB_HOST, DB_USER, DB_PASS);

if ($task == 0)
{
    cleanDataTable($dbh);
}

if ($task > 0)
{
    createDataFile($task);
    if ($bulk == 0)
    {
        echo '<span style="color:red;">insertData is disable</span>';
    }
    elseif ($bulk == 1)
    {
        insertSingleDataFile($dbh, $task);
    }
}

if ($bulk == 3)
{
    insertAllDataFiles($dbh);
}

$timeScriptRunning = microtime(true) - $start;
showVarTable();
