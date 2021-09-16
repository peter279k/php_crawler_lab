<?php

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;

$requestParams = [
    'multipart' => [
        [
            'name'     => 'image',
            'contents' => Utils::tryFopen('./sample.png', 'r'),
            'filename' => 'sample.png',
        ],
    ]
];

$requestUrl = 'https://upload.prntscr.com/upload/1631785559/ae1f2845cf58e3738599a40687344601';
$client = new Client();
$response = $client->request('POST', $requestUrl, $requestParams);
var_dump($response->getBody()->getContents());
