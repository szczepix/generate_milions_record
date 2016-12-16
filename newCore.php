<?php

echo "<pre>";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//error_reporting( E_ALL ^ E_NOTICE );
error_reporting(E_ALL);

echo ini_get("memory_limit") . "\n";
ini_set('memory_limit', '2048M');
echo ini_get("memory_limit") . "\n";
echo "not real: " . (memory_get_peak_usage(false) / 1024 / 1024) . " MiB\n";
echo "real: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MiB\n\n";

$dbName = "praktykanci";
$host = "localhost";
$dbUser = "user-praktykanci";
$dbPass = "praktykanci";

$randomGeneratorTimeLetter = 0;
$randomGeneratorTimeLetterGen = 0;
$randomGeneratorTimeNumber = 0;
$randomGeneratorTimeSql = 0;
$sql = "";

$task = null;
$fileCSV = "";
$startLoop = null;
$stopLoop = null;

function randomString($length = 15)
{
    global $randomGeneratorTimeLetter;
    $startTime = microtime(true);

    // test 0 - one milion = 1.3495995998383
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $shuffle = str_shuffle($characters);
    $randomText = substr($shuffle, 0, $length);
    $result = ucfirst($randomText);

    $endTime = microtime(true);
    $randomGeneratorTimeLetter += ($endTime - $startTime);

    return $result;
}

function randomAge()
{
    global $randomGeneratorTimeNumber;
    $startTime = microtime(true);

    $result = mt_rand(1, 99);

    $endTime = microtime(true);
    $randomGeneratorTimeNumber += ($endTime - $startTime);

    return $result;
}

$startTimeScript = microtime(true);
$startTimeGlobal = microtime(true);
echo "Start: " . date('H:i:s', time()) . " \n";

$countRecords = 5000000;

echo "do zapisania w tabeli: " . number_format($countRecords, 0, ',', ' ') . " rekordów\n";

$startTime = microtime(true);

if ($_GET["task"] == 0)
{
    $task = 0;
    $dbh = new PDO("pgsql:dbname=$dbName;host=$host", $dbUser, $dbPass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sqlTableTruncate = "DROP TABLE public.users";
    $dbh->query($sqlTableTruncate);

    $sqlTableCreate = "CREATE TABLE public.users
(
   user_id serial, 
   first_name character varying(15), 
   last_name character varying(15), 
   user_age smallint,
   CONSTRAINT user_id_key PRIMARY KEY (user_id)
) 
WITH (
  OIDS = FALSE
)";
    $sqlTableOwner = 'ALTER TABLE public.users
  OWNER TO "user-praktykanci"';

    $dbh->query($sqlTableCreate);
    $dbh->query($sqlTableOwner);
//    exit();
}
elseif ($_GET["task"] == 1)
{
    $task = 1;
    $fileCSV = "plik" . $task . ".csv";
    $startLoop = 0;
    $stopLoop = $task * 1000000;
}
elseif ($_GET["task"] == 2)
{
    $task = 2;
    $fileCSV = "plik" . $task . ".csv";
    $startLoop = ($task - 1) * 1000000;
    $stopLoop = $task * 1000000;
}
elseif ($_GET["task"] == 3)
{
    $task = 3;
    $fileCSV = "plik" . $task . ".csv";
    $startLoop = ($task - 1) * 1000000;
    $stopLoop = $task * 1000000;
}
elseif ($_GET["task"] == 4)
{
    $task = 4;
    $fileCSV = "plik" . $task . ".csv";
    $startLoop = ($task - 1) * 1000000;
    $stopLoop = $task * 1000000;
}
elseif ($_GET["task"] == 5)
{
    $task = 5;
    $fileCSV = "plik" . $task . ".csv";
    $startLoop = ($task - 1) * 1000000;
    $stopLoop = $task * 1000000;
}
else
{
    echo "BŁĄÐ: parametr task nie ustawiony!";
    echo "CSV: " . $fileCSV . "\n";
    echo "startLoop: " . $startLoop . "\n";
    echo "stopLoop: " . $stopLoop . "\n";
    exit();
}

//$output = shell_exec('rm -rf /tmp/ram');
//echo $output . "\n";
//
//$output = shell_exec('mkdir /tmp/ram');
//echo $output . "\n";
//
//$output = shell_exec('mount -t tmpfs -o size=512m tmpfs /tmp/ram');
//echo $output . "\n";

//if (isset($task) && $task > 0 && $task < 6)
//{
    try
    {
        $fileHandler = fopen('../tmp/ram/' . $fileCSV, 'w');

        $dataTable = array('user_id', 'first_name', 'last_name', 'user_age',);

        if ($fileHandler != false)
        {

            echo "Memory used (before) real: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MiB\n\n";
            fputcsv($fileHandler, $dataTable);

            for ($i = $startLoop; $i < $stopLoop; $i ++)
            {
                fputcsv($fileHandler, array($i + 1, randomString(15), randomString(15), randomAge()));
            }

            fclose($fileHandler);

            echo "Memory used (after) real: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MiB\n\n";
        }
        else
        {
            echo "File open error";
        }
    }
    catch (Exception $ex)
    {
        echo "File Error: " . $ex->getMessage();
    }

    $sqlBulk = "COPY users (user_id, first_name, last_name, user_age)
    FROM '/tmp/ram/$fileCSV'
    CSV 
    HEADER";

    echo "Memory used (before) real: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MiB\n\n";

    try
    {
        $dbh = new PDO("pgsql:dbname=$dbName;host=$host", $dbUser, $dbPass);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->query($sqlBulk);
    }
    catch (PDOException $e)
    {
        echo 'PDO error: ' . $e->getMessage() . "\n\n";
    }
    
    echo "Memory used (after) real: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MiB\n\n";
//}

$endTime = microtime(true);
$randomGeneratorTimeSql += ($endTime - $startTime);
$randomGeneratorTimeSql = ($randomGeneratorTimeSql - $randomGeneratorTimeLetter - $randomGeneratorTimeNumber);

$endTimeScript = microtime(true);

$allTimeScript = ($endTimeScript - $startTimeScript);
$endTimeGlobal = microtime(true);
echo "Koniec: " . date('H:i:s', time()) . "\n" . ($endTimeGlobal - $startTimeGlobal) . " s\n";
echo number_format($allTimeScript, 16, '.', ' ') . " sek - Czas całego skryptu\n";
echo number_format($randomGeneratorTimeSql, 16, '.', ' ') . " sek - Czas SQL-a\n";
echo number_format($randomGeneratorTimeLetter, 16, '.', ' ') . " sek - randomString()\n";
echo number_format($randomGeneratorTimeNumber, 16, '.', ' ') . " sek - randomAge()\n\n";

$allPercentNumber = $allTimeScript;
$sqlPercent = (($randomGeneratorTimeSql) * 100) / $allPercentNumber;
$randomPercent = ((($randomGeneratorTimeLetter) + ($randomGeneratorTimeNumber)) * 100) / $allPercentNumber;
echo "Proporcje czasów działania skryptu\n\n";
echo number_format($sqlPercent, 4, '.', ' ') . "% - czas SQL-a\n";
echo " " . number_format($randomPercent, 4, '.', ' ') . "% - czas generowania danych\n";

echo "</pre>";