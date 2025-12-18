<?php

require(__DIR__ . '/vendor/autoload.php');

use GuzzleHttp\Client;

$client = new Client([
    // Base URI is used with relative requests
    'base_uri' => 'https://www.yrgopelag.se/centralbank/transferCode',
    // You can set any number of default request options.
]);

$r = $client->request('POST', 'https://www.yrgopelag.se/centralbank/transferCode', [
    'form_params' => [
        'transferCode' => 'a67ebb95-548c-4ae0-9f85-5d42e817a34e', 
        'totalCost' => '5'
        ]
    
]);

var_dump($r->getBody()->getContents()); 