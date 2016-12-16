<?php

echo "<pre>";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function curlClearData()
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost/praktykanci/core.php?task=0");
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);
}

$urls = array(
    "http://localhost/praktykanci/core.php?task=1",
    "http://localhost/praktykanci/core.php?task=2",
    "http://localhost/praktykanci/core.php?task=3",
    "http://localhost/praktykanci/core.php?task=4",
);

function curlMultiRequest($urls, $options = array())
{
    $ch = array();
    $results = array();
    $mh = curl_multi_init();
    foreach ($urls as $key => $val)
    {
        $ch[$key] = curl_init();
        if ($options)
        {
            curl_setopt_array($ch[$key], $options);
        }
        curl_setopt($ch[$key], CURLOPT_URL, $val);
        curl_multi_add_handle($mh, $ch[$key]);
    }

    $running = null;
    do
    {
        curl_multi_exec($mh, $running);
        curl_multi_select($mh);
    }
    while ($running > 0);

    // Get content and remove handles.
    foreach ($ch as $key => $val)
    {
        $results[$key] = curl_multi_getcontent($val);
        curl_multi_remove_handle($mh, $val);
    }

    curl_multi_close($mh);

    return $results;
}

curlClearData();

sleep(3);

$start = microtime(true);

curlMultiRequest($urls);

$end = microtime(true);
echo "\n\nCzas dzialania: " . number_format(($end - $start), 16, '.', ' ') . " sek";

echo "\n\n\n\n";