<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

$latestNews = 'https://aa.nttu.edu.tw/p/412-1002-8645.php?Lang=zh-tw';
$client = new Client();
$response = $client->request('GET', $latestNews);

$courseOutlineString = (string)$response->getBody();

$courseStrings = [];
$index = 0;

$crawler = new Crawler($courseOutlineString);

$crawler
    ->filter('table a')
    ->reduce(function (Crawler $node, $i) {
        global $courseStrings;
        global $index;
        $courseStrings[$index]['data_link'] = $node->attr('href');
        $courseStrings[$index]['title'] = $node->text();
        $index += 1;
    });

var_dump($courseStrings);
