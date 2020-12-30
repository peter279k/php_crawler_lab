<?php

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;
use Nesk\Rialto\Exceptions\Node\FatalException;

$singleEpisodeUrl = $argv[1] ?? '';
if ($singleEpisodeUrl === '') {
    echo 'Cannot find specific single episode URL. Exited...', "\n";
    exit(1);
}

$matchedCount = preg_match('/(https\:\/\/baabao\.com\/single-episode\/)(\d+)(\?)to=(\d+)&s=(\w+)/', $singleEpisodeUrl);

if ($matchedCount !== 1) {
    echo 'The single episode URL format is invalid...', "\n";
    exit(1);
}

$jsCode = "return JSON.parse(JSON.parse(localStorage.getItem('localforage/listen_history/lastListenEpisode')));";

try {
    $puppeteerOptions = [
        'read_timeout' => 65,
    ];
    $puppeteer = new Puppeteer($puppeteerOptions);
    $launchOptions = [
        'headless' => true,
        'ignoreHTTPSErrors' => true,
        'args' => [
            '--no-sandbox',
        ],
    ];
    $browser = $puppeteer->launch($launchOptions);

    $navigationOptions = [
        'timeout' => 60000,
        'waitUntil' => 'networkidle2',
    ];
    $page = $browser->newPage();
    $page->goto($singleEpisodeUrl, $navigationOptions);

    $episodeInfo = $page->evaluate(JsFunction::createWithBody($jsCode)); 
    echo 'Episode title: ', $episodeInfo['episode_title'], "\n";
    echo 'Episode Data URL: ', $episodeInfo['episode_data_url'], "\n";

    $browser->close();
} catch(FatalException $e) {
    echo 'Fatal error exception: ', $e->getMessage(), "\n";
    exit(1);
}

$downloadFileClient = new Client();
$requestOption = [
    'sink' => './files/' . $episodeInfo['episode_title'] . '.mp3',
];

echo 'Download files:', $episodeInfo['episode_title'], '.mp3', "...\n";
try {
    $downloadFileClient->request('GET', $episodeInfo['episode_data_url'], $requestOption);
} catch (RequestException $e) {
    echo 'Failed to download file:', $episodeInfo['episode_title'], '.mp3', "...\n";
    echo 'Failed to download URL:', $episodeInfo['episode_data_url'], "...\n";
    exit(1);
}

echo 'Download ', $episodeInfo['episode_title'], '.mp3 has been done.', "\n";
