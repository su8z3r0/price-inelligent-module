<?php

$eans = [
    '3349668612598',
    '3386460101042',
    '3423478504059',
    '3423478925656',
    '3614272225695',
    '6291108730515',
    '6294015158823',
    '7640177366009',
    '7640496670146',
    '8011003809196',
    '8011003838004',
    '8011003852727',
    '8011003996179',
    '8011003997008',
    '8018365071162',
    '8052086373969',
    '8052464891603'
];

$baseUrl = 'https://makeup.it';
$results = [];

foreach ($eans as $ean) {
    echo "Searching for $ean... ";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$baseUrl/search/?q=$ean");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $html = curl_exec($ch);
    curl_close($ch);
    
    // Pattern to find product link in search results
    // Makeup.it usually has links like /product/12345/
    if (preg_match('/href="(\/product\/\d+\/)"/i', $html, $matches)) {
        $url = $baseUrl . $matches[1];
        $results[$ean] = $url;
        echo "Found: $url\n";
    } else {
        echo "Not found\n";
    }
    
    sleep(1); // Be polite
}

file_put_contents('discovered_urls.json', json_encode($results, JSON_PRETTY_PRINT));
echo "\nDiscovery complete. Results saved to discovered_urls.json\n";
