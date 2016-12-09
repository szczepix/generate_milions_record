<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//error_reporting( E_ALL ^ E_NOTICE );
error_reporting( E_ALL );

$dbName = "praktykanci";
$host = "localhost";
$dbUser = "user-praktykanci";
$dbPass = "praktykanci";

$randomGeneratorTimeLetter = 0;
$randomGeneratorTimeLetterGen = 0;
$randomGeneratorTimeNumber = 0;
$randomGeneratorTimeSql = 0;
$sql = "";

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

function genRandomString($length = 15) {
    
    global $randomGeneratorTimeLetterGen;
    $startTime = microtime(true);
    
    // test 1 - one milion = 1.2621750831604
    $string = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'),0,$length);
    // test 2 - one milion = 3.2537093162537
//    $chars = "abcdefghijklmnopqrstuvwxyz";
//    $l = strlen($chars)-1;
//    $string = '';
//    for ($i = 0; $i < $length; $i++)
//    {
//        $string .= $chars[mt_rand(0, $l)];
//    }
    // test 3 - one milion = 
    
    
    $endTime = microtime(true);
    $randomGeneratorTimeLetterGen += ($endTime - $startTime);

    return $string;
}

function randomAge()
{
    global $randomGeneratorTimeNumber;
    $startTime = microtime(true);
    
    // test 0 - one milion = 0.17256164550781
//    $result = rand(1, 99);
    // test 1 - one milion = 0.17052984237671
    $result = mt_rand(1, 99);
    
    $endTime = microtime(true);
    $randomGeneratorTimeNumber += ($endTime - $startTime);
    
    return $result;
}

$dbh = new PDO("pgsql:dbname=$dbName;host=$host", $dbUser, $dbPass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sqlTableTruncate = "TRUNCATE TABLE public.users;";
$dbh->query($sqlTableTruncate);



echo "<pre>";
$startTimeScript = microtime(true);
echo "Start: " . date('H:i:s', time()) . "\n";

$countRecords = 1000;

//$usersRecordsTable = array();
//for ($i = 0; $i < $countRecords; $i++)
//{
//    $usersRecordsTable[$i] = array(
//        "first_name" => randomString(15),
//        "last_name" => randomString(15),
//        "age" => randomAge(),
//    );   
//}
//echo "Liczba rekordów w tablicy [ usersRecordsTable ]: " . count($usersRecordsTable) ."\n";

$sth = $dbh->prepare('INSERT INTO users(first_name, last_name, user_age)'
        . 'VALUES (:firstname, :lastname, :age)');
$sth->bindParam(':firstname', $firstname);
$sth->bindParam(':lastname', $lastname);
$sth->bindParam(':age', $age);


echo "do zapisania w tabeli: " . number_format($countRecords, 0, ',', ' ') . " rekordów\n";

$startTime = microtime(true);



for ($i = 0; $i < $countRecords; $i++)
{

    

    $firstname = randomString();
    $lastname = randomString();
    $age = randomAge();
    
//    $firstname2 = genRandomString();
//    $lastname2 = genRandomString();
    
    $sth->execute();     
}

$endTime = microtime(true);
$randomGeneratorTimeSql += ($endTime - $startTime);
$randomGeneratorTimeSql = ($randomGeneratorTimeSql - $randomGeneratorTimeLetter - $randomGeneratorTimeNumber); 




$endTimeScript = microtime(true);

$allTimeScript = ($endTimeScript - $startTimeScript);
echo "Koniec: " . date('H:i:s', time()) . "\n\n";
echo number_format($allTimeScript, 16, '.', ' ') . " sek - Czas całego skryptu\n";
echo number_format($randomGeneratorTimeSql, 16, '.', ' ') . " sek - Czas SQL-a\n";
echo number_format($randomGeneratorTimeLetter, 16, '.', ' ') . " sek - randomString()\n";
echo number_format($randomGeneratorTimeNumber, 16, '.', ' ') . " sek - randomAge()\n\n";


$allNumber = 5000000 / $countRecords;
echo "czas dla 5 milionów\n\n";
echo number_format($allTimeScript * $allNumber, 16, '.', ' ') . " sek - Czas całego skryptu\n";
echo number_format($randomGeneratorTimeSql * $allNumber, 16, '.', ' ') . " sek - Czas SQL-a\n";
echo "    " . number_format($randomGeneratorTimeLetter * $allNumber, 16, '.', ' ') . " sek - randomString()\n";
echo "     " . number_format($randomGeneratorTimeNumber * $allNumber, 16, '.', ' ') . " sek - randomAge()\n\n";




//echo "    " . number_format($randomGeneratorTimeLetterGen * $allNumber, 16, '.', ' ') . " - genRandomString()\n\n";;

$allPercentNumber = $allTimeScript * $allNumber;
$sqlPercent = (($randomGeneratorTimeSql * $allNumber) * 100) / $allPercentNumber;
$randomPercent = ((($randomGeneratorTimeLetter * $allNumber) + ($randomGeneratorTimeNumber * $allNumber)) * 100) / $allPercentNumber;
echo "Proporcje czasów działania skryptu\n\n";
echo number_format($sqlPercent, 4, '.', ' ') . "% - czas SQL-a\n";
echo " " . number_format($randomPercent, 4, '.', ' ') . "% - czas generowania danych\n";

echo "</pre>";