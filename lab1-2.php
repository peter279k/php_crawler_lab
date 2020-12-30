<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

$latestNews = 'https://enews.nttu.edu.tw/app/index.php?Action=mobilercglist';
$client = new Client();

$links = [];
$titles = [];
$page = 1;
$stat = '';
while ($stat !== 'over') {
    $formParams =  [
        'form_params' => [
            'Rcg' => '1009',
            'IsTop' => '0',
            'Op' => 'getpartlist',
            'Page' => (string) $page,
        ],
    ];

    $response = $client->request('POST', $latestNews, $formParams);

    $latestNewsString = (string)$response->getBody();
    $latestNewsString = json_decode($latestNewsString, true);
    $content = $latestNewsString['content'];
    $stat = $latestNewsString['stat'];

    $crawler = new Crawler($content);

    $crawler
        ->filter('a')
        ->reduce(function (Crawler $node, $i) {
            global $titles;
            global $links;
            $titles[] = str_replace(["	", "\r", "\n"], "", $node->text());
            $links[] = $node->attr('href');
        });

    $page += 1;
}

var_dump($links);
var_dump($titles);
