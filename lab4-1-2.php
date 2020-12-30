<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

$familyPortPrintUrl = 'https://www.famiport.com.tw/Web_Famiport/page/cloudprint.aspx';
$client = new Client(['cookie' => true]);

$response = $client->request('GET', $familyPortPrintUrl);
$responseHtml = (string)$response->getBody();
$crawler = new Crawler($responseHtml);
$viewState = '__VIEWSTATE';
$eventValidation = '__EVENTVALIDATION';
$viewStateGenerator = '__VIEWSTATEGENERATOR';

$crawler
    ->filter('input[type="hidden"]')
    ->reduce(function (Crawler $node, $i) {
        global $viewState;
        global $eventValidation;
        global $viewStateGenerator;

        if ($node->attr('name') === $viewState) {
            $viewState = $node->attr('value');
        }
        if ($node->attr('name') === $eventValidation) {
            $eventValidation = $node->attr('value');
        }
        if ($node->attr('name') === $viewStateGenerator) {
            $viewStateGenerator = $node->attr('value');
        }
    });

$email = 'peter279k@gmail.com';
$formParams = [
    'multipart' => [
        [
            'name' => '__VIEWSTATEGENERATOR',
            'contents' => $viewStateGenerator,
        ],
        [
            'name' => '__EVENTVALIDATION',
            'contents' => $eventValidation,
        ],
        [
            'name' => '__VIEWSTATE',
            'contents' => $viewState,
        ],
        [
            'name' => 'ctl00$ContentPlaceHolder1$FileLoad',
            'contents' => fopen('./OOPPHP.pdf', 'r'),
            'headers' => [
                'Content-Type' => 'application/pdf',
            ],
        ],
        [
            'name' => 'ctl00$ContentPlaceHolder1$CKbox',
            'contents' => true,
        ],
        [
            'name' => 'ctl00$ContentPlaceHolder1$txtEmail',
            'contents' => $email,
        ],
        [
            'name' => 'ctl00$ContentPlaceHolder1$btnSave',
            'contents' => '',
        ],
    ],
];

$response = $client->request('POST', $familyPortPrintUrl, $formParams);

var_dump((string)$response->getBody());
