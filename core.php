<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$dbName = "praktykanci";
$host = "localhost";
$dbUser = "user-praktykanci";
$dbPass = "praktykanci";

function randomString($length)
{
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $randomText = str_shuffle($characters);
    $randomText = substr(ucfirst($randomText), 0, $length);
    return $randomText;
}

function randomAge()
{
    return mt_rand(1, 99);
}

$dbh = new PDO("pgsql:dbname=$dbName;host=$host", $dbUser, $dbPass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sqlTableDrop = "TRUNCATE TABLE public.users;";
$dbh->query($sqlTableDrop);


echo "<pre>";
$startTime = microtime(true);
echo "Start: " . date('H:i:s', time()) . "\n";


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

$sth = $dbh->prepare('INSERT INTO users(first_name, last_name, user_age) VALUES(:first, :last, :age)');

$countRecords = 10000;
echo "do zapisania w tabeli: " . $countRecords . " rekordów\n";

for ($i = 0; $i < $countRecords; $i++)
{
//    $name = $usersRecordsTable[$i]['first_name'];
//    $surname = $usersRecordsTable[$i]['last_name'];
//    $age = $usersRecordsTable[$i]['age'];
    $name = randomString(15);
    $surname = randomString(15);
    $age = randomAge();

    $sth->bindParam(':first', $name);
    $sth->bindParam(':last', $surname);
    $sth->bindParam(':age', $age);
    $sth->execute();    
}










$endTime = microtime(true);
echo "Koniec: " . date('H:i:s', time()) . "\n";
echo "Czas w sek: " . ($endTime - $startTime);
echo "</pre>";