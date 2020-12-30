<?php

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

$programUrlLists = 'https://baabao.com/web-program-detail/29684/';
$client = new Client();
$programDetailResponse = $client->request('GET', $programUrlLists);

$episodeLists = json_decode((string)$programDetailResponse->getBody(), true)['episode_list'];

if (is_dir('./files/') === false) {
    mkdir('./files/');
}

$failedDownloadLists = [];
foreach ($episodeLists as $episodeList) {
    $downloadFileClient = new Client();
    $requestOption = [
        'sink' => './files/' . $episodeList['episode_title'] . '.mp3',
    ];
    echo 'Download files:', $episodeList['episode_title'], '.mp3', "...\n";
    try {
        $downloadFileClient->request('GET', $episodeList['episode_data_url'], $requestOption);
    } catch (RequestException $e) {
        echo 'Failed to download files:', $episodeList['episode_title'], '.mp3', "...\n";
        $failedDownloadLists[] = [
            $episodeList['episode_title'],
            $episodeList['episode_data_url'],
        ];
    }
}

if (count($failedDownloadLists) !== 0) {
    print_r($failedDownloadLists);
}
