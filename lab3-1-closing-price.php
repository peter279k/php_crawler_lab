<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

$closingPriceLink = 'https://www.kgieworld.com.tw/Stock/stock_2_7.aspx?findex=1';

$client = new Client(['cookies' => true]);
$response = $client->request('GET', $closingPriceLink);

$responseString = (string)$response->getBody();
$viewState = '__VIEWSTATE';
$eventValidation = '__EVENTVALIDATION';
$viewStateGenerator = '63FF896A';

$closingPriceFileContents = [
    'lbtnDown01',
    'lbtnDown02',
    'lbtnDown03',
    'lbtnDown04',
    'lbtnDown05',
];

$closingPriceDates = [
    'lblDate01' => '',
    'lblDate02' => '',
    'lblDate03' => '',
    'lblDate04' => '',
    'lblDate05' => '',
];

$crawler = new Crawler($responseString);

$crawler
   ->filter('input[type="hidden"]')
   ->reduce(function (Crawler $node, $i) {
        global $viewState;
        global $eventValidation;

        if ($node->attr('name') === $viewState) {
            $viewState = $node->attr('value');
        }
        if ($node->attr('name') === $eventValidation) {
            $eventValidation = $node->attr('value');
        }
   });

foreach ($closingPriceDates as $btnDateKey => $btnDate) {
    $crawler
        ->filter('span[id="' . $btnDateKey . '"]')
        ->reduce(function (Crawler $node, $i) {
            global $closingPriceDates;
            global $btnDateKey;
            $closingPriceDates[$btnDateKey] = $node->text();
    });
}

$formParams = [
    'form_params' => [
        '__EVENTTARGET' => '',
        '__EVENTARGUMENT' => '',
        '__VIEWSTATE' => $viewState,
        '__VIEWSTATEGENERATOR' => $viewStateGenerator,
        '__EVENTVALIDATION' => $eventValidation,
        'selMarket' => '1',
        'T1' => '',
        'T1' => '',
    ],
    'headers' => [
        'Host' => 'www.kgieworld.com.tw',
        'Upgrade-Insecure-Requests' => '1',
        'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36',
        'Sec-Fetch-Mode' => 'navigate',
        'Sec-Fetch-User' => '?1',
        'Sec-Fetch-Site' => 'same-origin',
    ],
];

$index = 1;
foreach ($closingPriceFileContents as $eventTarget) {
    $formParams['form_params']['__EVENTTARGET'] = $eventTarget;
    $response = $client->request('POST', $closingPriceLink, $formParams);
    $closingPriceFileContent = (string)$response->getBody();
    $closingPriceFileName = $closingPriceDates['lblDate0' . $index] . '.csv';
    $fileHandler = fopen($closingPriceFileName, 'w');

    $closingPriceCrawler = new Crawler($closingPriceFileContent);
    $closingPriceCrawler
        ->filter('tr')
        ->reduce(function (Crawler $node, $i) {
            global $fileHandler;

            $texts = str_replace(["\r", "\n", " ", "	", "'", "amp;", '<td>'], '', $node->html());
            $texts = str_replace('</td>', ',', $texts);
            $texts = mb_substr($texts, 0, -1) . "\n";

            fputs($fileHandler, $texts);
    });

    fclose($fileHandler);
    $index += 1;
}
