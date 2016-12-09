<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//phpinfo(4);
//phpinfo

echo "<pre>";

$dbName = "praktykanci";
$host = "localhost";
$dbUser = "user-praktykanci";
$dbPass = "praktykanci";

$servername = "localhost";
$username = "user-praktykanci";
$password = "praktykanci";
$dbname = "praktykanci";

//    $START = microtime(true);
//    for ($i = 1; $i < 5000000; ++$i) {
//        $j = rand(1,100);
//    }
//    $END = microtime(true) - $START;
//    print "Short rand() took $END seconds\n";
//
//    $START = microtime(true);
//    for ($i = 1; $i < 5000000; ++$i) {
//        $j = mt_rand(1,100);
//    }
//    $END = microtime(true) - $START;
//    print "Short mt_rand() took $END seconds\n";
//
//    $START = microtime(true);
//    for ($i = 1; $i < 5000000; ++$i) {
//        $j = rand(1,10000000);
//    }
//    $END = microtime(true) - $START;
//    print "Long rand() took $END seconds\n";
//
//    $START = microtime(true);
//    for ($i = 1; $i < 5000000; ++$i) {
//        $j = mt_rand(1,10000000);
//    }
//    $END = microtime(true) - $START;
//    print "Long mt_rand() took $END seconds\n";


mt_srand(123456);
    for($i = 0; $i < 100; $i++) {
     echo mt_rand(1, 99), "\n";   
    }










echo "</pre>";